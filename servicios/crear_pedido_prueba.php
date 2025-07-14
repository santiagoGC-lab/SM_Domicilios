<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once 'conexion.php';

try {
    // Obtener un cliente existente
    $stmt = $pdo->query("SELECT id_cliente FROM clientes LIMIT 1");
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        throw new Exception('No hay clientes en la base de datos');
    }
    
    // Obtener una zona existente
    $stmt = $pdo->query("SELECT id_zona FROM zonas LIMIT 1");
    $zona = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$zona) {
        throw new Exception('No hay zonas en la base de datos');
    }
    
    // Obtener un domiciliario existente
    $stmt = $pdo->query("SELECT id_domiciliario FROM domiciliarios LIMIT 1");
    $domiciliario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$domiciliario) {
        throw new Exception('No hay domiciliarios en la base de datos');
    }
    
    // Insertar pedido de prueba
    $stmt = $pdo->prepare("
        INSERT INTO pedidos (id_cliente, id_zona, id_domiciliario, estado, cantidad_paquetes, total, tiempo_estimado, fecha_pedido) 
        VALUES (?, ?, ?, 'pendiente', 2, 25000.00, 30, NOW())
    ");
    
    $stmt->execute([$cliente['id_cliente'], $zona['id_zona'], $domiciliario['id_domiciliario']]);
    
    $nuevo_id = $pdo->lastInsertId();
    
    echo "<h1>✅ Pedido de Prueba Creado</h1>";
    echo "<p><strong>ID del nuevo pedido:</strong> $nuevo_id</p>";
    echo "<p><strong>Estado:</strong> pendiente</p>";
    echo "<p><strong>Este pedido SÍ se puede eliminar</strong></p>";
    echo "<p><a href='debug_eliminar_pedido.php'>← Volver al Debug</a></p>";
    echo "<p><a href='../vistas/pedidos.php'>← Ir a Gestión de Pedidos</a></p>";
    
} catch (Exception $e) {
    echo "<h1>❌ Error</h1>";
    echo "<p>Error al crear pedido de prueba: " . $e->getMessage() . "</p>";
    echo "<p><a href='debug_eliminar_pedido.php'>← Volver al Debug</a></p>";
}
?>