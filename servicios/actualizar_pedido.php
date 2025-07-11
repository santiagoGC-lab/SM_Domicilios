<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once 'conexion.php';

try {
    // Validar datos requeridos
    $required_fields = ['id_pedido', 'id_cliente', 'id_zona', 'id_domiciliario', 'estado', 'bolsas', 'total'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Campo requerido: $field");
        }
    }
    
    $id_pedido = intval($_POST['id_pedido']);
    $id_cliente = intval($_POST['id_cliente']);
    $id_zona = intval($_POST['id_zona']);
    $id_domiciliario = intval($_POST['id_domiciliario']);
    $estado = $_POST['estado'];
    $cantidad_paquetes = intval($_POST['bolsas']);
    $total = floatval($_POST['total']);
    $tiempo_estimado = intval($_POST['tiempo_estimado'] ?? 30);
    
    // Obtener el estado anterior del pedido
    $stmt = $pdo->prepare("SELECT id_domiciliario, estado FROM pedidos WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);
    $pedido_anterior = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido_anterior) {
        throw new Exception('Pedido no encontrado');
    }
    
    // Validar que el cliente existe
    $stmt = $pdo->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ? AND estado = 'activo'");
    $stmt->execute([$id_cliente]);
    if (!$stmt->fetch()) {
        throw new Exception('Cliente no válido');
    }
    
    // Validar que la zona existe
    $stmt = $pdo->prepare("SELECT id_zona FROM zonas WHERE id_zona = ? AND estado = 'activo'");
    $stmt->execute([$id_zona]);
    if (!$stmt->fetch()) {
        throw new Exception('Zona no válida');
    }
    
    // Validar que el domiciliario existe
    $stmt = $pdo->prepare("SELECT id_domiciliario FROM domiciliarios WHERE id_domiciliario = ?");
    $stmt->execute([$id_domiciliario]);
    if (!$stmt->fetch()) {
        throw new Exception('Domiciliario no válido');
    }
    
    // Actualizar el pedido
    $stmt = $pdo->prepare("
        UPDATE pedidos 
        SET id_cliente = ?, id_zona = ?, id_domiciliario = ?, estado = ?, cantidad_paquetes = ?, total = ?, tiempo_estimado = ?
        WHERE id_pedido = ?
    ");
    
    $stmt->execute([$id_cliente, $id_zona, $id_domiciliario, $estado, $cantidad_paquetes, $total, $tiempo_estimado, $id_pedido]);
    
    // Manejar cambios de estado del domiciliario
    $domiciliario_anterior = $pedido_anterior['id_domiciliario'];
    $estado_anterior = $pedido_anterior['estado'];
    
    // Actualizar estado del domiciliario
    if ($estado === 'entregado' || $estado === 'cancelado') {
        $stmt = $pdo->prepare("UPDATE domiciliarios SET estado = 'disponible' WHERE id_domiciliario = ?");
        $stmt->execute([$id_domiciliario]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Pedido actualizado exitosamente']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 