<?php
require_once 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$id = $_POST['id'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$ciudad = $_POST['ciudad'] ?? '';
$tarifa = $_POST['tarifa'] ?? 0;
$estado = $_POST['estado'] ?? 'activo';

if (empty($nombre) || empty($ciudad) || !is_numeric($tarifa) || !in_array($estado, ['activo', 'inactivo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$db = ConectarDB();

if ($id) {
    // Actualizar zona existente
    $stmt = $db->prepare("UPDATE zonas SET nombre = ?, ciudad = ?, tarifa_base = ?, estado = ? WHERE id_zona = ?");
    $stmt->bind_param("ssdsi", $nombre, $ciudad, $tarifa, $estado, $id);
} else {
    // Crear nueva zona
    $stmt = $db->prepare("INSERT INTO zonas (nombre, ciudad, tarifa_base, estado, fecha_creacion) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssds", $nombre, $ciudad, $tarifa, $estado);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}