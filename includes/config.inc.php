<?php

// Cargar autoload de Composer una sola vez
if (!class_exists('Intervention\Image\ImageManager')) {
  require_once __DIR__ . '/../vendor/autoload.php';
}

/**
 * Archivo de configuración principal
 * Lee variables del archivo .env y define constantes globales
 */

// Función para cargar archivo .env
function loadEnv($path)
{
  if (!file_exists($path)) {
    throw new Exception("El archivo .env no existe en: " . $path);
  }

  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) {
      continue; // Saltar comentarios
    }

    if (strpos($line, '=') !== false) {
      list($name, $value) = explode('=', $line, 2);
      $name = trim($name);
      $value = trim($value);

      // Remover comillas si existen
      if (preg_match('/^".*"$/', $value) || preg_match('/^\'.*\'$/', $value)) {
        $value = substr($value, 1, -1);
      }

      $_ENV[$name] = $value;
      putenv("$name=$value");
    }
  }
}

// Detectar el directorio raíz del proyecto
$rootPath = __DIR__ . '/../';  // Por defecto: desde includes/ al directorio raíz

// Cargar archivo .env
try {
  loadEnv($rootPath . '.env');
} catch (Exception $e) {
  die('Error cargando configuración: ' . $e->getMessage());
}

// Función para obtener valor del entorno con valor por defecto
function env($key, $default = null)
{
  return $_ENV[$key] ?? getenv($key) ?: $default;
}

// === CONFIGURACIÓN DE BASE DE DATOS ===
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'lc_unique'));
define('DB_USER', env('DB_USER', 'homestead'));
define('DB_PASS', env('DB_PASS', 'secret'));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// DSN para PDO
define('DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

// === CONFIGURACIÓN DE URLs ===
define('APP_URL_FRONTEND', env('APP_URL_FRONTEND', 'http://unique.test'));
define('APP_URL_BACKEND', env('APP_URL_BACKEND', 'http://unique.test/backend'));

// === CONFIGURACIÓN DE EMAIL ===
define('EMAIL_SENDER', env('EMAIL_SENDER', 'info@unique.com'));
define('EMAIL_RECIPIENT', env('EMAIL_RECIPIENT', 'info@unique.com'));
define('EMAIL_BCC', env('EMAIL_BCC', 'admin@unique.com'));
define('EMAIL_SENDER_SHOW', env('EMAIL_SENDER_SHOW', 'noreply@unique.com'));
define('NAME_SENDER_SHOW', env('NAME_SENDER_SHOW', 'Unique Talent Solutions'));

// === CONFIGURACIÓN DE ARCHIVOS ===
define('UPLOAD_PATH', env('UPLOAD_PATH', 'uploads/cv/'));
define('MAX_FILE_SIZE', env('MAX_FILE_SIZE', 5242880)); // 5MB por defecto

// === CONFIGURACIÓN ESPECÍFICA PARA IMÁGENES DE POSTS ===
define('UPLOAD_PATH_IMAGES', env('UPLOAD_PATH_IMAGES', 'uploads/images/'));
define('MAX_IMAGE_SIZE', env('MAX_IMAGE_SIZE', 10485760)); // 10MB por defecto
define('ALLOWED_IMAGE_TYPES', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp'));

// === CONFIGURACIÓN AVANZADA DE IMÁGENES ===
// Configuración para tipos específicos de imágenes
define('IMAGE_SIZES_BY_TYPE', json_encode([
  'listing' => [
    'width' => 600,
    'height' => 600,
    'crop' => true,
    'quality' => 85
  ],
  'header' => [
    'width' => 1920,
    'height' => 750,
    'crop' => true,
    'quality' => 85
  ],
  'content' => [
    'width' => 1280,
    'height' => 800,
    'crop' => true,
    'quality' => 85
  ]
]));

// Configuración general de imágenes 
define('IMAGE_SIZES', env('IMAGE_SIZES', 'thumbnail:300x300,medium:800x600,large:1200x800'));
define('IMAGE_QUALITY_JPG', env('IMAGE_QUALITY_JPG', 85));
define('IMAGE_QUALITY_PNG', env('IMAGE_QUALITY_PNG', 90));
define('IMAGE_QUALITY_WEBP', env('IMAGE_QUALITY_WEBP', 80));
define('IMAGE_DRIVER', env('IMAGE_DRIVER', 'gd'));
define('IMAGE_CROP_POSITION', env('IMAGE_CROP_POSITION', 'center'));
define('GENERATE_WEBP', env('GENERATE_WEBP', true));

// === CONFIGURACIÓN DE TIMEZONE ===
date_default_timezone_set('America/Argentina/Buenos_Aires');

// === API KEY RECAPTCHA ===
define('RECAPTCHA_PUBLIC_KEY', env('RECAPTCHA_PUBLIC_KEY'));
define('RECAPTCHA_SECRET_KEY', env('RECAPTCHA_SECRET_KEY'));

// === CONFIGURACIÓN DE SESIONES ===
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// === MANEJO DE ERRORES ===
define('APP_DEBUG', env('APP_DEBUG', false));

if (env('APP_DEBUG')) {
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
} else {
  error_reporting(0);
  ini_set('display_errors', 0);
}
