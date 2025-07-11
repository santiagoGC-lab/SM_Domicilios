<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once 'conexion.php';

try {
    if (!isset($_POST['id_pedido']) || empty($_POST['id_pedido'])) {
        throw new Exception('ID de pedido requerido');
    }

    $id_pedido = intval($_POST['id_pedido']);
    
    // Verificar que el pedido existe y obtener información
    $stmt = $pdo->prepare("SELECT id_domiciliario, estado FROM pedidos WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        throw new Exception('Pedido no encontrado');
    }
    
    // Solo permitir eliminar pedidos pendientes o cancelados
    if ($pedido['estado'] === 'entregado') {
        throw new Exception('No se puede eliminar un pedido que ya fue entregado');
    }
    
    // Eliminar el pedido
    $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);
    
    echo json_encode(['success' => true, 'message' => 'Pedido eliminado exitosamente']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 