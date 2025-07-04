<?php
function conectarDB() {
    $host = 'localhost';
    $db   = 'sm_domicilios';
    $user = 'root';
    $pass = 'root';
    
    $conexion = new mysqli($host, $user, $pass, $db);
    
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    $conexion->set_charset("utf8mb4");
    return $conexion;
}
