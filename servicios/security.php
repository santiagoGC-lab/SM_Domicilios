<?php
/**
 * Sistema de Seguridad Centralizado
 * Maneja validación, sanitización y headers de seguridad (sin CSRF)
 */

require_once '../config.php';

// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers de seguridad
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Solo en producción
    if (!DEVELOPMENT_MODE) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Sanitizar entrada
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Sanitizar salida
function safeOutput($data) {
    if (is_array($data)) {
        return array_map('safeOutput', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validar documento (6-12 dígitos)
function validateDocument($document) {
    return preg_match('/^[0-9]{6,12}$/', $document);
}

// Validar teléfono
function validatePhone($phone) {
    return preg_match('/^[0-9]{7,15}$/', $phone);
}

// Rate limiting para login
function checkLoginAttempts($identifier) {
    $attempts = $_SESSION['login_attempts'][$identifier] ?? 0;
    $lastAttempt = $_SESSION['last_attempt'][$identifier] ?? 0;
    
    // Si han pasado más de 15 minutos, resetear intentos
    if (time() - $lastAttempt > 900) {
        $_SESSION['login_attempts'][$identifier] = 0;
        return true;
    }
    
    // Máximo 5 intentos en 15 minutos
    if ($attempts >= 5) {
        return false;
    }
    
    return true;
}

// Registrar intento de login
function recordLoginAttempt($identifier, $success = false) {
    if ($success) {
        // Resetear intentos si el login fue exitoso
        unset($_SESSION['login_attempts'][$identifier]);
        unset($_SESSION['last_attempt'][$identifier]);
    } else {
        // Incrementar contador de intentos
        $_SESSION['login_attempts'][$identifier] = ($_SESSION['login_attempts'][$identifier] ?? 0) + 1;
        $_SESSION['last_attempt'][$identifier] = time();
    }
}

// Validar contraseña
function validatePassword($password) {
    // Mínimo 8 caracteres, al menos una letra y un número
    return strlen($password) >= 8 && 
           preg_match('/[a-zA-Z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

// Generar contraseña segura
function generateSecurePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $password;
}

// Log de seguridad
function securityLog($action, $details = [], $level = 'INFO') {
    $logFile = LOGS_PATH . '/security_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = $_SESSION['id_usuario'] ?? 'anonymous';
    
    $logEntry = sprintf(
        "[%s] [%s] [IP: %s] [User: %s] %s %s\n",
        $timestamp,
        $level,
        $ip,
        $user,
        $action,
        json_encode($details)
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Verificar si la sesión está activa
function isSessionActive() {
    return isset($_SESSION['id_usuario']) && 
           isset($_SESSION['rol']) && 
           !empty($_SESSION['id_usuario']);
}

// Regenerar ID de sesión periódicamente
function regenerateSessionIfNeeded() {
    $regenerationTime = 300; // 5 minutos
    
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
        return;
    }
    
    if (time() - $_SESSION['last_regeneration'] > $regenerationTime) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Inicializar seguridad
function initSecurity() {
    setSecurityHeaders();
    regenerateSessionIfNeeded();
    
    // Log de acceso
    if (isSessionActive()) {
        securityLog('PAGE_ACCESS', [
            'page' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
        ]);
    }
}
?> 