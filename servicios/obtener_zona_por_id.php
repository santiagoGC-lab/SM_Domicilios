<?php
require_once 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$id = $_GET['id'] ?? '';

if (empty($id) || !is_numeric($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$db = ConectarDB();
$stmt = $db->prepare("SELECT * FROM zonas WHERE id_zona = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Zona no encontrada']);
    exit;
}

$zona = $result->fetch_assoc();
echo json_encode($zona);