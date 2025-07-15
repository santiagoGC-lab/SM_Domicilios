<?php
header('Content-Type: application/json');
require_once 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_historico'])) {
    echo json_encode(['error' => 'Solicitud inválida']);
    exit;
}

$id = intval($_POST['id_historico']);

$db = ConectarDB();
$stmt = $db->prepare("SELECT * FROM historico_pedidos WHERE id_historico = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();
$stmt->close();
$db->close();

if (!$pedido) {
    echo json_encode(['error' => 'Pedido archivado no encontrado']);
    exit;
}

echo json_encode($pedido); 