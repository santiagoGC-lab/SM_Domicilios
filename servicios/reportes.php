<?php
require_once '../config.php';
require_once 'conexion.php';
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Función para generar reporte en PDF
function generarReportePDF($datos)
{
    try {
        $tipo = $datos['tipo'] ?? 'general';
        $db = ConectarDB();

        // Función simple para generar PDF básico
        function generarPDF($titulo, $contenido)
        {
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

        switch ($tipo) {
            case 'general':
                // Reporte general
                $totalPedidos = $db->query("SELECT COUNT(*) FROM pedidos")->fetch_row()[0];
                $pedidosHoy = $db->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()")->fetch_row()[0];
                $ingresosHoy = $db->query("SELECT SUM(total) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE() AND estado = 'entregado'")->fetch_row()[0] ?? 0;
                $pedidosPendientes = $db->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetch_row()[0];

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

            default:
                return ['error' => 'Tipo de reporte no válido'];
        }

        $db->close();
        return ['success' => true, 'pdf' => generarPDF('Reporte', $contenido)];
    } catch (Exception $e) {
        return ['error' => 'Error al generar PDF: ' . $e->getMessage()];
    }
}

// Función para exportar reporte
function exportarReporte($datos)
{
    try {
        $tipo = $datos['tipo'] ?? 'general';
        $db = ConectarDB();

        $fecha = date('Y-m-d_H-i-s');
        $filename = "reporte_{$tipo}_{$fecha}.csv";

        $output = fopen('php://temp', 'w');

        switch ($tipo) {
            case 'general':
                // Encabezados
                fputcsv($output, ['REPORTE GENERAL - SM DOMICILIOS']);
                fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
                fputcsv($output, []);

                // Estadísticas generales
                $totalPedidos = $db->query("SELECT COUNT(*) FROM pedidos")->fetch_row()[0];
                $pedidosHoy = $db->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()")->fetch_row()[0];
                $ingresosHoy = $db->query("SELECT SUM(total) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE() AND estado = 'entregado'")->fetch_row()[0] ?? 0;
                $pedidosPendientes = $db->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetch_row()[0];

                fputcsv($output, ['ESTADÍSTICAS GENERALES']);
                fputcsv($output, ['Total Pedidos', $totalPedidos]);
                fputcsv($output, ['Pedidos Hoy', $pedidosHoy]);
                fputcsv($output, ['Ingresos Hoy', '$' . number_format($ingresosHoy, 2)]);
                fputcsv($output, ['Pedidos Pendientes', $pedidosPendientes]);
                fputcsv($output, []);

                // Pedidos por estado
                fputcsv($output, ['PEDIDOS POR ESTADO']);
                fputcsv($output, ['Estado', 'Cantidad']);

                $estados = $db->query("SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado ORDER BY total DESC");
                while ($estado = $estados->fetch_assoc()) {
                    fputcsv($output, [$estado['estado'], $estado['total']]);
                }
                break;

            case 'detallado':
                // Encabezados
                fputcsv($output, ['REPORTE DETALLADO - SM DOMICILIOS']);
                fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
                fputcsv($output, []);

                // Todos los pedidos
                fputcsv($output, ['TODOS LOS PEDIDOS']);
                fputcsv($output, ['ID', 'Cliente', 'Domiciliario', 'Zona', 'Estado', 'Fecha', 'Cantidad Paquetes', 'Total']);

                $pedidos = $db->query("
                    SELECT p.id_pedido, c.nombre as cliente, d.nombre as domiciliario, z.nombre as zona, 
                           p.estado, p.fecha_pedido, p.cantidad_paquetes, p.total
                    FROM pedidos p
                    LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                    LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
                    LEFT JOIN zonas z ON p.id_zona = z.id_zona
                    ORDER BY p.fecha_pedido DESC
                ");

                while ($pedido = $pedidos->fetch_assoc()) {
                    fputcsv($output, [
                        $pedido['id_pedido'],
                        $pedido['cliente'],
                        $pedido['domiciliario'] ?: 'No asignado',
                        $pedido['zona'],
                        $pedido['estado'],
                        $pedido['fecha_pedido'],
                        $pedido['cantidad_paquetes'],
                        '$' . number_format($pedido['total'], 2)
                    ]);
                }
                break;

            default:
                return ['error' => 'Tipo de reporte no válido'];
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        $db->close();

        return ['success' => true, 'csv' => $csv, 'filename' => $filename];
    } catch (Exception $e) {
        return ['error' => 'Error al exportar reporte: ' . $e->getMessage()];
    }
}

// Endpoint para manejar las peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'generar_pdf':
            $resultado = generarReportePDF($_POST);
            if (isset($resultado['error'])) {
                http_response_code(400);
                echo json_encode($resultado);
            } else {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="reporte_' . date('Y-m-d_H-i-s') . '.pdf"');
                echo $resultado['pdf'];
            }
            break;

        case 'exportar':
            $resultado = exportarReporte($_POST);
            if (isset($resultado['error'])) {
                http_response_code(400);
                echo json_encode($resultado);
            } else {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $resultado['filename'] . '"');
                echo $resultado['csv'];
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Para compatibilidad con el código existente
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'generar_pdf') {
        $resultado = generarReportePDF($_GET);
        if (isset($resultado['error'])) {
            http_response_code(400);
            echo json_encode($resultado);
        } else {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="reporte_' . date('Y-m-d_H-i-s') . '.pdf"');
            echo $resultado['pdf'];
        }
    } elseif ($accion === 'exportar') {
        $resultado = exportarReporte($_GET);
        if (isset($resultado['error'])) {
            http_response_code(400);
            echo json_encode($resultado);
        } else {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $resultado['filename'] . '"');
            echo $resultado['csv'];
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
