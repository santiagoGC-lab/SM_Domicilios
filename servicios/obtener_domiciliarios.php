<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$db = ConectarDB();
$result = $db->query("SELECT d.id_domiciliario, d.nombre, d.telefono, d.vehiculo, d.placa, z.nombre AS zona, d.estado 
                      FROM domiciliarios d 
                      LEFT JOIN zonas z ON d.id_zona = z.id_zona");
$domiciliarios = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($domiciliarios);
$db->close();
