<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$db = ConectarDB();

$id = $_POST['id'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$vehiculo = trim($_POST['tipoVehiculo'] ?? '');
$placa = trim($_POST['placa'] ?? '');
$zona = trim($_POST['zona'] ?? '');
$estado = trim($_POST['estado'] ?? 'disponible');

// Validación básica
if (!$nombre || !$telefono || !$vehiculo || !$placa || !$estado) {
    echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios']);
    exit;
}

// Obtener id_zona
$stmtZona = $db->prepare("SELECT id_zona FROM zonas WHERE nombre = ?");
$stmtZona->bind_param("s", $zona);
$stmtZona->execute();
$resultZona = $stmtZona->get_result();
$id_zona = $resultZona->fetch_assoc()['id_zona'] ?? null;
$stmtZona->close();

if ($id) {
    // Actualizar
    $stmt = $db->prepare("UPDATE domiciliarios SET nombre=?, telefono=?, vehiculo=?, placa=?, id_zona=?, estado=? WHERE id_domiciliario=?");
    $stmt->bind_param("ssssisi", $nombre, $telefono, $vehiculo, $placa, $id_zona, $estado, $id);
} else {
    // Crear
    $stmt = $db->prepare("INSERT INTO domiciliarios (nombre, telefono, vehiculo, placa, id_zona, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $nombre, $telefono, $vehiculo, $placa, $id_zona, $estado);
}

$success = $stmt->execute();
$stmt->close();
$db->close();

echo json_encode(['success' => $success]);
