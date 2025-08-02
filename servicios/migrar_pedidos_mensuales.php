<?php
require_once 'conexion.php';
session_start();

// Solo permitir ejecución manual (CLI o admin)
if (php_sapi_name() !== 'cli' && (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin')) {
    die('Acceso denegado');
}

$db = ConectarDB();

// Obtener el mes y año anterior
$mesAnterior = date('Y-m', strtotime('first day of last month'));
$mesAnteriorInicio = $mesAnterior . '-01';
$mesAnteriorFin = date('Y-m-t', strtotime($mesAnteriorInicio));

echo "Migrando pedidos del mes anterior: $mesAnteriorInicio hasta $mesAnteriorFin\n";

// Seleccionar pedidos archivados del mes anterior completo
$sql = "SELECT * FROM historico_pedidos 
        WHERE DATE(fecha_completado) >= ? 
        AND DATE(fecha_completado) <= ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("ss", $mesAnteriorInicio, $mesAnteriorFin);
$stmt->execute();
$result = $stmt->get_result();

$pedidos = [];
while ($row = $result->fetch_assoc()) {
    $pedidos[] = $row;
}
$stmt->close();

if (empty($pedidos)) {
    echo "No hay pedidos del mes anterior para migrar.\n";
    $db->close();
    exit;
}

echo "Encontrados " . count($pedidos) . " pedidos para migrar.\n";

// Insertar en pedidos_mensuales
$stmt = $db->prepare("INSERT INTO pedidos_mensuales (
    id_pedido_original, id_cliente, id_zona, id_domiciliario, estado, cantidad_paquetes, total, tiempo_estimado, fecha_pedido, fecha_completado,
    cliente_nombre, cliente_documento, cliente_telefono, cliente_direccion, zona_nombre, zona_tarifa, domiciliario_nombre, domiciliario_telefono, usuario_proceso, mes, anio
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$migrados = 0;
foreach ($pedidos as $pedido) {
    $fecha = new DateTime($pedido['fecha_pedido']);
    $mes = (int)$fecha->format('m');
    $anio = (int)$fecha->format('Y');

    try {
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
        $migrados++;
    } catch (Exception $e) {
        echo "Error migrando pedido #{$pedido['id_pedido_original']}: " . $e->getMessage() . "\n";
    }
}
$stmt->close();

if ($migrados > 0) {
    // Eliminar los pedidos migrados de historico_pedidos
    $ids = implode(',', array_map('intval', array_column($pedidos, 'id_historico')));
    $db->query("DELETE FROM historico_pedidos WHERE id_historico IN ($ids)");

    echo "Migración completada: $migrados pedidos movidos a pedidos_mensuales.\n";
    echo "Los pedidos del mes anterior ahora están disponibles en Reportes.\n";
} else {
    echo "No se pudo migrar ningún pedido.\n";
}

$db->close();
