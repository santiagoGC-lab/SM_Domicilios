-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 15-08-2025 a las 19:30:11
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
(1, 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'llanitos', '2025-07-09 14:27:48', 'activo'),
(7, 'María Gómez', '10000002', '3002222222', 'Carrera 8 #12-34', 'GAMA', '2025-07-15 15:49:45', 'activo'),
(8, 'Carlos Ruiz', '10000003', '3003333333', 'Avenida 15 #45-67', 'Las palmas', '2025-07-15 15:49:45', 'activo'),
(9, 'Ana Torres', '10000004', '3004444444', 'Calle 20 #8-90', 'La esperanza', '2025-07-15 15:49:45', 'activo'),
(10, 'Luisa Martínez', '10000005', '3005555555', 'Carrera 30 #10-50', 'LA VIRGEN', '2025-07-15 15:49:45', 'activo'),
(14, 'CARLITOS', '5555555555', '88888888888', 'Calle 4 carrera 9', 'San vicente', '2025-07-23 08:47:54', 'activo'),
(15, 'JOSE', '444444', '88888888888', 'Cas omwa 23 Q', 'FUNDADORES EN TODA SU AREA', '2025-07-31 11:40:34', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `domiciliarios`
--

CREATE TABLE `domiciliarios` (
  `id_domiciliario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `id_zona` int(11) DEFAULT NULL,
  `estado` enum('disponible','ocupado','inactivo') NOT NULL DEFAULT 'disponible',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `domiciliarios`
--

INSERT INTO `domiciliarios` (`id_domiciliario`, `nombre`, `telefono`, `id_zona`, `estado`, `fecha_creacion`) VALUES
(1, 'Juan Pérez', '3001234567', NULL, 'ocupado', '2025-07-12 08:44:28'),
(2, 'Carlos López', '3002345678', NULL, 'disponible', '2025-07-12 08:44:28'),
(3, 'Ana García', '3003456789', NULL, 'ocupado', '2025-07-12 08:44:28'),
(4, 'Luis Rodríguez', '3004567890', NULL, 'disponible', '2025-07-12 08:44:28'),
(6, 'Diego Ramírez', '3005678901', NULL, 'disponible', '2025-07-15 15:50:00'),
(7, 'Valeria Castro', '3006789012', NULL, 'disponible', '2025-07-15 16:00:00'),
(8, 'Felipe Gómez', '3007890123', NULL, 'disponible', '2025-07-15 16:10:00');

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
  `hora_estimada_entrega` time DEFAULT NULL,
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

INSERT INTO `historico_pedidos` (`id_historico`, `id_pedido_original`, `id_cliente`, `id_zona`, `id_domiciliario`, `id_vehiculo`, `estado`, `cantidad_paquetes`, `total`, `tiempo_estimado`, `hora_estimada_entrega`, `fecha_pedido`, `hora_salida`, `hora_llegada`, `fecha_completado`, `cliente_nombre`, `cliente_documento`, `cliente_telefono`, `cliente_direccion`, `zona_nombre`, `zona_tarifa`, `domiciliario_nombre`, `domiciliario_telefono`, `vehiculo_tipo`, `vehiculo_placa`, `usuario_proceso`) VALUES
(31, 21, 1, 4, 1, 1, 'entregado', 4, '8000.00', 20, NULL, '2025-08-05 17:04:20', '2025-08-05 17:49:16', '2025-08-05 17:49:19', '2025-08-05 17:49:19', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'Norte', '8000.00', 'Juan Pérez', '3001234567', 'carguero', 'ADS552', 1),
(32, 1, 14, 8, 1, 1, 'entregado', 5, '5000.00', 30, NULL, '2025-08-06 08:28:34', '2025-08-06 14:37:11', '2025-08-06 14:37:14', '2025-08-06 14:37:14', 'CARLITOS', '5555555555', '88888888888', 'Calle 4 carrera 9', 'Occidente', '5000.00', 'Juan Pérez', '3001234567', 'carguero', 'ADS552', 1),
(33, 2, 14, 8, 1, 2, 'entregado', 4, '5000.00', 30, NULL, '2025-08-06 14:44:42', '2025-08-06 14:50:33', '2025-08-06 15:04:16', '2025-08-06 15:04:16', 'CARLITOS', '5555555555', '88888888888', 'Calle 4 carrera 9', 'Occidente', '5000.00', 'Juan Pérez', '3001234567', 'moto', 'GHI789', 1),
(34, 3, 1, 4, 3, 1, 'entregado', 4, '8000.00', 30, NULL, '2025-08-06 14:48:29', '2025-08-06 15:04:10', '2025-08-06 17:04:24', '2025-08-06 17:04:24', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'Norte', '8000.00', 'Ana García', '3003456789', 'carguero', 'ADS552', 1),
(35, 4, 14, 8, 4, 2, 'entregado', 4, '5000.00', 30, NULL, '2025-08-06 14:48:56', '2025-08-06 17:04:21', '2025-08-06 17:04:29', '2025-08-06 17:04:29', 'CARLITOS', '5555555555', '88888888888', 'Calle 4 carrera 9', 'Occidente', '5000.00', 'Luis Rodríguez', '3004567890', 'moto', 'GHI789', 1),
(36, 1, 1, 14, 1, 1, 'entregado', 4, '5000.00', 30, NULL, '2025-08-08 11:11:35', '2025-08-08 11:12:11', '2025-08-08 11:12:20', '2025-08-08 11:12:21', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90', 'Norte', '5000.00', 'Juan Pérez', '3001234567', 'carguero', 'ADS552', 1),
(37, 1, 14, 31, 2, 1, 'entregado', 5, '5000.00', 30, NULL, '2025-08-09 08:30:17', '2025-08-09 12:38:28', '2025-08-09 12:38:35', '2025-08-09 12:38:35', 'CARLITOS', '5555555555', '88888888888', 'Calle 4 carrera 9', 'Oriente', '5000.00', 'Carlos López', '3002345678', 'carguero', 'ADS552', 1);

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
  `estado` enum('pendiente','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `hora_salida` datetime DEFAULT NULL,
  `hora_llegada` datetime DEFAULT NULL,
  `fecha_pedido` datetime DEFAULT CURRENT_TIMESTAMP,
  `cantidad_paquetes` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `tiempo_estimado` int(11) DEFAULT NULL,
  `hora_estimada_entrega` time DEFAULT NULL,
  `envio_inmediato` enum('si','no') DEFAULT 'no',
  `alistamiento` enum('si','no') DEFAULT 'no',
  `movido_historico` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_cliente`, `id_domiciliario`, `id_vehiculo`, `id_zona`, `estado`, `hora_salida`, `hora_llegada`, `fecha_pedido`, `cantidad_paquetes`, `total`, `tiempo_estimado`, `hora_estimada_entrega`, `envio_inmediato`, `alistamiento`, `movido_historico`) VALUES
(4, 14, NULL, NULL, 31, 'pendiente', NULL, NULL, '2025-08-13 16:02:41', 5, '5000.00', 30, '16:32:00', 'si', 'si', 0);

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
  `tipo` enum('moto','camioneta','carguero') NOT NULL,
  `placa` varchar(20) NOT NULL,
  `estado` enum('disponible','en_ruta','mantenimiento','inactivo') NOT NULL DEFAULT 'disponible',
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id_vehiculo`, `tipo`, `placa`, `estado`, `descripcion`) VALUES
(1, 'carguero', 'ADS552', 'disponible', 'CARGUERO'),
(2, 'moto', 'GHI789', 'disponible', 'MOTO'),
(3, 'camioneta', 'STU901', 'disponible', 'CAMIONETA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zonas`
--

CREATE TABLE `zonas` (
  `id_zona` int(11) NOT NULL,
  `nombre` enum('Norte','Sur','Oriente','Occidente','Nororiente','Noroccidente','Suroriente','Suroccidente') NOT NULL,
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
(8, 'Norte', 'SOTAVENTO', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(9, 'Norte', 'LA PISTA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(10, 'Norte', 'PARCELACION FLORENCIA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(11, 'Norte', 'VERGEL', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(12, 'Norte', 'SANTA ELENA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(13, 'Norte', 'BERLIN', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(14, 'Norte', 'LLANITOS', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(15, 'Norte', 'ESPERANZA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(16, 'Sur', 'GAMA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(17, 'Sur', 'CALLE LARGA Y CALLE MOCHA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(18, 'Sur', 'LA BALASTRERA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(19, 'Sur', 'VEGAS DEL CALIMA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(20, 'Sur', 'LA UNION', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(21, 'Sur', 'SAN JOSE', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(22, 'Sur', 'LA PRIMAVERA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(23, 'Sur', 'LA PLAYA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(24, 'Oriente', 'EL REMOLINO', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(25, 'Oriente', 'REFUGIOS', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(26, 'Oriente', 'ENTRADA 5', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(27, 'Oriente', 'IGUAZU', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(28, 'Oriente', 'OBRERO', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(29, 'Oriente', 'AREA DE LA SIMON', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(30, 'Oriente', 'OBRERO', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(31, 'Oriente', 'SAN VICENTE', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(32, 'Occidente', 'PARTE MAS LEJANA DEL MUSA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(33, 'Occidente', 'DOSQUEBRADAS', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(34, 'Occidente', 'CASA QUINDIANAS', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(35, 'Occidente', 'CANADA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(36, 'Occidente', 'CICUENTENARIO', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(37, 'Occidente', 'ALMENDROS', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(38, 'Occidente', 'TULIPANES', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(39, 'Occidente', 'LA PALMA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(40, 'Nororiente', 'SAN JORGE', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(41, 'Nororiente', 'FUNDADORES EN TODA SU AREA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(42, 'Nororiente', 'LA VIRGEN', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(43, 'Nororiente', 'GUAYACANES', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(44, 'Nororiente', 'MARIA LUISA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(45, 'Nororiente', 'EL DORADO', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(46, 'Nororiente', 'CANAGUAROS', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(47, 'Nororiente', 'SAN ANTONIO', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(48, 'Noroccidente', 'VILLAS DEL LAGO', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(49, 'Noroccidente', 'CIUDADELA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(50, 'Noroccidente', 'GALICIA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(51, 'Noroccidente', 'LA UNION', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(52, 'Noroccidente', 'PRIMAVERA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(53, 'Noroccidente', 'LA PLAYA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(54, 'Noroccidente', 'REFUGIOS', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(55, 'Noroccidente', 'VERGEL', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(56, 'Suroriente', 'LA PISTA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(57, 'Suroriente', 'ENTRADA 5', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(58, 'Suroriente', 'FLORENCIA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(59, 'Suroriente', 'BERLIN', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(60, 'Suroccidente', 'SANTA HELENA', '5000.00', 'activo', '2025-08-08 14:49:28', 15),
(61, 'Oriente', 'LAGUITOS', '5000.00', 'activo', '2025-08-08 14:49:28', 20);

--
-- Índices para tablas volcadas
--

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
  ADD KEY `id_zona` (`id_zona`),
  ADD KEY `idx_domiciliarios_estado` (`estado`),
  ADD KEY `idx_domiciliarios_zona` (`id_zona`);

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
  ADD KEY `idx_hora_llegada` (`hora_llegada`),
  ADD KEY `idx_pedidos_domiciliario_fecha` (`id_domiciliario`,`fecha_pedido`),
  ADD KEY `idx_pedidos_estado_fecha` (`estado`,`fecha_pedido`);

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
  MODIFY `id_historico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `zonas`
--
ALTER TABLE `zonas`
  MODIFY `id_zona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- Restricciones para tablas volcadas
--

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
