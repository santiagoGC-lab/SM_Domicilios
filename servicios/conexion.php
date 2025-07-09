<?php
function ConectarDB() {
    $conexion = new mysqli("localhost", "root", "root", "sm_domicilios");

    if ($conexion->connect_errno) {
        die("No se ha podido conectar con la base de datos: " . $conexion->connect_error);
    }
    
    mysqli_set_charset($conexion, 'utf8');
    return $conexion;
}
?>