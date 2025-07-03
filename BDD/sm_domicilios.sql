-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 03-07-2025 a las 13:18:37
-- Versión del servidor: 5.7.24
-- Versión de PHP: 8.2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sm_domicilios`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `barrios`
--

CREATE TABLE `barrios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `zona` varchar(50) NOT NULL DEFAULT 'Sur',
  `tarifa_domicilio` decimal(10,2) DEFAULT '3000.00',
  `horarios_disponibles` json DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `barrios`
--

INSERT INTO `barrios` (`id`, `nombre`, `zona`, `tarifa_domicilio`, `horarios_disponibles`, `estado`, `created_at`) VALUES
(1, 'La Ciudadela', 'Sur', '3000.00', '[\"08:00\", \"09:00\", \"10:00\", \"11:00\", \"14:00\", \"15:00\", \"16:00\"]', 'activo', '2025-07-02 21:55:02'),
(2, 'Las Palmas', 'Sur', '3000.00', '[\"08:00\", \"09:00\", \"10:00\", \"11:00\", \"14:00\", \"15:00\", \"16:00\"]', 'activo', '2025-07-02 21:55:02'),
(3, 'Canada', 'Norte', '3500.00', '[\"08:00\", \"09:00\", \"10:00\", \"11:00\", \"14:00\", \"15:00\", \"16:00\"]', 'activo', '2025-07-02 21:55:02'),
(4, 'Centro', 'Centro', '2500.00', '[\"08:00\", \"09:00\", \"10:00\", \"11:00\", \"14:00\", \"15:00\", \"16:00\", \"17:00\", \"18:00\"]', 'activo', '2025-07-02 21:55:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `direccion_principal` varchar(200) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `referencias_direccion` text,
  `preferencias_entrega` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` text,
  `tipo` enum('string','number','boolean','json') DEFAULT 'string',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `clave`, `valor`, `descripcion`, `tipo`, `updated_at`) VALUES
(1, 'tarifa_base_domicilio', '3000', 'Tarifa base para domicilios', 'number', '2025-07-02 21:55:02'),
(2, 'horario_inicio', '08:00', 'Hora de inicio de servicio', 'string', '2025-07-02 21:55:02'),
(3, 'horario_fin', '18:00', 'Hora de fin de servicio', 'string', '2025-07-02 21:55:02'),
(4, 'tiempo_preparacion_minutos', '30', 'Tiempo estimado de preparación en minutos', 'number', '2025-07-02 21:55:02'),
(5, 'maximo_pedidos_simultaneos', '50', 'Máximo número de pedidos simultáneos', 'number', '2025-07-02 21:55:02'),
(6, 'empresa_nombre', 'SuperMercar Domicilios', 'Nombre de la empresa', 'string', '2025-07-02 21:55:02'),
(7, 'empresa_telefono', '3001234567', 'Teléfono principal de la empresa', 'string', '2025-07-02 21:55:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones_cliente`
--

CREATE TABLE `direcciones_cliente` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre_direccion` varchar(50) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `referencias` text,
  `es_principal` enum('si','no') DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `numero_pedido` varchar(20) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `direccion_entrega` varchar(200) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `referencias_direccion` text,
  `telefono_contacto` varchar(15) NOT NULL,
  `cantidad_paquetes` int(3) UNSIGNED DEFAULT '1',
  `valor_productos` decimal(10,2) DEFAULT '0.00',
  `valor_domicilio` decimal(10,2) DEFAULT '3000.00',
  `valor_total` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','transferencia','tarjeta','pse') DEFAULT 'efectivo',
  `estado_pago` enum('pendiente','pagado','rechazado') DEFAULT 'pendiente',
  `observaciones` text,
  `hora_programada` time DEFAULT NULL,
  `fecha_programada` date DEFAULT NULL,
  `estado_pedido` enum('recibido','confirmado','alistando','listo','despachado','entregado','cancelado') DEFAULT 'recibido',
  `repartidor_id` int(11) DEFAULT NULL,
  `vehiculo_id` int(11) DEFAULT NULL,
  `hora_despacho` time DEFAULT NULL,
  `hora_entrega` time DEFAULT NULL,
  `calificacion_cliente` tinyint(1) DEFAULT NULL,
  `comentario_cliente` text,
  `foto_entrega` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repartidores`
--

CREATE TABLE `repartidores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `licencia_conduccion` varchar(20) DEFAULT NULL,
  `fecha_vencimiento_licencia` date DEFAULT NULL,
  `zona_cobertura` json DEFAULT NULL,
  `vehiculo_propio` enum('si','no') DEFAULT 'no',
  `calificacion_promedio` decimal(3,2) DEFAULT '5.00',
  `total_domicilios` int(11) DEFAULT '0',
  `estado_servicio` enum('disponible','ocupado','descanso','offline') DEFAULT 'offline',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento_pedidos`
--

CREATE TABLE `seguimiento_pedidos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `estado_anterior` varchar(20) DEFAULT NULL,
  `estado_nuevo` varchar(20) NOT NULL,
  `observaciones` text,
  `usuario_cambio` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `numero_documento` varchar(12) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('admin','repartidor') DEFAULT 'cliente',
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `remember_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiracion` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `numero_documento`, `nombre`, `email`, `telefono`, `contrasena`, `rol`, `estado`, `remember_token`, `reset_token`, `reset_token_expiracion`, `created_at`, `updated_at`) VALUES
(1, '1107841204', 'Administrador Sistema', 'admin@supermercar.com', '3001234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'activo', NULL, NULL, NULL, '2025-07-02 21:55:02', '2025-07-02 21:56:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL,
  `placa` varchar(10) NOT NULL,
  `tipo` enum('moto','carro','bicicleta','pie') NOT NULL,
  `marca` varchar(30) DEFAULT NULL,
  `estado` enum('disponible','en_uso','mantenimiento','fuera_servicio') DEFAULT 'disponible',
  `soat_vencimiento` date DEFAULT NULL,
  `revision_tecnica` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `barrios`
--
ALTER TABLE `barrios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `idx_zona` (`zona`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_clientes_barrio` (`barrio_id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_direcciones_cliente` (`cliente_id`),
  ADD KEY `fk_direcciones_barrio` (`barrio_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_pedido` (`numero_pedido`),
  ADD KEY `fk_pedidos_cliente` (`cliente_id`),
  ADD KEY `fk_pedidos_barrio` (`barrio_id`),
  ADD KEY `fk_pedidos_repartidor` (`repartidor_id`),
  ADD KEY `fk_pedidos_vehiculo` (`vehiculo_id`),
  ADD KEY `idx_estado_pedido` (`estado_pedido`),
  ADD KEY `idx_fecha_programada` (`fecha_programada`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indices de la tabla `repartidores`
--
ALTER TABLE `repartidores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_estado_servicio` (`estado_servicio`);

--
-- Indices de la tabla `seguimiento_pedidos`
--
ALTER TABLE `seguimiento_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_seguimiento_pedido` (`pedido_id`),
  ADD KEY `fk_seguimiento_usuario` (`usuario_cambio`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_rol` (`rol`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `placa` (`placa`),
  ADD KEY `fk_vehiculo_propietario` (`propietario_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `barrios`
--
ALTER TABLE `barrios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `repartidores`
--
ALTER TABLE `repartidores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `seguimiento_pedidos`
--
ALTER TABLE `seguimiento_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_clientes_barrio` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clientes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  ADD CONSTRAINT `fk_direcciones_barrio` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_direcciones_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_barrio` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedidos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedidos_repartidor` FOREIGN KEY (`repartidor_id`) REFERENCES `repartidores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedidos_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `repartidores`
--
ALTER TABLE `repartidores`
  ADD CONSTRAINT `fk_repartidores_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `seguimiento_pedidos`
--
ALTER TABLE `seguimiento_pedidos`
  ADD CONSTRAINT `fk_seguimiento_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_seguimiento_usuario` FOREIGN KEY (`usuario_cambio`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `fk_vehiculo_propietario` FOREIGN KEY (`propietario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
