<?php
session_start();

// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug - Eliminar Pedido</h1>";

// Verificar sesión
echo "<h2>1. Verificación de Sesión:</h2>";
if (!isset($_SESSION['usuario_id'])) {
    echo "<p style='color: red;'>❌ Usuario no autenticado. SESSION: " . print_r($_SESSION, true) . "</p>";
} else {
    echo "<p style='color: green;'>✅ Usuario autenticado. ID: " . $_SESSION['usuario_id'] . "</p>";
}

// Verificar conexión a la base de datos
echo "<h2>2. Verificación de Conexión a BD:</h2>";
try {
    require_once 'conexion.php'; // Ruta corregida desde el directorio servicios
    echo "<p style='color: green;'>✅ Conexión a base de datos exitosa</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar si hay pedidos en la base de datos
echo "<h2>3. Pedidos en la Base de Datos:</h2>";
try {
    $stmt = $pdo->prepare("SELECT id_pedido, estado, fecha_pedido FROM pedidos ORDER BY fecha_pedido DESC LIMIT 5");
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($pedidos)) {
        echo "<p style='color: orange;'>⚠️ No hay pedidos en la base de datos</p>";
    } else {
        echo "<p style='color: green;'>✅ Pedidos encontrados:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Estado</th><th>Fecha</th><th>¿Se puede eliminar?</th></tr>";
        foreach ($pedidos as $pedido) {
            $puedeEliminar = $pedido['estado'] !== 'entregado' ? 'SÍ' : 'NO';
            $color = $pedido['estado'] !== 'entregado' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>" . $pedido['id_pedido'] . "</td>";
            echo "<td>" . $pedido['estado'] . "</td>";
            echo "<td>" . $pedido['fecha_pedido'] . "</td>";
            echo "<td style='color: $color;'>$puedeEliminar</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al consultar pedidos: " . $e->getMessage() . "</p>";
}

// Simulación de eliminación (si se proporciona un ID)
if (isset($_GET['test_id'])) {
    $test_id = intval($_GET['test_id']);
    echo "<h2>4. Prueba de Eliminación (ID: $test_id):</h2>";
    
    try {
        // Verificar que el pedido existe
        $stmt = $pdo->prepare("SELECT id_domiciliario, estado FROM pedidos WHERE id_pedido = ?");
        $stmt->execute([$test_id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            echo "<p style='color: red;'>❌ Pedido no encontrado</p>";
        } else {
            echo "<p style='color: blue;'>📋 Pedido encontrado - Estado: " . $pedido['estado'] . "</p>";
            
            if ($pedido['estado'] === 'entregado') {
                echo "<p style='color: red;'>❌ No se puede eliminar un pedido entregado</p>";
            } else {
                echo "<p style='color: green;'>✅ El pedido se puede eliminar</p>";
                
                // Solo mostrar el SQL que se ejecutaría, no ejecutarlo realmente
                echo "<p style='color: blue;'>🔧 SQL que se ejecutaría: DELETE FROM pedidos WHERE id_pedido = $test_id</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en la prueba: " . $e->getMessage() . "</p>";
    }
}

// Instrucciones
echo "<h2>5. Instrucciones:</h2>";
echo "<p>Para probar la eliminación de un pedido específico, agrega ?test_id=ID_DEL_PEDIDO a la URL</p>";
echo "<p>Ejemplo: debug_eliminar_pedido.php?test_id=1</p>";

echo "<h2>6. Verificar JavaScript:</h2>";
echo "<p>Abre las herramientas de desarrollador (F12) en tu navegador y ve a la pestaña 'Console' cuando intentes eliminar un pedido.</p>";
echo "<p>Cualquier error de JavaScript aparecerá allí.</p>";

echo "<h2>7. Información del Sistema:</h2>";
echo "<p><strong>Archivo ubicado en:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Directorio actual:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Archivo de conexión:</strong> " . __DIR__ . "/conexion.php</p>";
?>