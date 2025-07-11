<?php
session_start();
// Simular sesión para pruebas
$_SESSION['usuario_id'] = 1;

echo "<h2>Prueba de Funcionalidad de Pedidos</h2>";

// Probar conexión
try {
    require_once 'servicios/conexion.php';
    echo "<p style='color: green;'>✓ Conexión exitosa</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar datos en tablas
echo "<h3>Verificando datos en tablas:</h3>";

// Clientes
$stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes WHERE estado = 'activo'");
$clientes = $stmt->fetch();
echo "<p>Clientes activos: " . $clientes['total'] . "</p>";

// Zonas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM zonas WHERE estado = 'activo'");
$zonas = $stmt->fetch();
echo "<p>Zonas activas: " . $zonas['total'] . "</p>";

// Domiciliarios
$stmt = $pdo->query("SELECT COUNT(*) as total FROM domiciliarios WHERE estado = 'disponible'");
$domiciliarios = $stmt->fetch();
echo "<p>Domiciliarios disponibles: " . $domiciliarios['total'] . "</p>";

// Pedidos
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
$pedidos = $stmt->fetch();
echo "<p>Total de pedidos: " . $pedidos['total'] . "</p>";

// Probar servicios
echo "<h3>Probando servicios:</h3>";

// Probar obtener pedidos
try {
    $stmt = $pdo->query("SELECT p.id_pedido, c.nombre AS cliente, d.nombre AS domiciliario, p.estado, p.fecha_pedido
                        FROM pedidos p
                        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                        LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
                        ORDER BY p.fecha_pedido DESC
                        LIMIT 5");
    $recentOrders = $stmt->fetchAll();
    echo "<p style='color: green;'>✓ Consulta de pedidos exitosa (" . count($recentOrders) . " pedidos encontrados)</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en consulta de pedidos: " . $e->getMessage() . "</p>";
}

echo "<h3>Enlaces de prueba:</h3>";
echo "<p><a href='vistas/pedidos.php' target='_blank'>Abrir interfaz de pedidos</a></p>";
echo "<p><a href='servicios/obtener_pedido.php?id=1' target='_blank'>Probar obtener pedido ID 1</a></p>";
echo "<p><a href='servicios/buscar_pedido.php' target='_blank'>Probar búsqueda de pedidos</a></p>";
?> 