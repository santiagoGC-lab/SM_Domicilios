<?php
session_start();

// Habilitar logging de errores
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/eliminar_pedido.log');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    error_log("Intento de eliminar pedido sin autenticación. SESSION: " . print_r($_SESSION, true));
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once 'conexion.php';

try {
    // Log del intento de eliminación
    error_log("Usuario " . $_SESSION['usuario_id'] . " intentando eliminar pedido. POST data: " . print_r($_POST, true));
    
    // Validar que se recibió el ID del pedido
    if (!isset($_POST['id_pedido']) || empty($_POST['id_pedido'])) {
        error_log("Error: ID de pedido no proporcionado");
        throw new Exception('ID de pedido requerido');
    }

    $id_pedido = intval($_POST['id_pedido']);
    
    // Log del ID recibido
    error_log("Procesando eliminación del pedido ID: " . $id_pedido);
    
    // Verificar que el pedido existe y obtener información
    $stmt = $pdo->prepare("SELECT id_domiciliario, estado FROM pedidos WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        error_log("Error: Pedido ID " . $id_pedido . " no encontrado en la base de datos");
        throw new Exception('Pedido no encontrado');
    }
    
    // Log del pedido encontrado
    error_log("Pedido encontrado - Estado: " . $pedido['estado'] . ", Domiciliario: " . $pedido['id_domiciliario']);
    
    // Solo permitir eliminar pedidos pendientes o cancelados
    if ($pedido['estado'] === 'entregado') {
        error_log("Error: Intento de eliminar pedido entregado ID " . $id_pedido);
        throw new Exception('No se puede eliminar un pedido que ya fue entregado');
    }
    
    // Iniciar transacción para asegurar consistencia
    $pdo->beginTransaction();
    
    try {
        // Eliminar el pedido
        $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
        $result = $stmt->execute([$id_pedido]);
        
        if (!$result) {
            throw new Exception('Error al ejecutar la eliminación del pedido');
        }
        
        $filasAfectadas = $stmt->rowCount();
        error_log("Pedido eliminado. Filas afectadas: " . $filasAfectadas);
        
        // Si el pedido tenía un domiciliario asignado, actualizar su estado a disponible
        if (!empty($pedido['id_domiciliario'])) {
            $stmt = $pdo->prepare("UPDATE domiciliarios SET estado = 'disponible' WHERE id_domiciliario = ?");
            $result = $stmt->execute([$pedido['id_domiciliario']]);
            
            if ($result) {
                error_log("Domiciliario ID " . $pedido['id_domiciliario'] . " marcado como disponible");
            } else {
                error_log("Advertencia: No se pudo actualizar el estado del domiciliario ID " . $pedido['id_domiciliario']);
            }
        }
        
        // Confirmar transacción
        $pdo->commit();
        error_log("Pedido ID " . $id_pedido . " eliminado exitosamente por usuario " . $_SESSION['usuario_id']);
        
        echo json_encode(['success' => true, 'message' => 'Pedido eliminado exitosamente']);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error al eliminar pedido: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 