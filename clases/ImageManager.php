<?php

require_once 'vendor/autoload.php';

use Intervention\Image\ImageManager as InterventionImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

class ImageManager
{
  private $sizes;
  private $manager;

  public function __construct()
  {
    // Configurar driver correcto para v3
    $driver = IMAGE_DRIVER === 'imagick' ? new ImagickDriver() : new GdDriver();
    $this->manager = new InterventionImageManager($driver);

    // Parsear tamaños configurados
    $this->parseSizes();
  }

  /**
   * Procesar imagen con múltiples tamaños
   */
  public function processImage($filePath, $filename, $options = [])
  {
    $results = [];

    try {
      $image = $this->manager->read($filePath);

      // Procesar cada tamaño configurado
      foreach ($this->sizes as $sizeName => $dimensions) {
        $processedImage = clone $image;
        $processedImage = $this->resizeImage($processedImage, $dimensions, $options);

        $sizedFilename = $this->generateSizedFilename($filename, $sizeName);
        $outputPath = $this->getUploadPath() . $sizedFilename;

        // Guardar imagen con calidad específica
        $quality = $this->getQuality($image->origin()->mediaType());
        $processedImage->save($outputPath, $quality);

        // Generar WebP si está habilitado
        if (GENERATE_WEBP) {
          $webpPath = $this->generateWebPPath($outputPath);
          $processedImage->toWebp($quality)->save($webpPath);
        }

        $results[$sizeName] = [
          'filename' => $sizedFilename,
          'path' => UPLOAD_PATH_IMAGES . $sizedFilename,
          'width' => $processedImage->width(),
          'height' => $processedImage->height(),
          'size' => filesize($outputPath)
        ];
      }

      return ['success' => true, 'images' => $results];
    } catch (Exception $e) {
      error_log("Error procesando imagen: " . $e->getMessage());
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  /**
   * Redimensionar imagen según configuración
   */
  private function resizeImage($image, $dimensions, $options)
  {
    $width = $dimensions['width'];
    $height = $dimensions['height'];
    $crop = $options['crop'] ?? $dimensions['crop'] ?? false;

    if ($crop) {
      // Crop y redimensión
      return $image->cover($width, $height, IMAGE_CROP_POSITION);
    } else {
      // Redimensión manteniendo aspecto
      return $image->scale($width, $height);
    }
  }

  /**
   * Parsear configuración de tamaños
   */
  private function parseSizes()
  {
    $this->sizes = [];

    foreach (explode(',', IMAGE_SIZES) as $sizeConfig) {
      $parts = explode(':', trim($sizeConfig));
      if (count($parts) !== 2) continue;

      list($name, $dimensions) = $parts;

      // Detectar si tiene crop (ejemplo: 300x300c)
      $hasCrop = substr($dimensions, -1) === 'c';
      if ($hasCrop) {
        $dimensions = rtrim($dimensions, 'c');
      }

      $sizeParts = explode('x', $dimensions);
      if (count($sizeParts) !== 2) continue;

      $this->sizes[$name] = [
        'width' => (int)$sizeParts[0],
        'height' => (int)$sizeParts[1],
        'crop' => $hasCrop
      ];
    }
  }

  private function generateSizedFilename($originalFilename, $sizeName)
  {
    $info = pathinfo($originalFilename);
    return $info['filename'] . '_' . $sizeName . '.' . $info['extension'];
  }

  private function generateWebPPath($originalPath)
  {
    return preg_replace('/\.[^.]+$/', '.webp', $originalPath);
  }

  private function getUploadPath()
  {
    $path = $_SERVER['DOCUMENT_ROOT'] . '/' . UPLOAD_PATH_IMAGES;
    if (!is_dir($path)) {
      mkdir($path, 0755, true);
    }
    return $path;
  }

  private function getQuality($mimeType)
  {
    switch ($mimeType) {
      case 'image/jpeg':
        return IMAGE_QUALITY_JPG;
      case 'image/png':
        return IMAGE_QUALITY_PNG;
      case 'image/webp':
        return IMAGE_QUALITY_WEBP;
      default:
        return 85;
    }
  }
}
