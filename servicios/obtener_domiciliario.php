<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$db = ConectarDB();
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo json_encode([]);
    exit;
}

$stmt = $db->prepare("SELECT d.id_domiciliario, d.nombre, d.telefono, d.vehiculo, d.placa, z.nombre AS zona, d.estado 
                      FROM domiciliarios d 
                      LEFT JOIN zonas z ON d.id_zona = z.id_zona 
                      WHERE d.id_domiciliario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_assoc());
$stmt->close();
$db->close();
