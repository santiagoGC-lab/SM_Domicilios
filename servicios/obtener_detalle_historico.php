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
        throw new Exception('ID de histórico requerido');
    }

    $id_historico = intval($_GET['id']);
    
    $stmt = $pdo->prepare("
        SELECT h.*, u.nombre as usuario_nombre
        FROM historico_pedidos h
        LEFT JOIN usuarios u ON h.usuario_proceso = u.id_usuario
        WHERE h.id_historico = ?
    ");
    
    $stmt->execute([$id_historico]);
    $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$detalle) {
        throw new Exception('Registro de histórico no encontrado');
    }
    
    // Formatear datos para el frontend
    $detalle['total'] = floatval($detalle['total']);
    $detalle['zona_tarifa'] = floatval($detalle['zona_tarifa']);
    
    echo json_encode($detalle);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>