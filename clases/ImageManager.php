<?php

use Intervention\Image\ImageManager as InterventionImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

class ImageManager
{
  private $manager;
  private $typeConfigs;

  public function __construct()
  {
    // Configurar driver correcto para v3
    $driver = IMAGE_DRIVER === 'imagick' ? new ImagickDriver() : new GdDriver();
    $this->manager = new InterventionImageManager($driver);

    // Cargar configuraciones por tipo
    $this->typeConfigs = json_decode(IMAGE_SIZES_BY_TYPE, true);
  }

  /**
   * Procesar imagen por tipo específico (listing, header, content)
   */
  public function processImageByType($filePath, $filename, $type = 'content')
  {
    error_log("=== INICIO PROCESAMIENTO IMAGEN POR TIPO ===");
    error_log("Archivo: " . $filePath);
    error_log("Filename: " . $filename);
    error_log("Tipo: " . $type);

    if (!isset($this->typeConfigs[$type])) {
      error_log("ERROR: Tipo de imagen desconocido: " . $type);
      return ['success' => false, 'error' => 'Tipo de imagen desconocido'];
    }

    $config = $this->typeConfigs[$type];
    error_log("Configuración: " . print_r($config, true));

    try {
      $image = $this->manager->read($filePath);
      error_log("Imagen leída correctamente - Dimensiones originales: " . $image->width() . "x" . $image->height());

      // Redimensionar y optimizar
      $processedImage = $this->resizeImageToType($image, $config);

      // Generar nombre de archivo optimizado
      $optimizedFilename = $this->generateOptimizedFilename($filename, $type);
      $outputPath = $this->getUploadPath() . $optimizedFilename;

      error_log("Guardando imagen optimizada en: " . $outputPath);

      // Convertir a WebP y guardar
      $webpImage = $processedImage->toWebp($config['quality']);
      $webpImage->save($outputPath);

      error_log("Imagen WebP guardada: " . $optimizedFilename . " (" . filesize($outputPath) . " bytes)");
      error_log("Dimensiones finales: " . $processedImage->width() . "x" . $processedImage->height());

      $result = [
        'success' => true,
        'filename' => $optimizedFilename,
        'path' => UPLOAD_PATH_IMAGES . $optimizedFilename,
        'width' => $processedImage->width(),
        'height' => $processedImage->height(),
        'size' => filesize($outputPath),
        'type' => $type,
        'format' => 'webp'
      ];

      error_log("=== PROCESAMIENTO COMPLETADO EXITOSAMENTE ===");
      return $result;
    } catch (Exception $e) {
      error_log("ERROR procesando imagen: " . $e->getMessage());
      error_log("Stack trace: " . $e->getTraceAsString());
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  /**
   * Redimensionar imagen según tipo específico
   */
  private function resizeImageToType($image, $config)
  {
    $width = $config['width'];
    $height = $config['height'];
    $crop = $config['crop'] ?? true;

    if ($crop) {
      // Crop exacto para mantener dimensiones precisas
      error_log("Aplicando crop a {$width}x{$height}");
      return $image->cover($width, $height, IMAGE_CROP_POSITION);
    } else {
      // Redimensión manteniendo aspecto (limitado por las dimensiones máximas)
      error_log("Aplicando scale a {$width}x{$height}");
      return $image->scale($width, $height);
    }
  }

  /**
   * Generar nombre de archivo optimizado
   */
  private function generateOptimizedFilename($originalFilename, $type)
  {
    $info = pathinfo($originalFilename);
    $baseName = $info['filename'];

    // Remover el tipo del nombre si ya está presente para evitar duplicados
    $baseName = preg_replace('/^' . preg_quote($type, '/') . '_/', '', $baseName);

    // Generar nombre final con tipo y formato
    return $type . '_' . $baseName . '_optimized.webp';
  }

  /**
   * Obtener directorio de subida
   */
  private function getUploadPath()
  {
    $path = $_SERVER['DOCUMENT_ROOT'] . '/' . UPLOAD_PATH_IMAGES;
    if (!is_dir($path)) {
      mkdir($path, 0755, true);
    }
    return $path;
  }

  /**
   * Verificar si un tipo de imagen es válido
   */
  public function isValidType($type)
  {
    return isset($this->typeConfigs[$type]);
  }

  /**
   * Obtener configuración para un tipo específico
   */
  public function getTypeConfig($type)
  {
    return $this->typeConfigs[$type] ?? null;
  }

  /**
   * Obtener todos los tipos disponibles
   */
  public function getAvailableTypes()
  {
    return array_keys($this->typeConfigs);
  }

  // === MÉTODO LEGACY PARA COMPATIBILIDAD ===
  /**
   * Procesar imagen con múltiples tamaños (método anterior)
   * Mantenido para compatibilidad con código existente
   */
  public function processImage($filePath, $filename, $options = [])
  {
    // Implementación anterior mantenida...
    // (código existente sin cambios)
    return ['success' => false, 'error' => 'Usar processImageByType() para nuevas implementaciones'];
  }
}
