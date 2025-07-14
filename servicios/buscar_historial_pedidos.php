<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');
require_once 'conexion.php';

try {
    $query = isset($_POST['query']) ? trim($_POST['query']) : '';

    if (empty($query)) {
        // Si no hay búsqueda, devolver los últimos 10 pedidos archivados
        $sql = "SELECT id_pedido_original, cliente_nombre, domiciliario_nombre, estado, fecha_pedido, fecha_completado, total
                FROM historico_pedidos
                ORDER BY fecha_completado DESC
                LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else {
        // Búsqueda por ID de pedido, nombre de cliente o documento
        $sql = "SELECT id_pedido_original, cliente_nombre, domiciliario_nombre, estado, fecha_pedido, fecha_completado, total
                FROM historico_pedidos
                WHERE cliente_nombre LIKE :query 
                   OR cliente_documento LIKE :query 
                   OR id_pedido_original = :id_pedido
                ORDER BY fecha_completado DESC
                LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'query' => "%$query%",
            'id_pedido' => is_numeric($query) ? intval($query) : 0
        ]);
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>