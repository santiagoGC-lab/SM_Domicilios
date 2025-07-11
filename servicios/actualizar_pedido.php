<?php

header('Content-Type: application/json');
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión a la base de datos', 'detalles' => $e->getMessage()]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    

// Sanitizar entradas
$id_pedido = $_POST['id_pedido'] ?? null;
$id_cliente = $_POST['id_cliente'] ?? null;
$id_zona = $_POST['id_zona'] ?? null;
$id_domiciliario = $_POST['id_domiciliario'] ?? null;
$estado = $_POST['estado'] ?? 'pendiente';
$cantidad = $_POST['bolsas'] ?? null;
$total = $_POST['total'] ?? null;

$estados_validos = ['pendiente', 'en_camino', 'entregado', 'cancelado'];
if (!in_array($estado, $estados_validos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado inválido']);
    exit;
}

if (!$id_pedido || !is_numeric($id_pedido)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de pedido inválido']);
    exit;
}

if (!$id_cliente || !$id_zona || !$cantidad || !$total) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos del pedido']);
    exit;
}

$total = str_replace(',', '.', $total);
$total = floatval($total);
$cantidad = intval($cantidad);

$id_domiciliario = ($id_domiciliario === '' || $id_domiciliario === null) ? null : $id_domiciliario;

try {
    $sql = "UPDATE pedidos SET 
                id_cliente = :id_cliente,
                id_zona = :id_zona,
                id_domiciliario = :id_domiciliario,
                estado = :estado,
                cantidad_paquetes = :cantidad,
                total = :total
            WHERE id_pedido = :id_pedido";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->bindParam(':id_zona', $id_zona, PDO::PARAM_INT);
    $stmt->bindValue(':id_domiciliario', $id_domiciliario, $id_domiciliario === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
    $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
    $stmt->bindParam(':total', $total);
    $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);

    $stmt->execute();

    echo json_encode(['success' => true, 'mensaje' => 'Pedido actualizado correctamente']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el pedido', 'detalles' => $e->getMessage()]);
}


