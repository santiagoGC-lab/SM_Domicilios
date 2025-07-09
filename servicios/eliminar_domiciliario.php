<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$db = ConectarDB();
$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

$stmt = $db->prepare("DELETE FROM domiciliarios WHERE id_domiciliario = ?");
$stmt->bind_param("i", $id);
$success = $stmt->execute();
$stmt->close();
$db->close();

echo json_encode(['success' => $success]);
