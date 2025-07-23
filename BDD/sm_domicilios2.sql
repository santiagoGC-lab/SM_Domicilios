-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 23-07-2025 a las 15:14:28
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

--
-- Volcado de datos para la tabla `actividad_reciente`
--

INSERT INTO `actividad_reciente` (`id_actividad`, `tipo_actividad`, `descripcion`, `id_usuario`, `id_pedido`, `fecha_actividad`) VALUES
(1, 'pedido_archivado', 'Pedido #4 archivado por usuario #1', 1, 4, '2025-07-14 11:47:32'),
(2, 'pedido_archivado', 'Pedido #2 archivado por usuario #1', 1, 2, '2025-07-14 11:52:25'),
(3, 'pedido_archivado', 'Pedido #1 archivado por usuario #1', 1, 1, '2025-07-14 11:53:00');

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
  `tipo_cliente` enum('regular','vip','corporativo') NOT NULL DEFAULT 'regular',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `documento`, `telefono`, `direccion`, `barrio`, `tipo_cliente`, `fecha_creacion`, `estado`) VALUES
(1, 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90|', 'La Esperanza', 'regular', '2025-07-09 14:27:48', 'activo'),
(2, 'María González', '12345678', '3001111111', 'Calle 123 #45-67', 'La Soledad', 'regular', '2025-07-12 08:44:28', 'activo'),
(3, 'Pedro Martínez', '87654321', '3002222222', 'Carrera 78 #90-12', 'Chapinero', 'vip', '2025-07-12 08:44:28', 'activo'),
(4, 'Sofia Ruiz', '11223344', '3003333333', 'Avenida 15 #23-45', 'Usaquén', 'regular', '2025-07-12 08:44:28', 'activo'),
(5, 'Roberto Silva', '4433221', '3004444444', 'Calle 89 #12-34', 'Suba', 'corporativo', '2025-07-12 08:44:28', 'activo'),
(6, 'Juan Pérez', '10000001', '3001111111', 'Calle 10 #5-20', 'Centro', 'regular', '2025-07-15 15:49:45', 'activo'),
(7, 'María Gómez', '10000002', '3002222222', 'Carrera 8 #12-34', 'Norte', 'vip', '2025-07-15 15:49:45', 'activo'),
(8, 'Carlos Ruiz', '10000003', '3003333333', 'Avenida 15 #45-67', 'Sur', 'regular', '2025-07-15 15:49:45', 'activo'),
(9, 'Ana Torres', '10000004', '3004444444', 'Calle 20 #8-90', 'Occidente', 'corporativo', '2025-07-15 15:49:45', 'activo'),
(10, 'Luisa Martínez', '10000005', '3005555555', 'Carrera 30 #10-50', 'Centro', 'regular', '2025-07-15 15:49:45', 'activo'),
(11, 'Laura Fernández', '55443322', '3005555555', 'Calle 45 #67-89', 'Fontibón', 'regular', '2025-07-15 15:00:00', 'activo'),
(12, 'Andrés Morales', '99887766', '3006666666', 'Carrera 12 #34-56', 'Teusaquillo', 'vip', '2025-07-15 15:10:00', 'activo'),
(13, 'Camila Torres', '77665544', '3007777777', 'Avenida 68 #90-12', 'Engativá', 'corporativo', '2025-07-15 15:20:00', 'activo'),
(14, 'CARLITOS', '5555555555', '88888888888', 'Calle 4 carrera 9', 'San vicente', 'regular', '2025-07-23 08:47:54', 'activo');

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
(1, 'Juan Pérez', '3001234567', 3, 'disponible', '2025-07-12 08:44:28'),
(2, 'Carlos López', '3002345678', 3, 'disponible', '2025-07-12 08:44:28'),
(3, 'Ana García', '3003456789', NULL, 'disponible', '2025-07-12 08:44:28'),
(4, 'Luis Rodríguez', '3004567890', 3, 'disponible', '2025-07-12 08:44:28'),
(6, 'Diego Ramírez', '3005678901', 1, 'disponible', '2025-07-15 15:50:00'),
(7, 'Valeria Castro', '3006789012', 2, 'disponible', '2025-07-15 16:00:00'),
(8, 'Felipe Gómez', '3007890123', 3, 'disponible', '2025-07-15 16:10:00');

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
  `estado` enum('entregado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad_paquetes` int(11) NOT NULL DEFAULT '1',
  `total` decimal(10,2) NOT NULL,
  `tiempo_estimado` int(11) NOT NULL DEFAULT '30',
  `fecha_pedido` datetime NOT NULL,
  `fecha_completado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cliente_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cliente_direccion` text COLLATE utf8mb4_unicode_ci,
  `zona_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zona_tarifa` decimal(10,2) NOT NULL,
  `domiciliario_nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domiciliario_telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_proceso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `historico_pedidos`
--

INSERT INTO `historico_pedidos` (`id_historico`, `id_pedido_original`, `id_cliente`, `id_zona`, `id_domiciliario`, `estado`, `cantidad_paquetes`, `total`, `tiempo_estimado`, `fecha_pedido`, `fecha_completado`, `cliente_nombre`, `cliente_documento`, `cliente_telefono`, `cliente_direccion`, `zona_nombre`, `zona_tarifa`, `domiciliario_nombre`, `domiciliario_telefono`, `usuario_proceso`) VALUES
(1, 4, 4, 3, 1, 'entregado', 1, '7000.00', 30, '2025-07-12 13:29:28', '2025-07-14 11:47:32', 'Sofia Ruiz', '11223344', '3003333333', 'Avenida 15 #23-45', 'Sur', '7000.00', 'Juan Pérez', '3001234567', 1),
(2, 2, 2, 2, 2, 'entregado', 1, '12000.00', 30, '2025-07-12 12:44:28', '2025-07-14 11:52:25', 'María González', '12345678', '3001111111', 'Calle 123 #45-67', 'Centro', '6000.00', 'Carlos López', '3002345678', 1),
(3, 1, 1, 1, 1, 'entregado', 2, '10000.00', 30, '2025-07-12 11:44:28', '2025-07-14 11:53:00', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90|', 'Occidente', '5000.00', 'Juan Pérez', '3001234567', 1),
(4, 7, 2, 3, 1, 'entregado', 55, '7000.00', 30, '2025-07-15 14:45:03', '2025-07-15 15:29:08', 'María González', '12345678', '3001111111', 'Calle 123 #45-67', 'Sur', '7000.00', 'Juan Pérez', '3001234567', 1),
(5, 6, 4, 1, 2, 'entregado', 11, '5000.00', 30, '2025-07-15 14:44:43', '2025-07-15 15:42:35', 'Sofia Ruiz', '11223344', '3003333333', 'Avenida 15 #23-45', 'Occidente', '5000.00', 'Carlos López', '3002345678', 1),
(6, 7, 2, 3, 1, 'entregado', 55, '7000.00', 30, '2025-07-15 14:45:03', '2025-07-15 16:50:00', 'María González', '12345678', '3001111111', 'Calle 123 #45-67', 'Sur', '7000.00', 'Juan Pérez', '3001234567', 1),
(7, 6, 4, 1, 2, 'entregado', 11, '5000.00', 30, '2025-07-15 14:44:43', '2025-07-15 17:00:00', 'Sofia Ruiz', '11223344', '3003333333', 'Avenida 15 #23-45', 'Occidente', '5000.00', 'Carlos López', '3002345678', 1),
(8, 11, 4, 1, 3, 'entregado', 1, '7000.00', 20, '2025-07-15 15:49:53', '2025-07-15 16:23:40', 'Sofia Ruiz', '11223344', '3003333333', 'Avenida 15 #23-45', 'Occidente', '5000.00', 'Ana García', '3003456789', 1),
(9, 8, 1, 1, 1, 'entregado', 2, '15000.00', 30, '2025-07-14 15:49:53', '2025-07-15 16:23:47', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90|', 'Occidente', '5000.00', 'Juan Pérez', '3001234567', 1),
(10, 25, 6, 3, 3, 'entregado', 6, '7000.00', 30, '2025-07-15 16:13:19', '2025-07-16 16:45:00', 'Juan Pérez', '10000001', '3001111111', 'Calle 10 #5-20', 'Sur', '7000.00', 'Ana García', '3003456789', 1),
(11, 9, 2, 2, 2, 'entregado', 1, '8000.00', 25, '2025-07-15 15:49:53', '2025-07-16 16:45:03', 'María González', '12345678', '3001111111', 'Calle 123 #45-67', 'Centro', '6000.00', 'Carlos López', '3002345678', 1),
(12, 5, 1, 1, 2, 'entregado', 5, '5000.00', 30, '2025-07-22 17:23:05', '2025-07-22 17:24:24', 'wilmer', '1107841204', '3153194602', 'Transversal 8 #12A-90|', 'Occidente', '5000.00', 'Carlos López', '3002345678', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_domiciliario` int(11) DEFAULT NULL,
  `id_zona` int(11) NOT NULL,
  `estado` enum('pendiente','en_camino','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `fecha_pedido` datetime DEFAULT CURRENT_TIMESTAMP,
  `cantidad_paquetes` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `tiempo_estimado` int(11) DEFAULT NULL,
  `movido_historico` tinyint(1) DEFAULT '0',
  `id_vehiculo` int(11) DEFAULT NULL,
  `hora_salida` datetime DEFAULT NULL,
  `hora_llegada` datetime DEFAULT NULL,
  `domicilio_compartido` enum('SI','NO') DEFAULT 'NO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_cliente`, `id_domiciliario`, `id_zona`, `estado`, `fecha_pedido`, `cantidad_paquetes`, `total`, `tiempo_estimado`, `movido_historico`) VALUES
(1, 1, 1, 1, 'entregado', '2025-07-12 11:44:28', 2, '10000.00', 30, 1),
(2, 2, 2, 2, 'entregado', '2025-07-12 12:44:28', 1, '12000.00', 30, 1),
(4, 4, 1, 3, 'entregado', '2025-07-12 13:29:28', 1, '7000.00', 30, 1),
(5, 1, NULL, 4, 'pendiente', '2025-07-23 10:01:57', 5, '8000.00', 30, 0);

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
-- Estructura de tabla para la tabla `zonas`
--

CREATE TABLE `zonas` (
  `id_zona` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `barrio` varchar(255) DEFAULT NULL,
  `tarifa_base` decimal(10,2) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `zonas`
--

INSERT INTO `zonas` (`id_zona`, `nombre`, `barrio`, `tarifa_base`, `estado`, `fecha_creacion`) VALUES
(1, 'Occidente', 'San vicente', '5000.00', 'activo', '2025-07-12 08:44:28'),
(2, 'Centro', 'Las palmas', '6000.00', 'activo', '2025-07-12 08:44:28'),
(3, 'Sur', 'La ciudadela', '7000.00', 'activo', '2025-07-12 08:44:28'),
(4, 'Norte', 'La esperanza', '8000.00', 'activo', '2025-07-12 08:44:28'),
(6, 'Oriente', 'CUBA', '50000.00', 'activo', '2025-07-15 14:42:54');

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
  ADD KEY `id_vehiculo` (`id_vehiculo`);

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
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `domiciliarios`
--
ALTER TABLE `domiciliarios`
  MODIFY `id_domiciliario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `historico_pedidos`
--
ALTER TABLE `historico_pedidos`
  MODIFY `id_historico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  ADD CONSTRAINT `pedidos_ibfk_3` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id_zona`),
  ADD CONSTRAINT `fk_pedidos_vehiculo` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`);
COMMIT;

-- === CORRECCIONES PARA FLUJO COORDINADOR/VEHICULOS ===

-- 1. Crear tabla vehiculos
CREATE TABLE IF NOT EXISTS `vehiculos` (
  `id_vehiculo` INT(11) NOT NULL AUTO_INCREMENT,
  `tipo` VARCHAR(50) NOT NULL,
  `placa` VARCHAR(20) NOT NULL UNIQUE,
  `estado` ENUM('disponible','en_ruta','mantenimiento','inactivo') NOT NULL DEFAULT 'disponible',
  `descripcion` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_vehiculo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2. Quitar vehiculo y placa de domiciliarios
ALTER TABLE `domiciliarios` DROP COLUMN `vehiculo`;
ALTER TABLE `domiciliarios` DROP COLUMN `placa`;

-- 3. Agregar campos a pedidos
ALTER TABLE `pedidos` ADD COLUMN `id_vehiculo` INT(11) DEFAULT NULL AFTER `id_domiciliario`;
ALTER TABLE `pedidos` ADD COLUMN `hora_salida` DATETIME DEFAULT NULL AFTER `estado`;
ALTER TABLE `pedidos` ADD COLUMN `hora_llegada` DATETIME DEFAULT NULL AFTER `hora_salida`;
ALTER TABLE `pedidos` ADD COLUMN `domicilio_compartido` ENUM('SI','NO') DEFAULT 'NO' AFTER `hora_llegada`;

-- 4. Relación pedidos-vehiculos
ALTER TABLE `pedidos` ADD CONSTRAINT `fk_pedidos_vehiculo` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`);

-- === FIN DE CORRECCIONES ===

-- === REPARAR CLAVES FORÁNEAS DE PEDIDOS ===

-- Eliminar claves foráneas si existen
ALTER TABLE pedidos DROP FOREIGN KEY IF EXISTS pedidos_ibfk_1;
ALTER TABLE pedidos DROP FOREIGN KEY IF EXISTS pedidos_ibfk_2;
ALTER TABLE pedidos DROP FOREIGN KEY IF EXISTS fk_pedidos_vehiculo;

-- Agregar claves foráneas correctamente
ALTER TABLE pedidos
  ADD CONSTRAINT pedidos_ibfk_1 FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
  ADD CONSTRAINT pedidos_ibfk_2 FOREIGN KEY (id_domiciliario) REFERENCES domiciliarios(id_domiciliario),
  ADD CONSTRAINT fk_pedidos_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id_vehiculo);

-- === FIN REPARACIÓN CLAVES FORÁNEAS ===

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
