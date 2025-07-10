<?php
require_once 'conexion.php'; // Asegúrate de tener la conexión

header('Content-Type: application/json');
$db = ConectarDB();

$query = "SELECT * FROM zonas ORDER BY id_zona DESC";
$resultado = $db->query($query);

$zonas = [];

while ($fila = $resultado->fetch_assoc()) {
    $zonas[] = $fila;
}

echo json_encode($zonas);