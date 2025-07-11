<?php
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="reporte_' . date('Y-m-d_H-i-s') . '.pdf"');

require_once 'conexion.php';

// Función simple para generar PDF básico
function generarPDF($titulo, $contenido) {
    $pdf = '';
    
    // Encabezado del PDF
    $pdf .= "%PDF-1.4\n";
    $pdf .= "1 0 obj\n";
    $pdf .= "<<\n";
    $pdf .= "/Type /Catalog\n";
    $pdf .= "/Pages 2 0 R\n";
    $pdf .= ">>\n";
    $pdf .= "endobj\n";
    
    // Páginas
    $pdf .= "2 0 obj\n";
    $pdf .= "<<\n";
    $pdf .= "/Type /Pages\n";
    $pdf .= "/Kids [3 0 R]\n";
    $pdf .= "/Count 1\n";
    $pdf .= ">>\n";
    $pdf .= "endobj\n";
    
    // Contenido de la página
    $pdf .= "3 0 obj\n";
    $pdf .= "<<\n";
    $pdf .= "/Type /Page\n";
    $pdf .= "/Parent 2 0 R\n";
    $pdf .= "/MediaBox [0 0 612 792]\n";
    $pdf .= "/Contents 4 0 R\n";
    $pdf .= ">>\n";
    $pdf .= "endobj\n";
    
    // Stream de contenido
    $pdf .= "4 0 obj\n";
    $pdf .= "<<\n";
    $pdf .= "/Length " . strlen($contenido) . "\n";
    $pdf .= ">>\n";
    $pdf .= "stream\n";
    $pdf .= $contenido;
    $pdf .= "\nendstream\n";
    $pdf .= "endobj\n";
    
    // Trailer
    $pdf .= "xref\n";
    $pdf .= "0 5\n";
    $pdf .= "0000000000 65535 f \n";
    $pdf .= "0000000009 00000 n \n";
    $pdf .= "0000000058 00000 n \n";
    $pdf .= "0000000115 00000 n \n";
    $pdf .= "0000000204 00000 n \n";
    $pdf .= "trailer\n";
    $pdf .= "<<\n";
    $pdf .= "/Size 5\n";
    $pdf .= "/Root 1 0 R\n";
    $pdf .= ">>\n";
    $pdf .= "startxref\n";
    $pdf .= "0\n";
    $pdf .= "%%EOF\n";
    
    return $pdf;
}

try {
    $tipo = $_GET['tipo'] ?? 'general';
    
    switch ($tipo) {
        case 'general':
            // Reporte general
            $totalPedidos = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
            $pedidosHoy = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()")->fetchColumn();
            $ingresosHoy = $pdo->query("SELECT SUM(total) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE() AND estado = 'entregado'")->fetchColumn() ?? 0;
            $pedidosPendientes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn();
            
            $contenido = "BT\n";
            $contenido .= "/F1 16 Tf\n";
            $contenido .= "50 750 Td\n";
            $contenido .= "(REPORTE GENERAL - SM DOMICILIOS) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 720 Td\n";
            $contenido .= "(Fecha: " . date('d/m/Y H:i:s') . ") Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 14 Tf\n";
            $contenido .= "50 680 Td\n";
            $contenido .= "(ESTADÍSTICAS GENERALES) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 650 Td\n";
            $contenido .= "(Total de Pedidos: $totalPedidos) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 620 Td\n";
            $contenido .= "(Pedidos Hoy: $pedidosHoy) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 590 Td\n";
            $contenido .= "(Ingresos Hoy: $" . number_format($ingresosHoy, 2) . ") Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 560 Td\n";
            $contenido .= "(Pedidos Pendientes: $pedidosPendientes) Tj\n";
            $contenido .= "ET\n";
            break;
            
        case 'detallado':
            // Reporte detallado con tablas
            $pedidosPorZona = $pdo->query("
                SELECT z.nombre, COUNT(p.id_pedido) as total, SUM(p.total) as ingresos
                FROM zonas z
                LEFT JOIN pedidos p ON z.id_zona = p.id_zona
                WHERE z.estado = 'activo'
                GROUP BY z.id_zona, z.nombre
                ORDER BY total DESC
            ")->fetchAll();
            
            $domiciliariosActivos = $pdo->query("
                SELECT d.nombre, COUNT(p.id_pedido) as entregas, SUM(p.total) as ingresos
                FROM domiciliarios d
                LEFT JOIN pedidos p ON d.id_domiciliario = p.id_domiciliario AND p.estado = 'entregado'
                WHERE d.estado IN ('disponible', 'ocupado')
                GROUP BY d.id_domiciliario, d.nombre
                ORDER BY entregas DESC
                LIMIT 5
            ")->fetchAll();
            
            $contenido = "BT\n";
            $contenido .= "/F1 16 Tf\n";
            $contenido .= "50 750 Td\n";
            $contenido .= "(REPORTE DETALLADO - SM DOMICILIOS) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 720 Td\n";
            $contenido .= "(Fecha: " . date('d/m/Y H:i:s') . ") Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 14 Tf\n";
            $contenido .= "50 680 Td\n";
            $contenido .= "(PEDIDOS POR ZONA) Tj\n";
            $contenido .= "ET\n";
            
            $y = 650;
            foreach ($pedidosPorZona as $zona) {
                $contenido .= "BT\n";
                $contenido .= "/F1 10 Tf\n";
                $contenido .= "50 $y Td\n";
                $contenido .= "(" . $zona['nombre'] . " - " . $zona['total'] . " pedidos - $" . number_format($zona['ingresos'] ?? 0, 2) . ") Tj\n";
                $contenido .= "ET\n";
                $y -= 20;
            }
            
            $contenido .= "BT\n";
            $contenido .= "/F1 14 Tf\n";
            $contenido .= "50 " . ($y - 20) . " Td\n";
            $contenido .= "(TOP DOMICILIARIOS) Tj\n";
            $contenido .= "ET\n";
            
            $y -= 50;
            foreach ($domiciliariosActivos as $domiciliario) {
                $contenido .= "BT\n";
                $contenido .= "/F1 10 Tf\n";
                $contenido .= "50 $y Td\n";
                $contenido .= "(" . $domiciliario['nombre'] . " - " . $domiciliario['entregas'] . " entregas - $" . number_format($domiciliario['ingresos'] ?? 0, 2) . ") Tj\n";
                $contenido .= "ET\n";
                $y -= 20;
            }
            break;
            
        case 'estados':
            // Reporte de pedidos por estado
            $pedidosPorEstado = $pdo->query("
                SELECT estado, COUNT(*) as total
                FROM pedidos
                GROUP BY estado
                ORDER BY total DESC
            ")->fetchAll();
            
            $contenido = "BT\n";
            $contenido .= "/F1 16 Tf\n";
            $contenido .= "50 750 Td\n";
            $contenido .= "(REPORTE POR ESTADOS - SM DOMICILIOS) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 720 Td\n";
            $contenido .= "(Fecha: " . date('d/m/Y H:i:s') . ") Tj\n";
            $contenido .= "ET\n";
            
            $y = 680;
            foreach ($pedidosPorEstado as $estado) {
                $contenido .= "BT\n";
                $contenido .= "/F1 12 Tf\n";
                $contenido .= "50 $y Td\n";
                $contenido .= "(" . ucfirst($estado['estado']) . ": " . $estado['total'] . " pedidos) Tj\n";
                $contenido .= "ET\n";
                $y -= 25;
            }
            break;
            
        case 'zonas':
            // Reporte de pedidos por zona
            $pedidosPorZona = $pdo->query("
                SELECT z.nombre, COUNT(p.id_pedido) as total, SUM(p.total) as ingresos
                FROM zonas z
                LEFT JOIN pedidos p ON z.id_zona = p.id_zona
                WHERE z.estado = 'activo'
                GROUP BY z.id_zona, z.nombre
                ORDER BY total DESC
            ")->fetchAll();
            
            $contenido = "BT\n";
            $contenido .= "/F1 16 Tf\n";
            $contenido .= "50 750 Td\n";
            $contenido .= "(REPORTE POR ZONAS - SM DOMICILIOS) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 720 Td\n";
            $contenido .= "(Fecha: " . date('d/m/Y H:i:s') . ") Tj\n";
            $contenido .= "ET\n";
            
            $y = 680;
            foreach ($pedidosPorZona as $zona) {
                $contenido .= "BT\n";
                $contenido .= "/F1 12 Tf\n";
                $contenido .= "50 $y Td\n";
                $contenido .= "(" . $zona['nombre'] . ": " . $zona['total'] . " pedidos - $" . number_format($zona['ingresos'] ?? 0, 2) . ") Tj\n";
                $contenido .= "ET\n";
                $y -= 25;
            }
            break;
            
        case 'ingresos':
            // Reporte de ingresos últimos 7 días
            $pedidosUltimos7Dias = $pdo->query("
                SELECT DATE(fecha_pedido) as fecha, COUNT(*) as total, SUM(total) as ingresos
                FROM pedidos
                WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(fecha_pedido)
                ORDER BY fecha DESC
            ")->fetchAll();
            
            $contenido = "BT\n";
            $contenido .= "/F1 16 Tf\n";
            $contenido .= "50 750 Td\n";
            $contenido .= "(REPORTE DE INGRESOS - SM DOMICILIOS) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 720 Td\n";
            $contenido .= "(Fecha: " . date('d/m/Y H:i:s') . ") Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 14 Tf\n";
            $contenido .= "50 680 Td\n";
            $contenido .= "(ÚLTIMOS 7 DÍAS) Tj\n";
            $contenido .= "ET\n";
            
            $y = 650;
            foreach ($pedidosUltimos7Dias as $dia) {
                $fecha = date('d/m/Y', strtotime($dia['fecha']));
                $contenido .= "BT\n";
                $contenido .= "/F1 12 Tf\n";
                $contenido .= "50 $y Td\n";
                $contenido .= "(" . $fecha . ": " . $dia['total'] . " pedidos - $" . number_format($dia['ingresos'] ?? 0, 2) . ") Tj\n";
                $contenido .= "ET\n";
                $y -= 25;
            }
            break;
            
        case 'domiciliarios':
            // Reporte de domiciliarios más activos
            $domiciliariosActivos = $pdo->query("
                SELECT d.nombre, COUNT(p.id_pedido) as entregas, SUM(p.total) as ingresos
                FROM domiciliarios d
                LEFT JOIN pedidos p ON d.id_domiciliario = p.id_domiciliario AND p.estado = 'entregado'
                WHERE d.estado IN ('disponible', 'ocupado')
                GROUP BY d.id_domiciliario, d.nombre
                ORDER BY entregas DESC
                LIMIT 5
            ")->fetchAll();
            
            $contenido = "BT\n";
            $contenido .= "/F1 16 Tf\n";
            $contenido .= "50 750 Td\n";
            $contenido .= "(REPORTE DE DOMICILIARIOS - SM DOMICILIOS) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 720 Td\n";
            $contenido .= "(Fecha: " . date('d/m/Y H:i:s') . ") Tj\n";
            $contenido .= "ET\n";
            
            $y = 680;
            foreach ($domiciliariosActivos as $domiciliario) {
                $contenido .= "BT\n";
                $contenido .= "/F1 12 Tf\n";
                $contenido .= "50 $y Td\n";
                $contenido .= "(" . $domiciliario['nombre'] . ": " . $domiciliario['entregas'] . " entregas - $" . number_format($domiciliario['ingresos'] ?? 0, 2) . ") Tj\n";
                $contenido .= "ET\n";
                $y -= 25;
            }
            break;
            
        case 'clientes':
            // Reporte de clientes más frecuentes
            $clientesFrecuentes = $pdo->query("
                SELECT c.nombre, COUNT(p.id_pedido) as pedidos, SUM(p.total) as total_gastado
                FROM clientes c
                LEFT JOIN pedidos p ON c.id_cliente = p.id_cliente
                WHERE c.estado = 'activo'
                GROUP BY c.id_cliente, c.nombre
                HAVING pedidos > 0
                ORDER BY pedidos DESC
                LIMIT 5
            ")->fetchAll();
            
            $contenido = "BT\n";
            $contenido .= "/F1 16 Tf\n";
            $contenido .= "50 750 Td\n";
            $contenido .= "(REPORTE DE CLIENTES - SM DOMICILIOS) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 720 Td\n";
            $contenido .= "(Fecha: " . date('d/m/Y H:i:s') . ") Tj\n";
            $contenido .= "ET\n";
            
            $y = 680;
            foreach ($clientesFrecuentes as $cliente) {
                $contenido .= "BT\n";
                $contenido .= "/F1 12 Tf\n";
                $contenido .= "50 $y Td\n";
                $contenido .= "(" . $cliente['nombre'] . ": " . $cliente['pedidos'] . " pedidos - $" . number_format($cliente['total_gastado'] ?? 0, 2) . ") Tj\n";
                $contenido .= "ET\n";
                $y -= 25;
            }
            break;
            
        case 'detalle_zonas':
            // Reporte detallado por zonas
            $pedidosPorZona = $pdo->query("
                SELECT z.nombre, COUNT(p.id_pedido) as total, SUM(p.total) as ingresos
                FROM zonas z
                LEFT JOIN pedidos p ON z.id_zona = p.id_zona
                WHERE z.estado = 'activo'
                GROUP BY z.id_zona, z.nombre
                ORDER BY total DESC
            ")->fetchAll();
            
            $contenido = "BT\n";
            $contenido .= "/F1 16 Tf\n";
            $contenido .= "50 750 Td\n";
            $contenido .= "(DETALLE POR ZONAS - SM DOMICILIOS) Tj\n";
            $contenido .= "ET\n";
            
            $contenido .= "BT\n";
            $contenido .= "/F1 12 Tf\n";
            $contenido .= "50 720 Td\n";
            $contenido .= "(Fecha: " . date('d/m/Y H:i:s') . ") Tj\n";
            $contenido .= "ET\n";
            
            $y = 680;
            foreach ($pedidosPorZona as $zona) {
                $contenido .= "BT\n";
                $contenido .= "/F1 12 Tf\n";
                $contenido .= "50 $y Td\n";
                $contenido .= "(" . $zona['nombre'] . ": " . $zona['total'] . " pedidos - $" . number_format($zona['ingresos'] ?? 0, 2) . ") Tj\n";
                $contenido .= "ET\n";
                $y -= 25;
            }
            break;
            
        default:
            throw new Exception('Tipo de reporte no válido: ' . $tipo);
    }
    
    $pdf = generarPDF("Reporte SM Domicilios", $contenido);
    echo $pdf;
    
} catch (Exception $e) {
    // En caso de error, generar un PDF simple con el error
    $contenido = "BT\n";
    $contenido .= "/F1 12 Tf\n";
    $contenido .= "50 750 Td\n";
    $contenido .= "(Error al generar reporte: " . $e->getMessage() . ") Tj\n";
    $contenido .= "ET\n";
    
    $pdf = generarPDF("Error", $contenido);
    echo $pdf;
}
?> 