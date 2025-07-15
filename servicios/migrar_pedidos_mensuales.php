<?php
require_once 'conexion.php';
session_start();

// Solo permitir ejecución manual (CLI o admin)
if (php_sapi_name() !== 'cli' && (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin')) {
    die('Acceso denegado');
}

$db = ConectarDB();

// Seleccionar pedidos archivados con más de 7 días
$sql = "SELECT * FROM historico_pedidos WHERE fecha_completado < DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = $db->query($sql);
$pedidos = [];
while ($row = $result->fetch_assoc()) {
    $pedidos[] = $row;
}

if (empty($pedidos)) {
    echo "No hay pedidos para migrar.\n";
    $db->close();
    exit;
}

$stmt = $db->prepare("INSERT INTO pedidos_mensuales (
    id_pedido_original, id_cliente, id_zona, id_domiciliario, estado, cantidad_paquetes, total, tiempo_estimado, fecha_pedido, fecha_completado,
    cliente_nombre, cliente_documento, cliente_telefono, cliente_direccion, zona_nombre, zona_tarifa, domiciliario_nombre, domiciliario_telefono, usuario_proceso, mes, anio
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($pedidos as $pedido) {
    $fecha = new DateTime($pedido['fecha_pedido']);
    $mes = (int)$fecha->format('m');
    $anio = (int)$fecha->format('Y');
    $stmt->bind_param(
        "iiiisidssssssssssiii",
        $pedido['id_pedido_original'],
        $pedido['id_cliente'],
        $pedido['id_zona'],
        $pedido['id_domiciliario'],
        $pedido['estado'],
        $pedido['cantidad_paquetes'],
        $pedido['total'],
        $pedido['tiempo_estimado'],
        $pedido['fecha_pedido'],
        $pedido['fecha_completado'],
        $pedido['cliente_nombre'],
        $pedido['cliente_documento'],
        $pedido['cliente_telefono'],
        $pedido['cliente_direccion'],
        $pedido['zona_nombre'],
        $pedido['zona_tarifa'],
        $pedido['domiciliario_nombre'],
        $pedido['domiciliario_telefono'],
        $pedido['usuario_proceso'],
        $mes,
        $anio
    );
    $stmt->execute();
}
$stmt->close();

// Eliminar los pedidos migrados de historico_pedidos
$ids = implode(',', array_map('intval', array_column($pedidos, 'id_historico')));
$db->query("DELETE FROM historico_pedidos WHERE id_historico IN ($ids)");

$db->close();
echo "Pedidos migrados correctamente a pedidos_mensuales.\n"; 