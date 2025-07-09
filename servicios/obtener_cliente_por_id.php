<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de cliente no proporcionado']);
    exit;
}

try {
    $db = ConectarDB();
    $stmt = $db->prepare("SELECT id_cliente, nombre, documento, telefono, direccion, barrio, tipo_cliente FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($cliente = $result->fetch_assoc()) {
        echo json_encode($cliente);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Cliente no encontrado']);
    }

    $stmt->close();
    $db->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener cliente', 'detalle' => $e->getMessage()]);
}
