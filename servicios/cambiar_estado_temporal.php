<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once 'conexion.php';

echo "<h1>🔧 Cambiar Estado de Pedido (Temporal)</h1>";

// Si se recibe un ID para cambiar
if (isset($_POST['id_pedido']) && isset($_POST['nuevo_estado'])) {
    try {
        $id_pedido = intval($_POST['id_pedido']);
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $stmt->execute([$nuevo_estado, $id_pedido]);
        
        echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
        echo "✅ Pedido ID $id_pedido cambiado a estado '$nuevo_estado'";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
        echo "❌ Error: " . $e->getMessage();
        echo "</div>";
    }
}

// Mostrar pedidos actuales
try {
    $stmt = $pdo->query("SELECT id_pedido, estado, fecha_pedido FROM pedidos ORDER BY fecha_pedido DESC LIMIT 10");
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>📋 Pedidos Actuales:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Estado Actual</th><th>Fecha</th><th>Acción</th></tr>";
    
    foreach ($pedidos as $pedido) {
        $color = $pedido['estado'] === 'entregado' ? '#dc3545' : '#28a745';
        echo "<tr>";
        echo "<td>" . $pedido['id_pedido'] . "</td>";
        echo "<td style='color: $color; font-weight: bold;'>" . $pedido['estado'] . "</td>";
        echo "<td>" . $pedido['fecha_pedido'] . "</td>";
        echo "<td>";
        
        if ($pedido['estado'] === 'entregado') {
            echo "<form method='POST' style='display: inline;'>";
            echo "<input type='hidden' name='id_pedido' value='" . $pedido['id_pedido'] . "'>";
            echo "<input type='hidden' name='nuevo_estado' value='pendiente'>";
            echo "<button type='submit' style='background: #ffc107; color: #212529; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>";
            echo "Cambiar a Pendiente</button>";
            echo "</form>";
        } else {
            echo "<span style='color: #28a745;'>✅ Ya se puede eliminar</span>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error al consultar pedidos: " . $e->getMessage() . "</p>";
}

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
echo "<h3>ℹ️ Información Importante:</h3>";
echo "<ul>";
echo "<li><strong>Solo pedidos 'pendiente' o 'cancelado' se pueden eliminar</strong></li>";
echo "<li><strong>Los pedidos 'entregado' están protegidos por seguridad</strong></li>";
echo "<li><strong>Este cambio es temporal, solo para pruebas</strong></li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='debug_eliminar_pedido.php'>← Volver al Debug</a></p>";
echo "<p><a href='../vistas/pedidos.php'>← Ir a Gestión de Pedidos</a></p>";
?>