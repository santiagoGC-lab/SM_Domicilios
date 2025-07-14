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
    $usuario_id = $_SESSION['usuario_id'];

    // Iniciar transacción
    $pdo->beginTransaction();

    // Obtener datos del pedido
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre AS cliente_nombre, c.documento AS cliente_documento, c.telefono AS cliente_telefono, c.direccion AS cliente_direccion,
               z.nombre AS zona_nombre, z.tarifa_base AS zona_tarifa, d.nombre AS domiciliario_nombre, d.telefono AS domiciliario_telefono
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN zonas z ON p.id_zona = z.id_zona
        LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
        WHERE p.id_pedido = ? AND p.estado IN ('entregado', 'cancelado') AND p.movido_historico = 0
    ");
    $stmt->execute([$id_pedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new Exception('El pedido no está entregado, cancelado o ya fue archivado');
    }

    // Insertar en historico_pedidos
    $stmt = $pdo->prepare("
        INSERT INTO historico_pedidos (
            id_pedido_original, id_cliente, id_zona, id_domiciliario, estado, cantidad_paquetes, total, tiempo_estimado, fecha_pedido, fecha_completado,
            cliente_nombre, cliente_documento, cliente_telefono, cliente_direccion, zona_nombre, zona_tarifa, domiciliario_nombre, domiciliario_telefono, usuario_proceso
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $pedido['id_pedido'],
        $pedido['id_cliente'],
        $pedido['id_zona'],
        $pedido['id_domiciliario'],
        $pedido['estado'],
        $pedido['cantidad_paquetes'],
        $pedido['total'],
        $pedido['tiempo_estimado'],
        $pedido['fecha_pedido'],
        $pedido['cliente_nombre'],
        $pedido['cliente_documento'],
        $pedido['cliente_telefono'],
        $pedido['cliente_direccion'],
        $pedido['zona_nombre'],
        $pedido['zona_tarifa'],
        $pedido['domiciliario_nombre'],
        $pedido['domiciliario_telefono'],
        $usuario_id
    ]);

    // Registrar actividad en actividad_reciente
    $stmt = $pdo->prepare("
        INSERT INTO actividad_reciente (tipo_actividad, descripcion, id_usuario, id_pedido)
        VALUES ('pedido_archivado', ?, ?, ?)
    ");
    $descripcion = "Pedido #{$id_pedido} archivado por usuario #{$usuario_id}";
    $stmt->execute([$descripcion, $usuario_id, $id_pedido]);

    // Marcar el pedido como movido
    $stmt = $pdo->prepare("UPDATE pedidos SET movido_historico = 1 WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);

    // Confirmar transacción
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Pedido movido al historial exitosamente']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>