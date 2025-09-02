<?php

/**
 * Configuración dinámica para JavaScript
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Cargar configuración
require_once('../../includes/config.inc.php');

// Enviar header de JavaScript
header('Content-Type: application/javascript; charset=utf-8');

// No debe haber salida HTML antes de este punto
?>

// === CONFIGURACIÓN DINÁMICA DESDE PHP ===
window.API_BASE_URL = "<?php echo APP_URL_BACKEND; ?>/";
window.FRONTEND_URL = "<?php echo APP_URL_FRONTEND; ?>";

// === CONFIGURACIÓN DE LA APLICACIÓN ===
window.APP_CONFIG = {
API_BASE_URL: window.API_BASE_URL,
FRONTEND_URL: window.FRONTEND_URL,
MAX_FILE_SIZE: <?php echo MAX_FILE_SIZE; ?>,
UPLOAD_PATH: "<?php echo UPLOAD_PATH; ?>",
};

console.log('Configuración cargada:', window.APP_CONFIG);