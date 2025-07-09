<?php
require_once 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de cliente inválido']);
    exit;
}

try {
    $db = ConectarDB();

    $stmt = $db->prepare("DELETE FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Cliente no encontrado o ya eliminado']);
    }

    $stmt->close();
    $db->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar cliente', 'detalle' => $e->getMessage()]);
}
