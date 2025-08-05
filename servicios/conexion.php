<?php
// Configuración de zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de base de datos centralizada
define('DB_HOST', 'localhost');
define('DB_NAME', 'sm_domicilios');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

// Conexión PDO global (para vistas que ya la usan)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log("Error de conexión PDO: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}

// Función MySQLi (mantener compatibilidad con servicios existentes)
function ConectarDB()
{
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conexion->connect_error) {
        error_log("Error de conexión MySQLi: " . $conexion->connect_error);
        http_response_code(500);
        die(json_encode(['error' => 'Error de conexión a la base de datos']));
    }

    $conexion->set_charset(DB_CHARSET);
    return $conexion;
}

// Función helper para obtener PDO (nueva)
function getPDO()
{
    global $pdo;
    return $pdo;
}

// Función para cerrar conexiones de manera segura
function cerrarConexion($conexion)
{
    if ($conexion instanceof mysqli) {
        $conexion->close();
    }
    // PDO se cierra automáticamente
}
