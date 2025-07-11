<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios;charset=utf8mb4", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
}

function ConectarDB() {
    $conexion = new mysqli("localhost", "root", "root", "sm_domicilios");

    if ($conexion->connect_error) {
        http_response_code(500);
        die(json_encode(['error' => 'Error de conexión: ' . $conexion->connect_error]));
    }

    $conexion->set_charset("utf8mb4");
    return $conexion;
}
?>
