-- Crear tabla para el histórico de pedidos
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

-- Agregar columna para rastrear si un pedido ya fue movido al histórico
ALTER TABLE pedidos ADD COLUMN movido_historico BOOLEAN DEFAULT FALSE;