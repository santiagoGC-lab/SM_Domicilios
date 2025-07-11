<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once 'conexion.php';

try {
    if (!isset($_POST['id_pedido']) || !isset($_POST['nuevo_estado'])) {
        throw new Exception('ID de pedido y nuevo estado requeridos');
    }

    $id_pedido = intval($_POST['id_pedido']);
    $nuevo_estado = $_POST['nuevo_estado'];
    
    // Validar estado válido
    $estados_validos = ['pendiente', 'entregado', 'cancelado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        throw new Exception('Estado no válido');
    }
    
    // Obtener información actual del pedido
    $stmt = $pdo->prepare("SELECT id_domiciliario, estado FROM pedidos WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        throw new Exception('Pedido no encontrado');
    }
    
    // Actualizar el pedido
    $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
    $stmt->execute([$nuevo_estado, $id_pedido]);
    
    // Manejar estado del domiciliario
    $domiciliario_id = $pedido['id_domiciliario'];
    if ($domiciliario_id) {
        if ($nuevo_estado === 'entregado' || $nuevo_estado === 'cancelado') {
            // Marcar domiciliario como disponible
            $stmt = $pdo->prepare("UPDATE domiciliarios SET estado = 'disponible' WHERE id_domiciliario = ?");
            $stmt->execute([$domiciliario_id]);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Estado actualizado exitosamente']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 