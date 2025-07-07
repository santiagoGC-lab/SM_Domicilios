<?php
session_start();

// Incluir el archivo de conexión
require_once 'servicios/conexion.php';

// Habilitar depuración (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión a la base de datos
$conexion = conectarDB();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_documento = trim($_POST['numero_documento'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if (empty($numero_documento) || empty($contrasena)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        // Escapar datos para prevenir inyección SQL
        $numero_documento = $conexion->real_escape_string($numero_documento);

        // Buscar usuario por número de documento
        $query = "SELECT id_usuario, nombre_completo, contrasena, rol, estado FROM usuarios WHERE documento = '$numero_documento'";
        $resultado = $conexion->query($query);

        if ($resultado === false) {
            $error = 'Error en la consulta: ' . $conexion->error;
        } else {
            $usuario = $resultado->fetch_assoc();

            if ($usuario && $usuario['estado'] === 'activo' && password_verify($contrasena, $usuario['contrasena'])) {
                // Autenticación exitosa
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre_completo'];
                $_SESSION['rol'] = $usuario['rol'];

                // Redirigir según rol
                header('Location: vistas/dashboard.php');
                exit;
            } else {
                $error = 'Número de documento o contraseña incorrectos, o usuario inactivo.';
            }
        }
        $resultado->free();
    }
}

// Cerrar la conexión
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Acceso al Sistema</title>
    <link rel="shortcut icon" href="componentes/img/logo2.png" />
    <link rel="stylesheet" href="componentes/login-pure.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="cont-auten">
        <div class="img-auten">
            <div>
                <h2>Sistema SM</h2>
                <img src="componentes/img/logo2.png" alt="Logo SuperMercar" style="max-width: 150px;">
            </div>
        </div>
        <div class="form-auten">
            <div id="alertContainer">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" onclick="this.parentElement.remove()" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="titulo-inicio">
                <h3>Iniciar Sesión</h3>
            </div>
            <form id="formlogin" action="login.php" method="post">
                <div class="inputs-login">
                    <label for="numero_documento" class="txt-form">Número de Documento</label>
                    <input type="text" class="input-form" id="numero_documento" name="numero_documento" minlength="6"
                        maxlength="12" title="Solo se permiten números" pattern="[0-9]+" tabindex="1" required>
                </div>
                <div class="inputs-login">
                    <label for="contrasena" class="txt-form">Contraseña</label>
                    <div class="password-toggle">
                        <input type="password" class="input-form" id="contrasena" name="contrasena" maxlength="20"
                            minlength="6" pattern="[a-zA-Z0-9\-\_\@\!]"
                            title="Solo letras y números, algunos caracteres especiales son permitidos como -,_,@,!; El resto no está permitido"
                            tabindex="2" required>
                        <i class="toggle-icon fas fa-eye" onclick="togglePasswordVisibility('contrasena', this)"></i>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
                <div class="text-center mt-3">
                    <a href="vistas/recuperar-contra.html" class="txt-olvidado">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()" aria-label="Cerrar"></button>
            `;
            alertContainer.appendChild(alert);
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('formlogin');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    const numeroDocumento = document.getElementById('numero_documento').value;
                    const contrasena = document.getElementById('contrasena').value;

                    if (!numeroDocumento || !contrasena) {
                        e.preventDefault();
                        showAlert('Por favor, complete todos los campos.', 'danger');
                        return false;
                    }

                    if (!/^[0-9]{6,12}$/.test(numeroDocumento)) {
                        e.preventDefault();
                        showAlert('El número de documento debe tener entre 6 y 12 dígitos.', 'danger');
                        return false;
                    }
                });
            }
        });
    </script>
</body>

</html>