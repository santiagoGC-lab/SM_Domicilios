<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerar ID de sesión para mayor seguridad
session_regenerate_id(true);

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Eliminar la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Eliminar otras cookies que puedan contener información sensible
if (isset($_COOKIE['PHPSESSID'])) {
    setcookie('PHPSESSID', '', time()-3600, '/');
}

// Redirigir al login con mensaje de confirmación
header("Location: ../login.html?success=" . urlencode("Sesión cerrada exitosamente. ¡Hasta pronto!"));
exit();
?>