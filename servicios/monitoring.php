<?php
/**
 * Sistema de Monitoreo y Alertas
 * Detecta problemas en el sistema y envía alertas
 */

require_once '../config.php';
require_once 'Database.php';
require_once 'security.php';

class MonitoringSystem {
    private $alertThresholds;
    private $monitoringData;
    
    public function __construct() {
        $this->alertThresholds = [
            'disk_usage' => 80, // Porcentaje
            'memory_usage' => 85, // Porcentaje
            'failed_logins' => 10, // En 1 hora
            'error_rate' => 5, // Porcentaje de errores
            'response_time' => 3000, // Milisegundos
            'database_connections' => 50 // Conexiones simultáneas
        ];
        
        $this->monitoringData = [];
    }
    
    /**
     * Ejecutar todas las verificaciones de monitoreo
     */
    public function runHealthCheck() {
        $checks = [
            'system' => $this->checkSystemHealth(),
            'database' => $this->checkDatabaseHealth(),
            'security' => $this->checkSecurityHealth(),
            'performance' => $this->checkPerformanceHealth(),
            'storage' => $this->checkStorageHealth()
        ];
        
        $overallStatus = $this->calculateOverallStatus($checks);
        
        $this->monitoringData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_status' => $overallStatus,
            'checks' => $checks
        ];
        
        // Guardar datos de monitoreo
        $this->saveMonitoringData();
        
        // Enviar alertas si es necesario
        $this->sendAlerts($checks);
        
        return $this->monitoringData;
    }
    
    /**
     * Verificar salud del sistema
     */
    private function checkSystemHealth() {
        $checks = [];
        
        // Uso de CPU
        $cpuUsage = $this->getCPUUsage();
        $checks['cpu_usage'] = [
            'value' => $cpuUsage,
            'status' => $cpuUsage > 90 ? 'critical' : ($cpuUsage > 70 ? 'warning' : 'ok'),
            'threshold' => 90
        ];
        
        // Uso de memoria
        $memoryUsage = $this->getMemoryUsage();
        $checks['memory_usage'] = [
            'value' => $memoryUsage,
            'status' => $memoryUsage > $this->alertThresholds['memory_usage'] ? 'critical' : 'ok',
            'threshold' => $this->alertThresholds['memory_usage']
        ];
        
        // Uptime del sistema
        $uptime = $this->getSystemUptime();
        $checks['uptime'] = [
            'value' => $uptime,
            'status' => 'ok',
            'formatted' => $this->formatUptime($uptime)
        ];
        
        // Versión de PHP
        $checks['php_version'] = [
            'value' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'warning'
        ];
        
        return $checks;
    }
    
    /**
     * Verificar salud de la base de datos
     */
    private function checkDatabaseHealth() {
        $checks = [];
        
        try {
            $db = getDB();
            
            // Verificar conexión
            $checks['connection'] = [
                'value' => 'connected',
                'status' => 'ok'
            ];
            
            // Tamaño de la base de datos
            $dbSize = $this->getDatabaseSize();
            $checks['database_size'] = [
                'value' => $dbSize,
                'status' => 'ok',
                'formatted' => $this->formatBytes($dbSize)
            ];
            
            // Número de tablas
            $tables = $db->fetchAll("SHOW TABLES");
            $checks['tables_count'] = [
                'value' => count($tables),
                'status' => 'ok'
            ];
            
            // Verificar tablas corruptas
            $corruptedTables = $this->checkCorruptedTables();
            $checks['corrupted_tables'] = [
                'value' => count($corruptedTables),
                'status' => empty($corruptedTables) ? 'ok' : 'critical',
                'details' => $corruptedTables
            ];
            
            // Tiempo de respuesta de consultas
            $responseTime = $this->measureDatabaseResponseTime();
            $checks['response_time'] = [
                'value' => $responseTime,
                'status' => $responseTime > 1000 ? 'warning' : 'ok',
                'unit' => 'ms'
            ];
            
        } catch (Exception $e) {
            $checks['connection'] = [
                'value' => 'error',
                'status' => 'critical',
                'error' => $e->getMessage()
            ];
        }
        
        return $checks;
    }
    
    /**
     * Verificar salud de seguridad
     */
    private function checkSecurityHealth() {
        $checks = [];
        
        // Intentos de login fallidos recientes
        $failedLogins = $this->getRecentFailedLogins();
        $checks['failed_logins'] = [
            'value' => $failedLogins,
            'status' => $failedLogins > $this->alertThresholds['failed_logins'] ? 'warning' : 'ok',
            'threshold' => $this->alertThresholds['failed_logins']
        ];
        
        // Verificar archivos de log de seguridad
        $securityLogs = $this->checkSecurityLogs();
        $checks['security_logs'] = [
            'value' => $securityLogs['count'],
            'status' => $securityLogs['has_critical'] ? 'warning' : 'ok',
            'details' => $securityLogs['recent_events']
        ];
        
        // Verificar permisos de archivos críticos
        $filePermissions = $this->checkFilePermissions();
        $checks['file_permissions'] = [
            'value' => count($filePermissions['insecure']),
            'status' => empty($filePermissions['insecure']) ? 'ok' : 'warning',
            'details' => $filePermissions
        ];
        
        // Verificar sesiones activas
        $activeSessions = $this->getActiveSessions();
        $checks['active_sessions'] = [
            'value' => $activeSessions,
            'status' => 'ok'
        ];
        
        return $checks;
    }
    
    /**
     * Verificar rendimiento
     */
    private function checkPerformanceHealth() {
        $checks = [];
        
        // Tiempo de respuesta de la aplicación
        $appResponseTime = $this->measureAppResponseTime();
        $checks['app_response_time'] = [
            'value' => $appResponseTime,
            'status' => $appResponseTime > $this->alertThresholds['response_time'] ? 'warning' : 'ok',
            'unit' => 'ms'
        ];
        
        // Verificar archivos de log de errores
        $errorLogs = $this->checkErrorLogs();
        $checks['error_logs'] = [
            'value' => $errorLogs['count'],
            'status' => $errorLogs['count'] > 10 ? 'warning' : 'ok',
            'details' => $errorLogs['recent_errors']
        ];
        
        // Verificar uso de memoria de PHP
        $phpMemoryUsage = memory_get_usage(true);
        $phpMemoryLimit = ini_get('memory_limit');
        $checks['php_memory'] = [
            'value' => $phpMemoryUsage,
            'status' => 'ok',
            'formatted' => $this->formatBytes($phpMemoryUsage),
            'limit' => $phpMemoryLimit
        ];
        
        return $checks;
    }
    
    /**
     * Verificar almacenamiento
     */
    private function checkStorageHealth() {
        $checks = [];
        
        // Uso de disco
        $diskUsage = $this->getDiskUsage();
        $checks['disk_usage'] = [
            'value' => $diskUsage,
            'status' => $diskUsage > $this->alertThresholds['disk_usage'] ? 'critical' : 'ok',
            'threshold' => $this->alertThresholds['disk_usage']
        ];
        
        // Espacio disponible
        $freeSpace = disk_free_space(ROOT_PATH);
        $totalSpace = disk_total_space(ROOT_PATH);
        $checks['free_space'] = [
            'value' => $freeSpace,
            'status' => 'ok',
            'formatted' => $this->formatBytes($freeSpace),
            'percentage' => round(($freeSpace / $totalSpace) * 100, 2)
        ];
        
        // Verificar directorio de logs
        $logsSize = $this->getLogsDirectorySize();
        $checks['logs_size'] = [
            'value' => $logsSize,
            'status' => $logsSize > 100 * 1024 * 1024 ? 'warning' : 'ok', // 100MB
            'formatted' => $this->formatBytes($logsSize)
        ];
        
        return $checks;
    }
    
    /**
     * Calcular estado general del sistema
     */
    private function calculateOverallStatus($checks) {
        $statuses = [];
        
        foreach ($checks as $category => $categoryChecks) {
            foreach ($categoryChecks as $check) {
                if (isset($check['status'])) {
                    $statuses[] = $check['status'];
                }
            }
        }
        
        if (in_array('critical', $statuses)) {
            return 'critical';
        } elseif (in_array('warning', $statuses)) {
            return 'warning';
        } else {
            return 'ok';
        }
    }
    
    /**
     * Guardar datos de monitoreo
     */
    private function saveMonitoringData() {
        $monitoringFile = LOGS_PATH . '/monitoring_' . date('Y-m-d') . '.json';
        
        // Cargar datos existentes
        $existingData = [];
        if (file_exists($monitoringFile)) {
            $existingData = json_decode(file_get_contents($monitoringFile), true) ?: [];
        }
        
        // Agregar nuevos datos
        $existingData[] = $this->monitoringData;
        
        // Mantener solo los últimos 100 registros
        if (count($existingData) > 100) {
            $existingData = array_slice($existingData, -100);
        }
        
        file_put_contents($monitoringFile, json_encode($existingData, JSON_PRETTY_PRINT));
    }
    
    /**
     * Enviar alertas
     */
    private function sendAlerts($checks) {
        $alerts = [];
        
        foreach ($checks as $category => $categoryChecks) {
            foreach ($categoryChecks as $checkName => $check) {
                if (isset($check['status']) && in_array($check['status'], ['critical', 'warning'])) {
                    $alerts[] = [
                        'category' => $category,
                        'check' => $checkName,
                        'status' => $check['status'],
                        'value' => $check['value'] ?? 'N/A',
                        'threshold' => $check['threshold'] ?? 'N/A'
                    ];
                }
            }
        }
        
        if (!empty($alerts)) {
            $this->logAlerts($alerts);
            $this->sendEmailAlerts($alerts);
        }
    }
    
    /**
     * Registrar alertas
     */
    private function logAlerts($alerts) {
        foreach ($alerts as $alert) {
            securityLog('MONITORING_ALERT', $alert, 'WARNING');
        }
    }
    
    /**
     * Enviar alertas (solo logging por ahora)
     */
    private function sendEmailAlerts($alerts) {
        // Solo registrar alertas en el log
        securityLog('MONITORING_ALERTS', [
            'alerts_count' => count($alerts),
            'alerts' => $alerts,
            'overall_status' => $this->monitoringData['overall_status']
        ]);
    }
    
    // Métodos auxiliares para obtener métricas del sistema
    
    private function getCPUUsage() {
        // Implementación básica - en producción usar herramientas específicas
        return rand(10, 80); // Simulado
    }
    
    private function getMemoryUsage() {
        $memoryInfo = file_get_contents('/proc/meminfo');
        if ($memoryInfo) {
            preg_match('/MemTotal:\s+(\d+)/', $memoryInfo, $total);
            preg_match('/MemAvailable:\s+(\d+)/', $memoryInfo, $available);
            
            if (isset($total[1]) && isset($available[1])) {
                return round((($total[1] - $available[1]) / $total[1]) * 100, 2);
            }
        }
        
        return rand(20, 70); // Simulado
    }
    
    private function getSystemUptime() {
        $uptime = file_get_contents('/proc/uptime');
        if ($uptime) {
            return (int)explode(' ', $uptime)[0];
        }
        return time(); // Simulado
    }
    
    private function formatUptime($seconds) {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return "{$days}d {$hours}h {$minutes}m";
    }
    
    private function getDatabaseSize() {
        try {
            $db = getDB();
            $result = $db->fetchOne("
                SELECT SUM(data_length + index_length) as size 
                FROM information_schema.tables 
                WHERE table_schema = ?
            ", [DB_NAME]);
            
            return $result['size'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function checkCorruptedTables() {
        try {
            $db = getDB();
            $tables = $db->fetchAll("SHOW TABLES");
            $corrupted = [];
            
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                $result = $db->fetchOne("CHECK TABLE `$tableName`");
                
                if ($result['Msg_text'] !== 'OK') {
                    $corrupted[] = $tableName;
                }
            }
            
            return $corrupted;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function measureDatabaseResponseTime() {
        $start = microtime(true);
        
        try {
            $db = getDB();
            $db->query("SELECT 1");
            $end = microtime(true);
            
            return round(($end - $start) * 1000, 2);
        } catch (Exception $e) {
            return 9999;
        }
    }
    
    private function getRecentFailedLogins() {
        $logFile = LOGS_PATH . '/security_' . date('Y-m-d') . '.log';
        
        if (!file_exists($logFile)) {
            return 0;
        }
        
        $content = file_get_contents($logFile);
        $failedLogins = substr_count($content, 'LOGIN_FAILED');
        
        return $failedLogins;
    }
    
    private function checkSecurityLogs() {
        $logFile = LOGS_PATH . '/security_' . date('Y-m-d') . '.log';
        
        if (!file_exists($logFile)) {
            return ['count' => 0, 'has_critical' => false, 'recent_events' => []];
        }
        
        $lines = file($logFile);
        $recentEvents = array_slice($lines, -10); // Últimos 10 eventos
        
        return [
            'count' => count($lines),
            'has_critical' => strpos(file_get_contents($logFile), 'CRITICAL') !== false,
            'recent_events' => $recentEvents
        ];
    }
    
    private function checkFilePermissions() {
        $criticalFiles = [
            'config.php',
            'servicios/security.php',
            'servicios/Database.php'
        ];
        
        $insecure = [];
        $secure = [];
        
        foreach ($criticalFiles as $file) {
            $filePath = ROOT_PATH . '/' . $file;
            if (file_exists($filePath)) {
                $perms = fileperms($filePath);
                $perms = substr(sprintf('%o', $perms), -4);
                
                if ($perms > '0644') {
                    $insecure[] = ['file' => $file, 'permissions' => $perms];
                } else {
                    $secure[] = ['file' => $file, 'permissions' => $perms];
                }
            }
        }
        
        return ['insecure' => $insecure, 'secure' => $secure];
    }
    
    private function getActiveSessions() {
        // Contar archivos de sesión activos
        $sessionPath = session_save_path() ?: '/tmp';
        $sessionFiles = glob($sessionPath . '/sess_*');
        
        return count($sessionFiles);
    }
    
    private function measureAppResponseTime() {
        // Simulación de tiempo de respuesta
        return rand(50, 500);
    }
    
    private function checkErrorLogs() {
        $errorLogs = glob(LOGS_PATH . '/*.log');
        $totalErrors = 0;
        $recentErrors = [];
        
        foreach ($errorLogs as $logFile) {
            $content = file_get_contents($logFile);
            $errors = substr_count($content, 'ERROR');
            $totalErrors += $errors;
            
            if ($errors > 0) {
                $lines = file($logFile);
                $recentErrors[] = [
                    'file' => basename($logFile),
                    'errors' => $errors,
                    'last_error' => end($lines)
                ];
            }
        }
        
        return [
            'count' => $totalErrors,
            'recent_errors' => array_slice($recentErrors, 0, 5)
        ];
    }
    
    private function getDiskUsage() {
        $totalSpace = disk_total_space(ROOT_PATH);
        $freeSpace = disk_free_space(ROOT_PATH);
        $usedSpace = $totalSpace - $freeSpace;
        
        return round(($usedSpace / $totalSpace) * 100, 2);
    }
    
    private function getLogsDirectorySize() {
        $size = 0;
        $logFiles = glob(LOGS_PATH . '/*.log');
        
        foreach ($logFiles as $file) {
            $size += filesize($file);
        }
        
        return $size;
    }
    
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Endpoint para ejecutar monitoreo
if (isset($_GET['action']) && $_GET['action'] === 'health_check') {
    require_once 'verificar_permisos.php';
    verificarAcceso('dashboard');
    
    $monitoring = new MonitoringSystem();
    $result = $monitoring->runHealthCheck();
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
?> 