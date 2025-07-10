<?php
header('Content-Type: application/json');
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = isset($_POST['query']) ? $_POST['query'] : '';
    $sql = "SELECT p.id_pedido, c.nombre AS cliente, d.nombre AS domiciliario, p.estado, p.fecha_pedido
            FROM pedidos p
            LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
            WHERE c.nombre LIKE :query OR p.id_pedido LIKE :query
            ORDER BY p.fecha_pedido DESC
            LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['query' => "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>