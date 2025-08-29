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

            case 'domiciliarios':
                // Encabezados
                fputcsv($output, ['DOMICILIARIOS MÁS ACTIVOS (MES) - SM DOMICILIOS']);
                fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
                fputcsv($output, []);
                fputcsv($output, ['Domiciliario', 'Entregas', 'Ingresos']);

                // Consulta para domiciliarios más activos del mes
                $domiciliarios = $db->query("
                    SELECT d.nombre, COUNT(hp.id_pedido_original) as entregas, 
                           SUM(hp.total) as ingresos
                    FROM historico_pedidos hp
                    JOIN domiciliarios d ON hp.id_domiciliario = d.id_domiciliario
                    WHERE MONTH(hp.fecha_pedido) = MONTH(CURDATE()) 
                      AND YEAR(hp.fecha_pedido) = YEAR(CURDATE())
                    GROUP BY d.id_domiciliario, d.nombre
                    ORDER BY entregas DESC, ingresos DESC
                ");

                while ($domiciliario = $domiciliarios->fetch_assoc()) {
                    fputcsv($output, [
                        $domiciliario['nombre'],
                        $domiciliario['entregas'],
                        '$' . number_format($domiciliario['ingresos'] ?? 0, 2)
                    ]);
                }
                break;

            case 'clientes':
                // Encabezados
                fputcsv($output, ['CLIENTES MÁS FRECUENTES (MES) - SM DOMICILIOS']);
                fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
                fputcsv($output, []);
                fputcsv($output, ['Cliente', 'Pedidos', 'Total Gastado']);

                // Consulta para clientes más frecuentes del mes
                $clientes = $db->query("
                    SELECT c.nombre, COUNT(hp.id_pedido_original) as pedidos, 
                           SUM(hp.total) as total_gastado
                    FROM historico_pedidos hp
                    JOIN clientes c ON hp.id_cliente = c.id_cliente
                    WHERE MONTH(hp.fecha_pedido) = MONTH(CURDATE()) 
                      AND YEAR(hp.fecha_pedido) = YEAR(CURDATE())
                    GROUP BY c.id_cliente, c.nombre
                    ORDER BY pedidos DESC, total_gastado DESC
                ");

                while ($cliente = $clientes->fetch_assoc()) {
                    fputcsv($output, [
                        $cliente['nombre'],
                        $cliente['pedidos'],
                        '$' . number_format($cliente['total_gastado'] ?? 0, 2)
                    ]);
                }
                break;

            case 'zonas':
                // Encabezados
                fputcsv($output, ['DETALLE POR ZONA (MES) - SM DOMICILIOS']);
                fputcsv($output, ['Fecha de generación: ' . date('Y-m-d H:i:s')]);
                fputcsv($output, []);
                fputcsv($output, ['Zona', 'Pedidos', 'Ingresos']);

                // Consulta para detalle por zona del mes
                $zonas = $db->query("
                    SELECT z.nombre as zona, COUNT(hp.id_pedido_original) as pedidos, 
                           SUM(hp.total) as ingresos
                    FROM historico_pedidos hp
                    JOIN zonas z ON hp.id_zona = z.id_zona
                    WHERE MONTH(hp.fecha_pedido) = MONTH(CURDATE()) 
                      AND YEAR(hp.fecha_pedido) = YEAR(CURDATE())
                    GROUP BY z.id_zona, z.nombre
                    ORDER BY pedidos DESC, ingresos DESC
                ");

                while ($zona = $zonas->fetch_assoc()) {
                    fputcsv($output, [
                        $zona['zona'],
                        $zona['pedidos'],
                        '$' . number_format($zona['ingresos'] ?? 0, 2)
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

// Función para exportar detalles de pedido a Excel (MEJORADA)
function exportarDetallesPedidoExcel($datos)
{
    try {
        $pedidoId = $datos['pedido_id'] ?? null;
        $mes = $datos['mes'] ?? date('n');
        $anio = $datos['anio'] ?? date('Y');

        if (!$pedidoId) {
            return ['error' => 'ID de pedido requerido'];
        }

        $db = ConectarDB();

        // Obtener detalles del pedido del historial
        $stmt = $db->prepare("
            SELECT hp.*, z.barrio
            FROM historico_pedidos hp
            LEFT JOIN zonas z ON hp.id_zona = z.id_zona
            WHERE hp.id_pedido_original = ? AND MONTH(hp.fecha_pedido) = ? AND YEAR(hp.fecha_pedido) = ?
        ");
        $stmt->bind_param('iii', $pedidoId, $mes, $anio);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedido = $result->fetch_assoc();

        if (!$pedido) {
            return ['error' => 'Pedido no encontrado'];
        }

        $fecha = date('Y-m-d_H-i-s');
        $filename = "Detalle_Pedido_{$pedidoId}_{$fecha}.csv";

        $output = fopen('php://temp', 'w');

        // Escribir BOM para UTF-8 (para caracteres especiales)
        fwrite($output, "\xEF\xBB\xBF");

        // ===== ENCABEZADO PRINCIPAL =====
        fputcsv($output, ['SM DOMICILIOS - DETALLE COMPLETO DEL PEDIDO']);
        fputcsv($output, ['==============================================']);
        fputcsv($output, ['Fecha de Generación:', date('d/m/Y H:i:s')]);
        fputcsv($output, ['ID del Pedido:', $pedido['id_pedido_original']]);
        fputcsv($output, ['Estado:', strtoupper($pedido['estado'] ?? 'N/A')]);
        fputcsv($output, []);
        fputcsv($output, []);

        // ===== TABLA 1: INFORMACIÓN DEL CLIENTE =====
        fputcsv($output, ['INFORMACIÓN DEL CLIENTE']);
        fputcsv($output, ['========================']);
        fputcsv($output, ['CAMPO', 'INFORMACIÓN']);
        fputcsv($output, ['Nombre Completo', $pedido['cliente_nombre'] ?? 'N/A']);
        fputcsv($output, ['Número de Teléfono', $pedido['cliente_telefono'] ?? 'N/A']);
        fputcsv($output, ['Dirección de Entrega', $pedido['cliente_direccion'] ?? 'N/A']);
        fputcsv($output, ['Barrio/Zona', $pedido['barrio'] ?? 'N/A']);
        fputcsv($output, []);
        fputcsv($output, []);

        // ===== TABLA 2: INFORMACIÓN DEL SERVICIO =====
        fputcsv($output, ['INFORMACIÓN DEL SERVICIO DE ENTREGA']);
        fputcsv($output, ['=====================================']);
        fputcsv($output, ['CAMPO', 'INFORMACIÓN']);
        fputcsv($output, ['Domiciliario Asignado', $pedido['domiciliario_nombre'] ?? 'No asignado']);
        fputcsv($output, ['Zona de Cobertura', $pedido['zona_nombre'] ?? 'N/A']);
        fputcsv($output, ['Fecha del Pedido', $pedido['fecha_pedido'] ? date('d/m/Y', strtotime($pedido['fecha_pedido'])) : 'N/A']);
        fputcsv($output, ['Estado Actual', ucfirst($pedido['estado'] ?? 'N/A')]);
        fputcsv($output, []);
        fputcsv($output, []);

        // ===== TABLA 3: ANÁLISIS DE TIEMPOS DE ENTREGA =====
        fputcsv($output, ['ANÁLISIS DE TIEMPOS DE ENTREGA']);
        fputcsv($output, ['===============================']);
        fputcsv($output, ['CONCEPTO', 'HORA/TIEMPO', 'OBSERVACIONES']);

        // Hora de Salida
        $horaSalida = $pedido['hora_salida'] ? date('H:i', strtotime($pedido['hora_salida'])) : 'N/A';
        fputcsv($output, ['Hora de Salida', $horaSalida, 'Momento en que el domiciliario inició el recorrido']);

        // Hora Estimada de Llegada
        $horaEstimada = 'N/A';
        $observacionEstimada = 'No se pudo calcular';
        if ($pedido['hora_salida'] && $pedido['tiempo_estimado']) {
            $salida = new DateTime($pedido['hora_salida']);
            $salida->add(new DateInterval('PT' . $pedido['tiempo_estimado'] . 'M'));
            $horaEstimada = $salida->format('H:i');
            $observacionEstimada = 'Basado en ' . $pedido['tiempo_estimado'] . ' minutos estimados';
        }
        fputcsv($output, ['Hora Estimada de Llegada', $horaEstimada, $observacionEstimada]);

        // Hora Real de Llegada
        $horaLlegada = $pedido['hora_llegada'] ? date('H:i', strtotime($pedido['hora_llegada'])) : 'N/A';
        $observacionLlegada = $pedido['hora_llegada'] ? 'Hora real de entrega confirmada' : 'Pedido aún no entregado';
        fputcsv($output, ['Hora Real de Llegada', $horaLlegada, $observacionLlegada]);

        // Tiempo Real de Entrega y Análisis de Cumplimiento
        $tiempoReal = 'N/A';
        $cumplimiento = 'N/A';
        $observacionCumplimiento = 'No se puede evaluar';

        if ($pedido['hora_salida'] && $pedido['hora_llegada']) {
            $salida = new DateTime($pedido['hora_salida']);
            $llegada = new DateTime($pedido['hora_llegada']);
            $tiempoRealMinutos = round(($llegada->getTimestamp() - $salida->getTimestamp()) / 60);
            $tiempoReal = $tiempoRealMinutos . ' minutos';

            // NUEVA LÓGICA: Comparar hora de llegada con hora programada
            if ($pedido['hora_estimada_entrega']) {
                // Crear DateTime para la hora programada del mismo día que la llegada
                $fechaLlegada = $llegada->format('Y-m-d');
                $horaProgramada = new DateTime($fechaLlegada . ' ' . $pedido['hora_estimada_entrega']);

                if ($llegada <= $horaProgramada) {
                    $cumplimiento = '✓ CUMPLIDO';
                    $diferencia = round(($horaProgramada->getTimestamp() - $llegada->getTimestamp()) / 60);
                    $observacionCumplimiento = "Entregado {$diferencia} minutos antes de la hora programada ({$pedido['hora_estimada_entrega']})";
                } else {
                    $retraso = round(($llegada->getTimestamp() - $horaProgramada->getTimestamp()) / 60);
                    $cumplimiento = '✗ CON RETRASO';
                    $observacionCumplimiento = "Retraso de {$retraso} minutos respecto a la hora programada ({$pedido['hora_estimada_entrega']})";
                }
            } else {
                // Fallback: usar lógica anterior si no hay hora programada
                $tiempoEstimadoNum = intval($pedido['tiempo_estimado']) ?: 30;
                if ($tiempoRealMinutos <= $tiempoEstimadoNum) {
                    $cumplimiento = '✓ CUMPLIDO (estimado)';
                    $diferencia = $tiempoEstimadoNum - $tiempoRealMinutos;
                    $observacionCumplimiento = "Entregado {$diferencia} minutos antes del tiempo estimado";
                } else {
                    $retraso = $tiempoRealMinutos - $tiempoEstimadoNum;
                    $cumplimiento = '✗ CON RETRASO (estimado)';
                    $observacionCumplimiento = "Retraso de {$retraso} minutos respecto al tiempo estimado";
                }
            }
        }

        fputcsv($output, ['Tiempo Real de Entrega', $tiempoReal, 'Tiempo total desde salida hasta entrega']);
        fputcsv($output, ['Evaluación de Cumplimiento', $cumplimiento, $observacionCumplimiento]);
        fputcsv($output, []);
        fputcsv($output, []);

        // ===== TABLA 4: DETALLES FINANCIEROS DEL PEDIDO =====
        fputcsv($output, ['DETALLES FINANCIEROS Y DE CONTENIDO']);
        fputcsv($output, ['====================================']);
        fputcsv($output, ['CONCEPTO', 'CANTIDAD/VALOR', 'DETALLES']);
        fputcsv($output, ['Cantidad de Paquetes', $pedido['cantidad_paquetes'] ?? '1', 'Número total de paquetes en el pedido']);
        fputcsv($output, ['Valor Total del Pedido', '$' . number_format($pedido['total'] ?? 0, 2), 'Monto total a cobrar al cliente']);
        fputcsv($output, ['Tiempo Estimado Inicial', ($pedido['tiempo_estimado'] ?? '30') . ' minutos', 'Tiempo estimado para la entrega']);
        fputcsv($output, []);
        fputcsv($output, []);

        // ===== RESUMEN EJECUTIVO =====
        fputcsv($output, ['RESUMEN EJECUTIVO']);
        fputcsv($output, ['=================']);

        $estadoResumen = '';
        switch (strtolower($pedido['estado'] ?? '')) {
            case 'entregado':
                $estadoResumen = 'Pedido completado exitosamente';
                break;
            case 'en_transito':
                $estadoResumen = 'Pedido en proceso de entrega';
                break;
            case 'pendiente':
                $estadoResumen = 'Pedido pendiente de asignación';
                break;
            case 'cancelado':
                $estadoResumen = 'Pedido cancelado';
                break;
            default:
                $estadoResumen = 'Estado no definido';
        }

        fputcsv($output, ['Estado del Pedido:', $estadoResumen]);
        fputcsv($output, ['Cliente:', $pedido['cliente_nombre'] ?? 'N/A']);
        fputcsv($output, ['Domiciliario:', $pedido['domiciliario_nombre'] ?? 'No asignado']);
        fputcsv($output, ['Valor Total:', '$' . number_format($pedido['total'] ?? 0, 2)]);

        if ($cumplimiento !== 'N/A') {
            fputcsv($output, ['Cumplimiento de Tiempo:', $cumplimiento]);
        }

        fputcsv($output, []);
        fputcsv($output, ['=== FIN DEL REPORTE ===']);
        fputcsv($output, ['Generado automáticamente por SM Domicilios']);
        fputcsv($output, ['Fecha: ' . date('d/m/Y H:i:s')]);

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        $db->close();

        return ['success' => true, 'csv' => $csv, 'filename' => $filename];
    } catch (Exception $e) {
        return ['error' => 'Error al exportar detalles del pedido: ' . $e->getMessage()];
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

        case 'exportar_detalle_pedido':
            $resultado = exportarDetallesPedidoExcel($_POST);
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
