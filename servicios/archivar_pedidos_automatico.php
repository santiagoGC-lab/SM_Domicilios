<?php
require_once 'conexion.php';

try {
    $dias_retener = 7; // Valor fijo para días de retención

    // Iniciar transacción
    $pdo->beginTransaction();

    // Obtener pedidos entregados o cancelados con más de 7 días
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre AS cliente_nombre, c.documento AS cliente_documento, c.telefono AS cliente_telefono, c.direccion AS cliente_direccion,
               z.nombre AS zona_nombre, z.tarifa_base AS zona_tarifa, d.nombre AS domiciliario_nombre, d.telefono AS domiciliario_telefono
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN zonas z ON p.id_zona = z.id_zona
        LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
        WHERE p.estado IN ('entregado', 'cancelado') 
        AND p.movido_historico = 0
        AND p.fecha_pedido < NOW() - INTERVAL ? DAY
    ");
    $stmt->execute([$dias_retener]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pedidos as $pedido) {
        // Insertar en historico_pedidos
        $stmt = $pdo->prepare("
            INSERT INTO historico_pedidos (
                id_pedido_original, id_cliente, id_zona, id_domiciliario, estado, cantidad_paquetes, total, tiempo_estimado, fecha_pedido, fecha_completado,
                cliente_nombre, cliente_documento, cliente_telefono, cliente_direccion, zona_nombre, zona_tarifa, domiciliario_nombre, domiciliario_telefono, usuario_proceso
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, NULL)
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
            $pedido['domiciliario_telefono']
        ]);

        // Registrar actividad
        $stmt = $pdo->prepare("
            INSERT INTO actividad_reciente (tipo_actividad, descripcion, id_usuario, id_pedido)
            VALUES ('pedido_archivado', ?, NULL, ?)
        ");
        $descripcion = "Pedido #{$pedido['id_pedido']} archivado automáticamente";
        $stmt->execute([$descripcion, $pedido['id_pedido']]);

        // Marcar como movido
        $stmt = $pdo->prepare("UPDATE pedidos SET movido_historico = 1 WHERE id_pedido = ?");
        $stmt->execute([$pedido['id_pedido']]);
    }

    // Confirmar transacción
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Pedidos archivados automáticamente']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>