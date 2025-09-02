<?php

/**
 * Configuración dinámica para JavaScript
 * Este archivo genera variables JS con valores del backend
 */

header('Content-Type: application/javascript; charset=utf-8');

require_once('../includes/config.inc.php');
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