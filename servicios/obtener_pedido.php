<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once 'conexion.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('ID de pedido requerido');
    }

    $id_pedido = intval($_GET['id']);
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.documento, c.nombre AS nombre_cliente
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
        WHERE p.id_pedido = ?
    ");
    
    $stmt->execute([$id_pedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        throw new Exception('Pedido no encontrado');
    }
    
    echo json_encode($pedido);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 