-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 04-08-2025 a las 14:42:06
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
-- Estructura de tabla para la tabla `actividad_reciente`
--

CREATE TABLE `actividad_reciente` (
  `id_actividad` int(11) NOT NULL,
  `tipo_actividad` enum('pedido_entregado','pedido_asignado','cliente_registrado','zona_agregada','pedido_archivado') NOT NULL DEFAULT 'pedido_entregado',
  `descripcion` text NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  `fecha_actividad` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `documento` varchar(20) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `barrio` varchar(100) NOT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `documento`, `telefono`, `direccion`, `barrio`, `fecha_creacion`, `estado`) VALUES
(1, 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'La Esperanza', '2025-07-09 14:27:48', 'activo'),
(7, 'María Gómez', '10000002', '3002222222', 'Carrera 8 #12-34', 'Cuba', '2025-07-15 15:49:45', 'activo'),
(8, 'Carlos Ruiz', '10000003', '3003333333', 'Avenida 15 #45-67', 'Las palmas', '2025-07-15 15:49:45', 'activo'),
(9, 'Ana Torres', '10000004', '3004444444', 'Calle 20 #8-90', 'La esperanza', '2025-07-15 15:49:45', 'activo'),
(10, 'Luisa Martínez', '10000005', '3005555555', 'Carrera 30 #10-50', 'la ciudadela', '2025-07-15 15:49:45', 'activo'),
(14, 'CARLITOS', '5555555555', '88888888888', 'Calle 4 carrera 9', 'San vicente', '2025-07-23 08:47:54', 'activo'),
(15, 'JOSE', '444444', '88888888888', 'Cas omwa 23 Q', 'LA ciudadela', '2025-07-31 11:40:34', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `domiciliarios`
--

CREATE TABLE `domiciliarios` (
  `id_domiciliario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `vehiculo` varchar(50) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `id_zona` int(11) DEFAULT NULL,
  `estado` enum('disponible','ocupado','inactivo') NOT NULL DEFAULT 'disponible',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `domiciliarios`
--

INSERT INTO `domiciliarios` (`id_domiciliario`, `nombre`, `telefono`, `vehiculo`, `placa`, `id_zona`, `estado`, `fecha_creacion`) VALUES
(1, 'Juan Pérez', '3001234567', 'Moto', 'ABC123', 3, 'disponible', '2025-07-12 08:44:28'),
(2, 'Carlos López', '3002345678', 'Moto', 'DEF456', 3, 'disponible', '2025-07-12 08:44:28'),
(3, 'Ana García', '3003456789', 'Bicicleta', 'GHI789', NULL, 'disponible', '2025-07-12 08:44:28'),
(4, 'Luis Rodríguez', '3004567890', 'Moto', 'JKL012', 3, 'disponible', '2025-07-12 08:44:28'),
(6, 'Diego Ramírez', '3005678901', 'Moto', 'MNO345', 1, 'disponible', '2025-07-15 15:50:00'),
(7, 'Valeria Castro', '3006789012', 'Bicicleta', 'PQR678', 2, 'disponible', '2025-07-15 16:00:00'),
(8, 'Felipe Gómez', '3007890123', 'Moto', 'STU901', 3, 'disponible', '2025-07-15 16:10:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historico_pedidos`
--

CREATE TABLE `historico_pedidos` (
  `id_historico` int(11) NOT NULL,
  `id_pedido_original` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_zona` int(11) NOT NULL,
  `id_domiciliario` int(11) DEFAULT NULL,
  `id_vehiculo` int(11) DEFAULT NULL,
  `estado` enum('entregado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad_paquetes` int(11) NOT NULL DEFAULT '1',
  `total` decimal(10,2) NOT NULL,
  `tiempo_estimado` int(11) NOT NULL DEFAULT '30',
  `fecha_pedido` datetime NOT NULL,
  `hora_salida` datetime DEFAULT NULL,
  `hora_llegada` datetime DEFAULT NULL,
  `fecha_completado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cliente_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cliente_direccion` text COLLATE utf8mb4_unicode_ci,
  `zona_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zona_tarifa` decimal(10,2) NOT NULL,
  `domiciliario_nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domiciliario_telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehiculo_tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehiculo_placa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_proceso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `historico_pedidos`
--

INSERT INTO `historico_pedidos` (`id_historico`, `id_pedido_original`, `id_cliente`, `id_zona`, `id_domiciliario`, `id_vehiculo`, `estado`, `cantidad_paquetes`, `total`, `tiempo_estimado`, `fecha_pedido`, `hora_salida`, `hora_llegada`, `fecha_completado`, `cliente_nombre`, `cliente_documento`, `cliente_telefono`, `cliente_direccion`, `zona_nombre`, `zona_tarifa`, `domiciliario_nombre`, `domiciliario_telefono`, `vehiculo_tipo`, `vehiculo_placa`, `usuario_proceso`) VALUES
(1, 24, 1, 4, 1, 1, 'entregado', 5, '8000.00', 30, '2025-08-01 15:18:58', '2025-08-01 15:19:08', '2025-08-01 15:19:11', '2025-08-01 15:19:11', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'Norte', '8000.00', 'Juan Pérez', '3001234567', 'Carro', 'ADS552', 1),
(2, 25, 1, 4, 1, 1, 'entregado', 4, '8000.00', 30, '2025-08-01 15:22:35', '2025-08-01 15:22:45', '2025-08-01 15:22:48', '2025-08-01 15:22:48', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'Norte', '8000.00', 'Juan Pérez', '3001234567', 'Carro', 'ADS552', 1),
(3, 26, 1, 4, 1, 1, 'entregado', 5, '8000.00', 30, '2025-08-01 15:27:36', '2025-08-01 15:49:00', '2025-08-01 15:49:04', '2025-08-01 15:49:04', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'Norte', '8000.00', 'Juan Pérez', '3001234567', 'Carro', 'ADS552', 1),
(4, 27, 14, 1, 4, 1, 'entregado', 4, '5000.00', 30, '2025-08-01 15:50:21', '2025-08-01 15:50:30', '2025-08-01 15:50:33', '2025-08-01 15:50:34', 'CARLITOS', '5555555555', '88888888888', 'Calle 4 carrera 9', 'Occidente', '5000.00', 'Luis Rodríguez', '3004567890', 'Carro', 'ADS552', 1),
(5, 28, 1, 4, 1, 1, 'entregado', 8, '8000.00', 30, '2025-08-01 16:03:57', '2025-08-01 16:04:07', '2025-08-01 16:04:11', '2025-08-01 16:04:11', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'Norte', '8000.00', 'Juan Pérez', '3001234567', 'Carro', 'ADS552', 1),
(6, 1, 14, 1, 1, 1, 'entregado', 8, '5000.00', 30, '2025-08-02 08:18:56', '2025-08-02 08:19:03', '2025-08-02 08:19:06', '2025-08-02 08:19:06', 'CARLITOS', '5555555555', '88888888888', 'Calle 4 carrera 9', 'Occidente', '5000.00', 'Juan Pérez', '3001234567', 'Carro', 'ADS552', 1),
(7, 2, 1, 4, 1, 1, 'entregado', 3, '8000.00', 30, '2025-08-02 08:20:33', '2025-08-02 08:41:12', '2025-08-02 08:56:23', '2025-08-02 08:56:23', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'Norte', '8000.00', 'Juan Pérez', '3001234567', 'Carro', 'ADS552', 1),
(8, 3, 1, 4, 1, 1, 'entregado', 5, '8000.00', 30, '2025-08-02 08:44:55', '2025-08-02 08:56:29', '2025-08-02 11:04:20', '2025-08-02 11:04:20', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'Norte', '8000.00', 'Juan Pérez', '3001234567', 'Carro', 'ADS552', 1),
(9, 4, 1, 4, 7, 2, 'entregado', 3, '10000.00', 30, '2025-08-02 11:04:43', '2025-08-02 11:04:58', '2025-08-02 11:06:17', '2025-08-02 11:06:17', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'Norte', '8000.00', 'Valeria Castro', '3006789012', 'moto', 'GHI789', 1),
(10, 5, 14, 1, 3, 1, 'entregado', 6, '43333.00', 30, '2025-08-02 11:05:24', '2025-08-02 11:05:34', '2025-08-02 11:06:22', '2025-08-02 11:06:22', 'CARLITOS', '5555555555', '88888888888', 'Calle 4 carrera 9', 'Occidente', '5000.00', 'Ana García', '3003456789', 'Carro', 'ADS552', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_domiciliario` int(11) DEFAULT NULL,
  `id_vehiculo` int(11) DEFAULT NULL,
  `id_zona` int(11) NOT NULL,
  `estado` enum('pendiente','en_camino','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `hora_salida` datetime DEFAULT NULL,
  `hora_llegada` datetime DEFAULT NULL,
  `fecha_pedido` datetime DEFAULT CURRENT_TIMESTAMP,
  `cantidad_paquetes` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `tiempo_estimado` int(11) DEFAULT NULL,
  `envio_inmediato` tinyint(1) DEFAULT '0',
  `alistamiento` tinyint(1) DEFAULT '0',
  `movido_historico` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_cliente`, `id_domiciliario`, `id_vehiculo`, `id_zona`, `estado`, `hora_salida`, `hora_llegada`, `fecha_pedido`, `cantidad_paquetes`, `total`, `tiempo_estimado`, `envio_inmediato`, `alistamiento`, `movido_historico`) VALUES
(1, 1, NULL, NULL, 4, 'pendiente', NULL, NULL, '2025-08-04 08:28:54', 5, '8000.00', 30, 1, 0, 0),
(2, 14, NULL, NULL, 1, 'pendiente', NULL, NULL, '2025-08-04 08:29:52', 4, '5000.00', 30, 0, 1, 0),
(3, 9, NULL, NULL, 4, 'pendiente', NULL, NULL, '2025-08-04 08:30:14', 6, '8000.00', 30, 0, 1, 0),
(4, 8, NULL, NULL, 2, 'pendiente', NULL, NULL, '2025-08-04 08:30:42', 6, '6000.00', 30, 0, 1, 0),
(5, 7, NULL, NULL, 6, 'pendiente', NULL, NULL, '2025-08-04 08:31:07', 8, '50000.00', 30, 1, 0, 0),
(6, 10, NULL, NULL, 3, 'pendiente', NULL, NULL, '2025-08-04 08:31:39', 2, '7000.00', 30, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_mensuales`
--

CREATE TABLE `pedidos_mensuales` (
  `id_mensual` int(11) NOT NULL,
  `id_pedido_original` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_zona` int(11) NOT NULL,
  `id_domiciliario` int(11) DEFAULT NULL,
  `estado` varchar(20) NOT NULL,
  `cantidad_paquetes` int(11) NOT NULL DEFAULT '1',
  `total` decimal(10,2) NOT NULL,
  `tiempo_estimado` int(11) NOT NULL DEFAULT '30',
  `fecha_pedido` datetime NOT NULL,
  `fecha_completado` datetime NOT NULL,
  `cliente_nombre` varchar(100) NOT NULL,
  `cliente_documento` varchar(20) NOT NULL,
  `cliente_telefono` varchar(15) DEFAULT NULL,
  `cliente_direccion` text,
  `zona_nombre` varchar(100) NOT NULL,
  `zona_tarifa` decimal(10,2) NOT NULL,
  `domiciliario_nombre` varchar(100) DEFAULT NULL,
  `domiciliario_telefono` varchar(15) DEFAULT NULL,
  `usuario_proceso` int(11) DEFAULT NULL,
  `mes` int(11) NOT NULL,
  `anio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('admin','cajera','org_domicilios') NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `numero_documento`, `contrasena`, `rol`, `estado`, `fecha_creacion`) VALUES
(1, 'Santiago', 'Garcia', '1107841204', '$2y$10$W.D95lV8ZLQf4JxkqHIfpewW1eBihsmwOh848a4iACw//B2QyG9.K', 'admin', 'activo', '2025-07-09 14:25:23'),
(2, 'Cajera', 'Cardenas', '456789123', '$2y$10$8z2nVS9NtQqGrojKPLf.nOMKECdLQ9VxS7g7F3ycr/HcS.ZvazCjW', 'cajera', 'activo', '2025-07-12 09:40:49'),
(3, 'Gestor', 'Garcia', '987654321', '$2y$10$QLy35OSjCr2o67G6Qy8uPOCCOGMkA5DFu1UklVQv15b45A1tTaAIq', 'org_domicilios', 'activo', '2025-07-12 09:41:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id_vehiculo` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `estado` enum('disponible','en_ruta','mantenimiento','inactivo') NOT NULL DEFAULT 'disponible',
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id_vehiculo`, `tipo`, `placa`, `estado`, `descripcion`) VALUES
(1, 'Carro', 'ADS552', 'disponible', 'CARRO'),
(2, 'moto', 'GHI789', 'disponible', 'Moto buena');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zonas`
--

CREATE TABLE `zonas` (
  `id_zona` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `barrio` varchar(255) DEFAULT NULL,
  `tarifa_base` decimal(10,2) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `tiempo_estimado` int(11) NOT NULL DEFAULT '15' COMMENT 'Tiempo estimado de entrega en minutos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zonas`
--

INSERT INTO `zonas` (`id_zona`, `nombre`, `barrio`, `tarifa_base`, `estado`, `fecha_creacion`, `tiempo_estimado`) VALUES
(1, 'Occidente', 'San vicente', '5000.00', 'activo', '2025-07-12 08:44:28', 15),
(2, 'Centro', 'Las palmas', '6000.00', 'activo', '2025-07-12 08:44:28', 15),
(3, 'Sur', 'La ciudadela', '7000.00', 'activo', '2025-07-12 08:44:28', 20),
(4, 'Norte', 'La esperanza', '8000.00', 'activo', '2025-07-12 08:44:28', 15),
(6, 'Oriente', 'CUBA', '50000.00', 'activo', '2025-07-15 14:42:54', 15);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad_reciente`
--
ALTER TABLE `actividad_reciente`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_pedido` (`id_pedido`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `documento` (`documento`);

--
-- Indices de la tabla `domiciliarios`
--
ALTER TABLE `domiciliarios`
  ADD PRIMARY KEY (`id_domiciliario`),
  ADD UNIQUE KEY `placa` (`placa`),
  ADD KEY `id_zona` (`id_zona`);

--
-- Indices de la tabla `historico_pedidos`
--
ALTER TABLE `historico_pedidos`
  ADD PRIMARY KEY (`id_historico`),
  ADD KEY `idx_fecha_completado` (`fecha_completado`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_cliente` (`cliente_documento`),
  ADD KEY `idx_domiciliario` (`domiciliario_nombre`),
  ADD KEY `idx_id_original` (`id_pedido_original`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_domiciliario` (`id_domiciliario`),
  ADD KEY `id_zona` (`id_zona`),
  ADD KEY `idx_hora_salida` (`hora_salida`),
  ADD KEY `idx_hora_llegada` (`hora_llegada`);

--
-- Indices de la tabla `pedidos_mensuales`
--
ALTER TABLE `pedidos_mensuales`
  ADD PRIMARY KEY (`id_mensual`),
  ADD KEY `mes` (`mes`,`anio`),
  ADD KEY `fecha_pedido` (`fecha_pedido`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id_vehiculo`),
  ADD UNIQUE KEY `placa` (`placa`);

--
-- Indices de la tabla `zonas`
--
ALTER TABLE `zonas`
  ADD PRIMARY KEY (`id_zona`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad_reciente`
--
ALTER TABLE `actividad_reciente`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `domiciliarios`
--
ALTER TABLE `domiciliarios`
  MODIFY `id_domiciliario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `historico_pedidos`
--
ALTER TABLE `historico_pedidos`
  MODIFY `id_historico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pedidos_mensuales`
--
ALTER TABLE `pedidos_mensuales`
  MODIFY `id_mensual` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `zonas`
--
ALTER TABLE `zonas`
  MODIFY `id_zona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividad_reciente`
--
ALTER TABLE `actividad_reciente`
  ADD CONSTRAINT `actividad_reciente_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `actividad_reciente_ibfk_2` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`);

--
-- Filtros para la tabla `domiciliarios`
--
ALTER TABLE `domiciliarios`
  ADD CONSTRAINT `domiciliarios_ibfk_1` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id_zona`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_domiciliario`) REFERENCES `domiciliarios` (`id_domiciliario`),
  ADD CONSTRAINT `pedidos_ibfk_3` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id_zona`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
