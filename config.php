<?php
// Archivo de configuración central para SM_Domicilios
// Aquí se centralizan todas las configuraciones del proyecto

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sm_domicilios');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Configuración de la aplicación
define('APP_NAME', 'SM Domicilios');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/SM_Domicilios');

// Configuración de rutas
define('ROOT_PATH', __DIR__);
define('SERVICES_PATH', ROOT_PATH . '/servicios');
define('VIEWS_PATH', ROOT_PATH . '/vistas');
define('COMPONENTS_PATH', ROOT_PATH . '/componentes');
define('TESTS_PATH', ROOT_PATH . '/tests');

// Configuración de sesión
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// Configuración de archivos
define('MAX_FILE_SIZE', 5242880); // 5MB en bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Configuración de reportes
define('REPORTS_PATH', ROOT_PATH . '/reportes');
define('PDF_PATH', REPORTS_PATH . '/pdf');

// Función para incluir archivos de servicios
function includeService($serviceName) {
    $serviceFile = SERVICES_PATH . '/' . $serviceName . '.php';
    if (file_exists($serviceFile)) {
        require_once $serviceFile;
    } else {
        throw new Exception("Servicio no encontrado: " . $serviceName);
    }
}

// Función para obtener la URL de un servicio
function getServiceUrl($serviceName) {
    return APP_URL . '/servicios/' . $serviceName . '.php';
}

// Función para obtener la ruta de una vista
function getViewPath($viewName) {
    return VIEWS_PATH . '/' . $viewName . '.php';
}

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Función para redirigir
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

// Función para mostrar mensajes de error
function showError($message) {
    return '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}

// Función para mostrar mensajes de éxito
function showSuccess($message) {
    return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

// Configuración de zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de errores (solo para desarrollo)
define('DEVELOPMENT_MODE', true); // Cambiar a false en producción
if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?> 