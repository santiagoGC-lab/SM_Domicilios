<?php
require_once 'conexion.php';
session_start();

// Solo permitir ejecución manual (CLI o admin)
if (php_sapi_name() !== 'cli' && (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin')) {
    die('Acceso denegado');
}

$db = ConectarDB();

// Obtener información del mes actual y anterior
$mesActual = date('Y-m');
$mesAnterior = date('Y-m', strtotime('first day of last month'));
$mesAnteriorInicio = $mesAnterior . '-01';
$mesAnteriorFin = date('Y-m-t', strtotime($mesAnteriorInicio));

echo "=== VERIFICACIÓN DE MIGRACIÓN MENSUAL ===\n";
echo "Fecha actual: " . date('Y-m-d H:i:s') . "\n";
echo "Mes actual: $mesActual\n";
echo "Mes anterior: $mesAnterior ($mesAnteriorInicio - $mesAnteriorFin)\n\n";

// Verificar pedidos en historico_pedidos del mes anterior
$sql = "SELECT COUNT(*) as total, 
               SUM(CASE WHEN estado = 'entregado' THEN total ELSE 0 END) as ingresos_entregados,
               SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados
        FROM historico_pedidos 
        WHERE DATE(fecha_completado) >= ? 
        AND DATE(fecha_completado) <= ?";

$stmt = $db->prepare($sql);
$stmt->bind_param("ss", $mesAnteriorInicio, $mesAnteriorFin);
$stmt->execute();
$result = $stmt->get_result();
$datosMesAnterior = $result->fetch_assoc();
$stmt->close();

echo "PEDIDOS DEL MES ANTERIOR EN HISTORIAL:\n";
echo "- Total de pedidos: " . $datosMesAnterior['total'] . "\n";
echo "- Ingresos de entregados: $" . number_format($datosMesAnterior['ingresos_entregados'] ?? 0, 2) . "\n";
echo "- Pedidos cancelados: " . $datosMesAnterior['cancelados'] . "\n\n";

// Verificar pedidos en historico_pedidos del mes actual
$mesActualInicio = $mesActual . '-01';
$mesActualFin = date('Y-m-t', strtotime($mesActualInicio));

$sql = "SELECT COUNT(*) as total, 
               SUM(CASE WHEN estado = 'entregado' THEN total ELSE 0 END) as ingresos_entregados,
               SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados
        FROM historico_pedidos 
        WHERE DATE(fecha_completado) >= ? 
        AND DATE(fecha_completado) <= ?";

$stmt = $db->prepare($sql);
$stmt->bind_param("ss", $mesActualInicio, $mesActualFin);
$stmt->execute();
$result = $stmt->get_result();
$datosMesActual = $result->fetch_assoc();
$stmt->close();

echo "PEDIDOS DEL MES ACTUAL EN HISTORIAL:\n";
echo "- Total de pedidos: " . $datosMesActual['total'] . "\n";
echo "- Ingresos de entregados: $" . number_format($datosMesActual['ingresos_entregados'] ?? 0, 2) . "\n";
echo "- Pedidos cancelados: " . $datosMesActual['cancelados'] . "\n\n";

// Verificar pedidos ya migrados a pedidos_mensuales
$sql = "SELECT COUNT(*) as total, 
               SUM(CASE WHEN estado = 'entregado' THEN total ELSE 0 END) as ingresos_entregados,
               SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados
        FROM pedidos_mensuales 
        WHERE mes = ? AND anio = ?";

$mesAnteriorNum = (int)date('m', strtotime($mesAnteriorInicio));
$anioAnterior = (int)date('Y', strtotime($mesAnteriorInicio));

$stmt = $db->prepare($sql);
$stmt->bind_param("ii", $mesAnteriorNum, $anioAnterior);
$stmt->execute();
$result = $stmt->get_result();
$datosMigrados = $result->fetch_assoc();
$stmt->close();

echo "PEDIDOS YA MIGRADOS A REPORTES (mes anterior):\n";
echo "- Total de pedidos: " . $datosMigrados['total'] . "\n";
echo "- Ingresos de entregados: $" . number_format($datosMigrados['ingresos_entregados'] ?? 0, 2) . "\n";
echo "- Pedidos cancelados: " . $datosMigrados['cancelados'] . "\n\n";

// Recomendaciones
echo "=== RECOMENDACIONES ===\n";

if ($datosMesAnterior['total'] > 0) {
    echo "⚠️  Hay " . $datosMesAnterior['total'] . " pedidos del mes anterior que necesitan migración.\n";
    echo "   Ejecute: php servicios/migrar_pedidos_mensuales.php\n\n";
} else {
    echo "✅ No hay pedidos del mes anterior pendientes de migración.\n\n";
}

if ($datosMesActual['total'] > 0) {
    echo "✅ El historial del mes actual tiene " . $datosMesActual['total'] . " pedidos.\n";
    echo "   Estos se mostrarán en la vista de Historial de Pedidos.\n\n";
} else {
    echo "ℹ️  No hay pedidos archivados en el mes actual.\n\n";
}

// Verificar si es momento de migrar (primer día del mes)
$hoy = date('d');
if ($hoy == '01') {
    echo "📅 Es el primer día del mes. Considere ejecutar la migración si no se ha hecho.\n";
} else {
    $diasRestantes = 32 - $hoy; // Aproximado para el siguiente mes
    echo "📅 Faltan aproximadamente $diasRestantes días para el próximo ciclo de migración.\n";
}

$db->close();
echo "\n=== FIN DE VERIFICACIÓN ===\n";
?> 