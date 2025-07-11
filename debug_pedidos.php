<?php
session_start();
$_SESSION['usuario_id'] = 1;

require_once 'servicios/conexion.php';

echo "<h2>Depuración de Pedidos</h2>";

try {
    // Verificar todos los pedidos
    $stmt = $pdo->query("
        SELECT p.id_pedido, c.nombre AS cliente, d.nombre AS domiciliario, p.estado, p.fecha_pedido, p.tiempo_estimado
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
        ORDER BY p.fecha_pedido DESC
    ");
    
    $pedidos = $stmt->fetchAll();
    
    echo "<h3>Pedidos en la base de datos:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Cliente</th><th>Domiciliario</th><th>Estado</th><th>Fecha</th><th>Tiempo</th></tr>";
    
    foreach ($pedidos as $pedido) {
        $color = '';
        switch ($pedido['estado']) {
            case 'pendiente':
                $color = 'background-color: #dbb647;';
                break;
            case 'entregado':
                $color = 'background-color: #38bd57;';
                break;
            case 'cancelado':
                $color = 'background-color: #721c24;';
                break;
            default:
                $color = 'background-color: #f0f0f0;';
        }
        
        echo "<tr style='{$color}'>";
        echo "<td>#{$pedido['id_pedido']}</td>";
        echo "<td>{$pedido['cliente']}</td>";
        echo "<td>" . ($pedido['domiciliario'] ? $pedido['domiciliario'] : 'No asignado') . "</td>";
        echo "<td>{$pedido['estado']}</td>";
        echo "<td>{$pedido['fecha_pedido']}</td>";
        echo "<td>{$pedido['tiempo_estimado']} min</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Contar por estado
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado");
    $estados = $stmt->fetchAll();
    
    echo "<h3>Resumen por estado:</h3>";
    echo "<ul>";
    foreach ($estados as $estado) {
        echo "<li>{$estado['estado']}: {$estado['total']} pedidos</li>";
    }
    echo "</ul>";
    
    // Verificar si hay pedidos pendientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'");
    $pendientes = $stmt->fetch();
    
    echo "<h3>Pedidos pendientes: {$pendientes['total']}</h3>";
    
    if ($pendientes['total'] == 0) {
        echo "<p style='color: orange;'>⚠️ No hay pedidos pendientes. Los botones de 'Entregar' solo aparecen para pedidos con estado 'pendiente'.</p>";
        echo "<p><a href='insertar_datos_prueba.php'>Insertar datos de prueba</a></p>";
    } else {
        echo "<p style='color: green;'>✅ Hay {$pendientes['total']} pedidos pendientes. Los botones deberían aparecer.</p>";
    }
    
    echo "<h3>Enlaces de prueba:</h3>";
    echo "<p><a href='vistas/pedidos.php' target='_blank'>Ver interfaz de pedidos</a></p>";
    echo "<p><a href='insertar_datos_prueba.php'>Insertar más datos de prueba</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 