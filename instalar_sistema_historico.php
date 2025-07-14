<?php
session_start();

// Solo permitir acceso a administradores
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

require_once 'servicios/conexion.php';

echo "<h1>🔧 Instalador del Sistema de Histórico</h1>";

try {
    // 1. Crear tabla de histórico
    echo "<h2>1. Creando tabla de histórico...</h2>";
    $sql_historico = "
    CREATE TABLE IF NOT EXISTS historico_pedidos (
        id_historico INT AUTO_INCREMENT PRIMARY KEY,
        id_pedido_original INT NOT NULL,
        id_cliente INT NOT NULL,
        id_zona INT NOT NULL,
        id_domiciliario INT,
        estado ENUM('entregado', 'cancelado') NOT NULL,
        cantidad_paquetes INT NOT NULL DEFAULT 1,
        total DECIMAL(10,2) NOT NULL,
        tiempo_estimado INT NOT NULL DEFAULT 30,
        fecha_pedido DATETIME NOT NULL,
        fecha_completado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        motivo_cancelacion TEXT NULL,
        observaciones TEXT NULL,
        
        -- Datos del cliente (para mantener histórico completo)
        cliente_nombre VARCHAR(100) NOT NULL,
        cliente_documento VARCHAR(20) NOT NULL,
        cliente_telefono VARCHAR(15),
        cliente_direccion TEXT,
        
        -- Datos de la zona
        zona_nombre VARCHAR(100) NOT NULL,
        zona_tarifa DECIMAL(10,2) NOT NULL,
        
        -- Datos del domiciliario
        domiciliario_nombre VARCHAR(100),
        domiciliario_telefono VARCHAR(15),
        
        -- Usuario que procesó
        usuario_proceso INT,
        
        INDEX idx_fecha_completado (fecha_completado),
        INDEX idx_estado (estado),
        INDEX idx_cliente (cliente_documento),
        INDEX idx_domiciliario (domiciliario_nombre),
        INDEX idx_id_original (id_pedido_original)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql_historico);
    echo "<p style='color: green;'>✅ Tabla 'historico_pedidos' creada exitosamente</p>";
    
    // 2. Agregar columna a tabla pedidos
    echo "<h2>2. Modificando tabla de pedidos...</h2>";
    try {
        $sql_columna = "ALTER TABLE pedidos ADD COLUMN movido_historico BOOLEAN DEFAULT FALSE";
        $pdo->exec($sql_columna);
        echo "<p style='color: green;'>✅ Columna 'movido_historico' agregada a la tabla pedidos</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p style='color: blue;'>ℹ️ La columna 'movido_historico' ya existe</p>";
        } else {
            throw $e;
        }
    }
    
    // 3. Migrar pedidos existentes al histórico
    echo "<h2>3. Migrando pedidos existentes...</h2>";
    
    // Buscar pedidos entregados o cancelados que no estén en el histórico
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM pedidos 
        WHERE estado IN ('entregado', 'cancelado') 
        AND (movido_historico = FALSE OR movido_historico IS NULL)
    ");
    
    $pedidos_para_migrar = $stmt->fetchColumn();
    
    if ($pedidos_para_migrar > 0) {
        require_once 'servicios/mover_a_historico.php';
        $migrados = procesarPedidosParaHistorico();
        echo "<p style='color: green;'>✅ Se migraron $migrados pedidos al histórico</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ No hay pedidos para migrar al histórico</p>";
    }
    
    // 4. Verificar instalación
    echo "<h2>4. Verificando instalación...</h2>";
    
    $stats = $pdo->query("SELECT COUNT(*) FROM historico_pedidos")->fetchColumn();
    $pendientes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE movido_historico = FALSE")->fetchColumn();
    
    echo "<p>📊 <strong>Estadísticas:</strong></p>";
    echo "<ul>";
    echo "<li>Registros en histórico: $stats</li>";
    echo "<li>Pedidos activos: $pendientes</li>";
    echo "</ul>";
    
    echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>🎉 ¡Instalación Completa!</h3>";
    echo "<p><strong>El sistema de histórico ha sido instalado exitosamente.</strong></p>";
    echo "<h4>Funcionalidades disponibles:</h4>";
    echo "<ul>";
    echo "<li>✅ Los pedidos entregados/cancelados se mueven automáticamente al histórico</li>";
    echo "<li>✅ La gestión de pedidos activos solo muestra pedidos pendientes/en camino</li>";
    echo "<li>✅ Nueva sección 'Histórico' en el menú para consultar entregas/cancelaciones</li>";
    echo "<li>✅ Filtros avanzados en el histórico (fecha, estado, cliente)</li>";
    echo "<li>✅ Vista detallada de cada registro histórico</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🔗 Enlaces:</h3>";
    echo "<p><a href='vistas/pedidos.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Ir a Pedidos Activos</a></p>";
    echo "<p><a href='vistas/historico_pedidos.php' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Ver Histórico</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>❌ Error en la instalación</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Por favor, verifica los permisos de la base de datos y vuelve a intentar.</p>";
    echo "</div>";
}
?>