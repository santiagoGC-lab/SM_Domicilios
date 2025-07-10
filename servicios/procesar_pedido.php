<?php
session_start();
header('Content-Type: application/json');
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validar datos
    $id_cliente = $_POST['id_cliente'] ?? null;
    $id_zona = $_POST['id_zona'] ?? null;
    $estado = $_POST['estado'] ?? 'pendiente';
    $bolsas = $_POST['bolsas'] ?? null;
    $total = $_POST['total'] ?? null;

    if (!$id_cliente || !$id_zona || !$bolsas || !$total) {
        throw new Exception("Todos los campos son obligatorios");
    }

    // Verificar que el cliente y la zona existan
    $stmt = $pdo->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ? AND estado = 'activo'");
    $stmt->execute([$id_cliente]);
    if (!$stmt->fetch()) {
        throw new Exception("Cliente no encontrado o inactivo");
    }

    $stmt = $pdo->prepare("SELECT id_zona FROM zonas WHERE id_zona = ? AND estado = 'activo'");
    $stmt->execute([$id_zona]);
    if (!$stmt->fetch()) {
        throw new Exception("Zona no encontrada o inactiva");
    }

    // Insertar pedido
    $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, id_zona, estado, cantidad_paquetes, total) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id_cliente, $id_zona, $estado, $bolsas, $total]);
    $id_pedido = $pdo->lastInsertId();

    // Registrar actividad
    $stmt = $pdo->prepare("INSERT INTO actividad_reciente (tipo_actividad, descripcion, id_usuario, id_pedido) VALUES (?, ?, ?, ?)");
    $descripcion = "Nuevo pedido creado para el cliente ID $id_cliente en la zona ID $id_zona";
    $stmt->execute(['pedido_asignado', $descripcion, $_SESSION['usuario_id'], $id_pedido]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>