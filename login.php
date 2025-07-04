<?php
session_start();

// Conexión a la base de datos
$host = 'localhost';
$dbname = 'sm_domicilios';
$username = 'root'; // Cambia según tu configuración
$password = 'root'; // Cambia según tu configuración

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesar el formulario
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_documento = trim($_POST['numero_documento'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $recordarme = isset($_POST['recordarme']);

    if (empty($numero_documento) || empty($contrasena)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        // Buscar usuario por número de documento
        $stmt = $pdo->prepare("SELECT id, nombre, contrasena, rol, estado FROM usuarios WHERE numero_documento = ?");
        $stmt->execute([$numero_documento]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $usuario['estado'] === 'activo' && password_verify($contrasena, $usuario['contrasena'])) {
            // Autenticación exitosa
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            // Manejo de "Recordarme"
            if ($recordarme) {
                $token = bin2hex(random_bytes(32));
                $stmt = $pdo->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $usuario['id']]);
                setcookie('remember_token', $token, time() + (30 * 24 * 3600), '/'); // 30 días
            }

            // Redirigir según rol
            if ($usuario['rol'] === 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: clientes.php');
            }
            exit;
        } else {
            $error = 'Número de documento o contraseña incorrectos, o usuario inactivo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Metadatos básicos -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Acceso al Sistema</title>
    <link rel="shortcut icon" href="componentes/img/logo2.png" />
    <!-- Hoja de estilos principal -->
    <link rel="stylesheet" href="componentes/login-pure.css">

    <!-- Iconos de Font Awesome (única librería externa que mantenemos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Contenedor principal de autenticación -->
    <div class="cont-auten">
        <!-- Sección de imagen/logo -->
        <div class="img-auten">
            <div>
                <h2>Sistema SM</h2>
                <img src="componentes/img/logo2.png" alt="Logo SuperMercar" style="max-width: 150px;">
            </div>
        </div>

        <!-- Sección de formulario -->
        <div class="form-auten">
            <!-- Contenedor para mensajes de alerta -->
            <div id="alertContainer"></div>

            <!-- Título del formulario -->
            <div class="titulo-inicio">
                <h3><i class="titulo-inicio"></i>Iniciar Sesión</h3>
            </div>

            <!-- Formulario de Login -->
            <form id="formlogin" action="servicios/autenticador.php" method="post">


                <!-- Campo de número de documento -->
                <div class="inputs-login">
                    <label for="numeroDocumento" class="txt-form">Número de Documento</label>
                    <input type="text" class="input-form" id="numeroDocumento" name="numeroDocumento" minlength="6"
                        maxlength="12" title="Solo se permiten números" pattern="[0-9]+" tabindex="1" required>
                </div>

                <!-- Campo de contraseña -->
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

                <!-- Opción recordar usuario -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="recuerdame" name="recuerdame">
                    <label class="form-check-label" for="recuerdame">Recordarme</label>
                </div>

                <!-- Botón de envío -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>

                <!-- Enlace para recuperar contraseña -->
                <div class="text-center mt-3">
                    <a href="vistas/recuperar-contra.html" class="txt-olvidado">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Función para mostrar/ocultar contraseña
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

        // Función para mostrar mensajes de alerta
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()" aria-label="Cerrar"></button>
            `;
            alertContainer.appendChild(alert);

            // Auto-cerrar después de 5 segundos
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 5000);
        }

        // Inicialización cuando el DOM está cargado
        document.addEventListener('DOMContentLoaded', function () {
            // Validación del formulario de login
            const loginForm = document.getElementById('formlogin');

            if (loginForm) {
                loginForm.addEventListener('submit', function (e) {
                    // Validaciones básicas
                    const numeroDocumento = document.getElementById('numeroDocumento').value;
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

            // Verificar si hay mensajes de error o éxito en la URL
            const urlParams = new URLSearchParams(window.location.search);
            const errorMsg = urlParams.get('error');
            const successMsg = urlParams.get('success');

            if (errorMsg) {
                showAlert(decodeURIComponent(errorMsg), 'danger');
            }

            if (successMsg) {
                showAlert(decodeURIComponent(successMsg), 'success');
            }
        });
    </script>
</body>

</html>