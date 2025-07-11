<?php
session_start();
$_SESSION['usuario_id'] = 1;

require_once 'servicios/conexion.php';

echo "<h2>Insertando datos de prueba</h2>";

try {
    // Insertar zonas de prueba
    $stmt = $pdo->prepare("INSERT IGNORE INTO zonas (nombre, ciudad, tarifa_base, estado) VALUES (?, ?, ?, ?)");
    
    $zonas = [
        ['Centro', 'Bogotá', 5000.00, 'activo'],
        ['Chapinero', 'Bogotá', 6000.00, 'activo'],
        ['Usaquén', 'Bogotá', 7000.00, 'activo'],
        ['Suba', 'Bogotá', 8000.00, 'activo']
    ];
    
    foreach ($zonas as $zona) {
        $stmt->execute($zona);
    }
    echo "<p style='color: green;'>✓ Zonas insertadas</p>";
    
    // Insertar domiciliarios de prueba
    $stmt = $pdo->prepare("INSERT IGNORE INTO domiciliarios (nombre, telefono, vehiculo, placa, estado) VALUES (?, ?, ?, ?, ?)");
    
    $domiciliarios = [
        ['Juan Pérez', '3001234567', 'Moto', 'ABC123', 'disponible'],
        ['Carlos López', '3002345678', 'Moto', 'DEF456', 'disponible'],
        ['Ana García', '3003456789', 'Bicicleta', 'GHI789', 'disponible'],
        ['Luis Rodríguez', '3004567890', 'Moto', 'JKL012', 'disponible']
    ];
    
    foreach ($domiciliarios as $domiciliario) {
        $stmt->execute($domiciliario);
    }
    echo "<p style='color: green;'>✓ Domiciliarios insertados</p>";
    
    // Insertar clientes de prueba (si no existen)
    $stmt = $pdo->prepare("INSERT IGNORE INTO clientes (nombre, documento, telefono, direccion, barrio, tipo_cliente, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $clientes = [
        ['María González', '12345678', '3001111111', 'Calle 123 #45-67', 'La Soledad', 'regular', 'activo'],
        ['Pedro Martínez', '87654321', '3002222222', 'Carrera 78 #90-12', 'Chapinero', 'vip', 'activo'],
        ['Sofia Ruiz', '11223344', '3003333333', 'Avenida 15 #23-45', 'Usaquén', 'regular', 'activo'],
        ['Roberto Silva', '44332211', '3004444444', 'Calle 89 #12-34', 'Suba', 'corporativo', 'activo']
    ];
    
    foreach ($clientes as $cliente) {
        $stmt->execute($cliente);
    }
    echo "<p style='color: green;'>✓ Clientes insertados</p>";
    
    // Insertar pedidos de prueba
    $stmt = $pdo->prepare("INSERT IGNORE INTO pedidos (id_cliente, id_zona, id_domiciliario, estado, cantidad_paquetes, total, fecha_pedido) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $pedidos = [
        [1, 1, 1, 'pendiente', 2, 10000.00, date('Y-m-d H:i:s', strtotime('-2 hours'))],
        [2, 2, 2, 'en_camino', 1, 12000.00, date('Y-m-d H:i:s', strtotime('-1 hour'))],
        [3, 3, 3, 'entregado', 3, 21000.00, date('Y-m-d H:i:s', strtotime('-30 minutes'))],
        [4, 4, 4, 'pendiente', 1, 16000.00, date('Y-m-d H:i:s', strtotime('-15 minutes'))]
    ];
    
    foreach ($pedidos as $pedido) {
        $stmt->execute($pedido);
    }
    echo "<p style='color: green;'>✓ Pedidos insertados</p>";
    
    echo "<h3>Datos insertados exitosamente</h3>";
    echo "<p><a href='vistas/pedidos.php' target='_blank'>Ir a la interfaz de pedidos</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 