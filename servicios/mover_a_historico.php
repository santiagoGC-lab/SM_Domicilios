<?php
require_once 'conexion.php';

/**
 * Mueve un pedido al histórico automáticamente
 * @param int $id_pedido ID del pedido a mover
 * @param string $motivo_cancelacion Motivo si es cancelación (opcional)
 * @param string $observaciones Observaciones adicionales (opcional)
 * @return bool True si se movió correctamente, False en caso contrario
 */
function moverPedidoAHistorico($id_pedido, $motivo_cancelacion = null, $observaciones = null) {
    global $pdo;
    
    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Obtener datos completos del pedido con joins
        $stmt = $pdo->prepare("
            SELECT 
                p.id_pedido, p.id_cliente, p.id_zona, p.id_domiciliario, p.estado, 
                p.cantidad_paquetes, p.total, p.tiempo_estimado, p.fecha_pedido,
                c.nombre as cliente_nombre, c.documento as cliente_documento, 
                c.telefono as cliente_telefono, c.direccion as cliente_direccion,
                z.nombre as zona_nombre, z.tarifa_base as zona_tarifa,
                d.nombre as domiciliario_nombre, d.telefono as domiciliario_telefono
            FROM pedidos p
            LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN zonas z ON p.id_zona = z.id_zona
            LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
            WHERE p.id_pedido = ? AND p.movido_historico = FALSE
        ");
        
        $stmt->execute([$id_pedido]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            throw new Exception("Pedido no encontrado o ya fue movido al histórico");
        }
        
        // Verificar que el estado sea elegible para histórico
        if (!in_array($pedido['estado'], ['entregado', 'cancelado'])) {
            throw new Exception("Solo se pueden mover pedidos entregados o cancelados al histórico");
        }
        
        // Obtener ID del usuario actual si está disponible
        $usuario_proceso = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
        
        // Insertar en histórico
        $stmt = $pdo->prepare("
            INSERT INTO historico_pedidos (
                id_pedido_original, id_cliente, id_zona, id_domiciliario, estado,
                cantidad_paquetes, total, tiempo_estimado, fecha_pedido, fecha_completado,
                motivo_cancelacion, observaciones,
                cliente_nombre, cliente_documento, cliente_telefono, cliente_direccion,
                zona_nombre, zona_tarifa,
                domiciliario_nombre, domiciliario_telefono,
                usuario_proceso
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, NOW(),
                ?, ?,
                ?, ?, ?, ?,
                ?, ?,
                ?, ?,
                ?
            )
        ");
        
        $result = $stmt->execute([
            $pedido['id_pedido'], $pedido['id_cliente'], $pedido['id_zona'], $pedido['id_domiciliario'], $pedido['estado'],
            $pedido['cantidad_paquetes'], $pedido['total'], $pedido['tiempo_estimado'], $pedido['fecha_pedido'],
            $motivo_cancelacion, $observaciones,
            $pedido['cliente_nombre'], $pedido['cliente_documento'], $pedido['cliente_telefono'], $pedido['cliente_direccion'],
            $pedido['zona_nombre'], $pedido['zona_tarifa'],
            $pedido['domiciliario_nombre'], $pedido['domiciliario_telefono'],
            $usuario_proceso
        ]);
        
        if (!$result) {
            throw new Exception("Error al insertar en el histórico");
        }
        
        // Marcar el pedido como movido al histórico
        $stmt = $pdo->prepare("UPDATE pedidos SET movido_historico = TRUE WHERE id_pedido = ?");
        $result = $stmt->execute([$id_pedido]);
        
        if (!$result) {
            throw new Exception("Error al marcar pedido como movido");
        }
        
        // Confirmar transacción
        $pdo->commit();
        
        // Log del movimiento
        error_log("Pedido ID $id_pedido movido al histórico exitosamente");
        
        return true;
        
    } catch (Exception $e) {
        // Revertir transacción
        $pdo->rollBack();
        error_log("Error al mover pedido $id_pedido al histórico: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica y mueve automáticamente pedidos que deberían estar en el histórico
 * @return int Número de pedidos movidos
 */
function procesarPedidosParaHistorico() {
    global $pdo;
    
    try {
        // Buscar pedidos entregados o cancelados que no estén en el histórico
        $stmt = $pdo->prepare("
            SELECT id_pedido 
            FROM pedidos 
            WHERE estado IN ('entregado', 'cancelado') 
            AND movido_historico = FALSE
        ");
        
        $stmt->execute();
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $movidos = 0;
        foreach ($pedidos as $pedido) {
            if (moverPedidoAHistorico($pedido['id_pedido'])) {
                $movidos++;
            }
        }
        
        return $movidos;
        
    } catch (Exception $e) {
        error_log("Error en procesarPedidosParaHistorico: " . $e->getMessage());
        return 0;
    }
}

// Si se llama directamente el archivo (para pruebas o cron)
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    session_start();
    
    echo "<h1>🔄 Procesador de Histórico de Pedidos</h1>";
    
    $movidos = procesarPedidosParaHistorico();
    
    if ($movidos > 0) {
        echo "<p style='color: green;'>✅ Se movieron $movidos pedidos al histórico</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ No hay pedidos para mover al histórico</p>";
    }
    
    echo "<p><a href='debug_eliminar_pedido.php'>← Volver al Debug</a></p>";
    echo "<p><a href='../vistas/pedidos.php'>← Ir a Gestión de Pedidos</a></p>";
}
?>