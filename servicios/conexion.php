<?php
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
