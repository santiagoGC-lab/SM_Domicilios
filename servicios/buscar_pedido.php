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
        // Si no hay búsqueda, devolver los últimos 10 pedidos no archivados
        $sql = "SELECT p.id_pedido, c.nombre AS cliente, d.nombre AS domiciliario, p.estado, p.fecha_pedido, p.tiempo_estimado
                FROM pedidos p
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
                WHERE p.movido_historico = 0
                ORDER BY p.fecha_pedido DESC
                LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else {
        // Búsqueda por ID de pedido, nombre de cliente o documento
        $sql = "SELECT p.id_pedido, c.nombre AS cliente, d.nombre AS domiciliario, p.estado, p.fecha_pedido, p.tiempo_estimado
                FROM pedidos p
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
                WHERE (c.nombre LIKE :query 
                   OR c.documento LIKE :query 
                   OR p.id_pedido = :id_pedido)
                   AND p.movido_historico = 0
                ORDER BY p.fecha_pedido DESC
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