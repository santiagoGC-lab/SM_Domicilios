<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once 'conexion.php';

try {
    $tipo = $_GET['tipo'] ?? 'general';
    
    switch ($tipo) {
        case 'general':
            exportarReporteGeneral($pdo);
            break;
        case 'detallado':
            exportarReporteDetallado($pdo);
            break;
        case 'estados':
            exportarReporteEstados($pdo);
            break;
        case 'zonas':
            exportarReporteZonas($pdo);
            break;
        case 'ingresos':
            exportarReporteIngresos($pdo);
            break;
        case 'domiciliarios':
            exportarReporteDomiciliarios($pdo);
            break;
        case 'clientes':
            exportarReporteClientes($pdo);
            break;
        case 'detalle_zonas':
            exportarDetalleZonas($pdo);
            break;
        default:
            throw new Exception('Tipo de reporte no válido');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function exportarReporteGeneral($pdo) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "reporte_general_{$fecha}.csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Encabezados
    fputcsv($output, ['REPORTE GENERAL - SM DOMICILIOS']);
    fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    
    // Estadísticas generales
    $totalPedidos = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
    $pedidosHoy = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()")->fetchColumn();
    $ingresosHoy = $pdo->query("SELECT SUM(total) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE() AND estado = 'entregado'")->fetchColumn() ?? 0;
    $pedidosPendientes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn();
    
    fputcsv($output, ['ESTADÍSTICAS GENERALES']);
    fputcsv($output, ['Total Pedidos', $totalPedidos]);
    fputcsv($output, ['Pedidos Hoy', $pedidosHoy]);
    fputcsv($output, ['Ingresos Hoy', '$' . number_format($ingresosHoy, 2)]);
    fputcsv($output, ['Pedidos Pendientes', $pedidosPendientes]);
    fputcsv($output, []);
    
    // Pedidos por estado
    fputcsv($output, ['PEDIDOS POR ESTADO']);
    fputcsv($output, ['Estado', 'Cantidad']);
    
    $estados = $pdo->query("SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado ORDER BY total DESC")->fetchAll();
    foreach ($estados as $estado) {
        fputcsv($output, [$estado['estado'], $estado['total']]);
    }
    
    fclose($output);
}

function exportarReporteDetallado($pdo) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "reporte_detallado_{$fecha}.csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Encabezados
    fputcsv($output, ['REPORTE DETALLADO - SM DOMICILIOS']);
    fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    
    // Todos los pedidos
    fputcsv($output, ['TODOS LOS PEDIDOS']);
    fputcsv($output, ['ID', 'Cliente', 'Domiciliario', 'Zona', 'Estado', 'Fecha', 'Cantidad Paquetes', 'Total', 'Tiempo Estimado']);
    
    $pedidos = $pdo->query("
        SELECT p.id_pedido, c.nombre as cliente, d.nombre as domiciliario, z.nombre as zona, 
               p.estado, p.fecha_pedido, p.cantidad_paquetes, p.total, p.tiempo_estimado
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
        LEFT JOIN zonas z ON p.id_zona = z.id_zona
        ORDER BY p.fecha_pedido DESC
    ")->fetchAll();
    
    foreach ($pedidos as $pedido) {
        fputcsv($output, [
            $pedido['id_pedido'],
            $pedido['cliente'],
            $pedido['domiciliario'] ?: 'No asignado',
            $pedido['zona'],
            $pedido['estado'],
            $pedido['fecha_pedido'],
            $pedido['cantidad_paquetes'],
            '$' . number_format($pedido['total'], 2),
            $pedido['tiempo_estimado'] . ' min'
        ]);
    }
    
    fclose($output);
}

function exportarReporteEstados($pdo) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "reporte_estados_{$fecha}.csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['REPORTE DE ESTADOS - SM DOMICILIOS']);
    fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    fputcsv($output, ['Estado', 'Cantidad', 'Porcentaje']);
    
    $total = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
    $estados = $pdo->query("SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado ORDER BY total DESC")->fetchAll();
    
    foreach ($estados as $estado) {
        $porcentaje = $total > 0 ? round(($estado['total'] / $total) * 100, 2) : 0;
        fputcsv($output, [$estado['estado'], $estado['total'], $porcentaje . '%']);
    }
    
    fclose($output);
}

function exportarReporteZonas($pdo) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "reporte_zonas_{$fecha}.csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['REPORTE DE ZONAS - SM DOMICILIOS']);
    fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    fputcsv($output, ['Zona', 'Pedidos', 'Ingresos', 'Promedio por Pedido']);
    
    $zonas = $pdo->query("
        SELECT z.nombre, COUNT(p.id_pedido) as total, SUM(p.total) as ingresos
        FROM zonas z
        LEFT JOIN pedidos p ON z.id_zona = p.id_zona
        WHERE z.estado = 'activo'
        GROUP BY z.id_zona, z.nombre
        ORDER BY total DESC
    ")->fetchAll();
    
    foreach ($zonas as $zona) {
        $promedio = $zona['total'] > 0 ? $zona['ingresos'] / $zona['total'] : 0;
        fputcsv($output, [
            $zona['nombre'],
            $zona['total'],
            '$' . number_format($zona['ingresos'] ?? 0, 2),
            '$' . number_format($promedio, 2)
        ]);
    }
    
    fclose($output);
}

function exportarReporteIngresos($pdo) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "reporte_ingresos_{$fecha}.csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['REPORTE DE INGRESOS - SM DOMICILIOS']);
    fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    fputcsv($output, ['Fecha', 'Pedidos', 'Ingresos', 'Promedio por Pedido']);
    
    $ingresos = $pdo->query("
        SELECT DATE(fecha_pedido) as fecha, COUNT(*) as total, SUM(total) as ingresos
        FROM pedidos
        WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(fecha_pedido)
        ORDER BY fecha DESC
    ")->fetchAll();
    
    foreach ($ingresos as $ingreso) {
        $promedio = $ingreso['total'] > 0 ? $ingreso['ingresos'] / $ingreso['total'] : 0;
        fputcsv($output, [
            $ingreso['fecha'],
            $ingreso['total'],
            '$' . number_format($ingreso['ingresos'] ?? 0, 2),
            '$' . number_format($promedio, 2)
        ]);
    }
    
    fclose($output);
}

function exportarReporteDomiciliarios($pdo) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "reporte_domiciliarios_{$fecha}.csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['REPORTE DE DOMICILIARIOS - SM DOMICILIOS']);
    fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    fputcsv($output, ['Domiciliario', 'Entregas', 'Ingresos', 'Promedio por Entrega']);
    
    $domiciliarios = $pdo->query("
        SELECT d.nombre, COUNT(p.id_pedido) as entregas, SUM(p.total) as ingresos
        FROM domiciliarios d
        LEFT JOIN pedidos p ON d.id_domiciliario = p.id_domiciliario AND p.estado = 'entregado'
        WHERE d.estado IN ('disponible', 'ocupado')
        GROUP BY d.id_domiciliario, d.nombre
        ORDER BY entregas DESC
    ")->fetchAll();
    
    foreach ($domiciliarios as $domiciliario) {
        $promedio = $domiciliario['entregas'] > 0 ? $domiciliario['ingresos'] / $domiciliario['entregas'] : 0;
        fputcsv($output, [
            $domiciliario['nombre'],
            $domiciliario['entregas'],
            '$' . number_format($domiciliario['ingresos'] ?? 0, 2),
            '$' . number_format($promedio, 2)
        ]);
    }
    
    fclose($output);
}

function exportarReporteClientes($pdo) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "reporte_clientes_{$fecha}.csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['REPORTE DE CLIENTES - SM DOMICILIOS']);
    fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    fputcsv($output, ['Cliente', 'Pedidos', 'Total Gastado', 'Promedio por Pedido']);
    
    $clientes = $pdo->query("
        SELECT c.nombre, COUNT(p.id_pedido) as pedidos, SUM(p.total) as total_gastado
        FROM clientes c
        LEFT JOIN pedidos p ON c.id_cliente = p.id_cliente
        WHERE c.estado = 'activo'
        GROUP BY c.id_cliente, c.nombre
        HAVING pedidos > 0
        ORDER BY pedidos DESC
    ")->fetchAll();
    
    foreach ($clientes as $cliente) {
        $promedio = $cliente['pedidos'] > 0 ? $cliente['total_gastado'] / $cliente['pedidos'] : 0;
        fputcsv($output, [
            $cliente['nombre'],
            $cliente['pedidos'],
            '$' . number_format($cliente['total_gastado'] ?? 0, 2),
            '$' . number_format($promedio, 2)
        ]);
    }
    
    fclose($output);
}

function exportarDetalleZonas($pdo) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "detalle_zonas_{$fecha}.csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['DETALLE POR ZONAS - SM DOMICILIOS']);
    fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    fputcsv($output, ['Zona', 'Pedidos', 'Ingresos', 'Promedio por Pedido']);
    
    $zonas = $pdo->query("
        SELECT z.nombre, COUNT(p.id_pedido) as total, SUM(p.total) as ingresos
        FROM zonas z
        LEFT JOIN pedidos p ON z.id_zona = p.id_zona
        WHERE z.estado = 'activo'
        GROUP BY z.id_zona, z.nombre
        ORDER BY total DESC
    ")->fetchAll();
    
    foreach ($zonas as $zona) {
        $promedio = $zona['total'] > 0 ? $zona['ingresos'] / $zona['total'] : 0;
        fputcsv($output, [
            $zona['nombre'],
            $zona['total'],
            '$' . number_format($zona['ingresos'] ?? 0, 2),
            '$' . number_format($promedio, 2)
        ]);
    }
    
    fclose($output);
}
?> 