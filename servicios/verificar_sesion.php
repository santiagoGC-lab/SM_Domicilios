<?php
function verificarSesion() {
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../login.html?error=' . urlencode('Debe iniciar sesión para acceder'));
        exit();
    }
}
?>