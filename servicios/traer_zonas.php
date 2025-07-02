<?php
require_once '../servicios/conexion.php';
$conexion = conectarDB();

$id_zona = $_POST['id_zona'];
$nombre_zona = $_POST['nombre_zona'];
$barrio = $_POST['barrio'];

$actualizar = $conexion->prepare("UPDATE zonas SET nombre_zona=?, barrio=? WHERE id_zona=?");
$actualizar->bind_param("ssi", $nombre_zona, $barrio, $id_zona);
$actualizar->execute();

if ($actualizar->affected_rows > 0) {
    echo "ok";
} else {
    echo "error";
}
$conexion->close();
?>
