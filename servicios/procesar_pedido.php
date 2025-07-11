<?php
session_start();
header('Content-Type: application/json');
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios", "root", "root"); // Cambia "tu_contraseña"
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validar datos
    $id_cliente = $_POST['id_cliente'] ?? null;
    $id_domiciliario = $_POST['id_domiciliario'] ?? null;
    $id_zona = $_POST['id_zona'] ?? null;
    $estado = $_POST['estado'] ?? 'pendiente';
    $bolsas = $_POST['bolsas'] ?? null;
    $total = $_POST['total'] ?? null;

    if (!$id_cliente || !$id_domiciliario || !$id_zona || !$bolsas || !$total) {
        throw new Exception("Todos los campos son obligatorios, incluido el repartidor");
    }

    // Verificar que el cliente exista y esté activo
    $stmt = $pdo->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ? AND estado = 'activo'");
    $stmt->execute([$id_cliente]);
    if (!$stmt->fetch()) {
        throw new Exception("Cliente no encontrado o inactivo");
    }

    // Verificar que la zona exista y esté activa
    $stmt = $pdo->prepare("SELECT id_zona FROM zonas WHERE id_zona = ? AND estado = 'activo'");
    $stmt->execute([$id_zona]);
    if (!$stmt->fetch()) {
        throw new Exception("Zona no encontrada o inactiva");
    }

    // Verificar que el domiciliario exista y esté disponible
    $stmt = $pdo->prepare("SELECT id_domiciliario FROM domiciliarios WHERE id_domiciliario = ? AND estado = 'disponible'");
    $stmt->execute([$id_domiciliario]);
    if (!$stmt->fetch()) {
        throw new Exception("Domiciliario no encontrado o no disponible");
    }

    // Iniciar transacción
    $pdo->beginTransaction();

    // Actualizar el estado y zona del domiciliario
    $stmt = $pdo->prepare("UPDATE domiciliarios SET estado = 'ocupado', id_zona = ? WHERE id_domiciliario = ?");
    $stmt->execute([$id_zona, $id_domiciliario]);

    // Insertar pedido
    $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, id_domiciliario, id_zona, estado, cantidad_paquetes, total) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id_cliente, $id_domiciliario, $id_zona, $estado, $bolsas, $total]);
    $id_pedido = $pdo->lastInsertId();

    // Registrar actividad
    $stmt = $pdo->prepare("INSERT INTO actividad_reciente (tipo_actividad, descripcion, id_usuario, id_pedido) VALUES (?, ?, ?, ?)");
    $descripcion = "Nuevo pedido creado para el cliente ID $id_cliente en la zona ID $id_zona asignado al domiciliario ID $id_domiciliario";
    $stmt->execute(['pedido_asignado', $descripcion, $_SESSION['usuario_id'], $id_pedido]);

    // Confirmar transacción
    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
