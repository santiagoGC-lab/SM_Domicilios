<?php
require_once '../config.php';
require_once 'conexion.php';
require_once 'verificar_permisos.php';
session_start();

// Función para registrar un nuevo usuario
function registrarUsuario($datos) {
    try {
        $conexion = ConectarDB();
        
        $nombreCompleto = trim($datos['nombreCompleto'] ?? '');
        $numeroDocumento = trim($datos['numeroDocumento'] ?? '');
        $contrasena = $datos['contrasena'] ?? '';
        $rol = $datos['rol'] ?? '';
        
        if (!$nombreCompleto || !$numeroDocumento || !$contrasena || !$rol) {
            return ['error' => 'Faltan campos obligatorios'];
        }

        $nombreArray = explode(' ', $nombreCompleto, 2);
        $nombre = $nombreArray[0];
        $apellido = $nombreArray[1] ?? '';

        // Validar si el documento ya existe
        $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE numero_documento = ?");
        $stmt->bind_param("s", $numeroDocumento);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            $conexion->close();
            return ['error' => 'El documento ya está registrado'];
        }

        // Hashear la contraseña
        $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

        // Insertar usuario
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, numero_documento, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nombre, $apellido, $numeroDocumento, $contrasenaHash, $rol);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conexion->close();
            return ['success' => true];
        } else {
            $stmt->close();
            $conexion->close();
            return ['error' => 'Error al registrar el usuario: ' . $stmt->error];
        }

    } catch (Exception $e) {
        return ['error' => 'Error general: ' . $e->getMessage()];
    }
}

// Función para autenticar usuario
function autenticarUsuario($credenciales) {
    try {
        if (empty($credenciales['numeroDocumento']) || empty($credenciales['contrasena'])) {
            return ['error' => 'Por favor, completa todos los campos.'];
        }

        $numeroDocumento = trim($credenciales['numeroDocumento']);
        if (!preg_match('/^[0-9]{6,12}$/', $numeroDocumento)) {
            return ['error' => 'El número de documento debe tener entre 6 y 12 dígitos.'];
        }
        $contrasena = trim($credenciales['contrasena']);

        $conexion = ConectarDB();

        $stmt = $conexion->prepare("SELECT id_usuario, nombre, apellido, rol, contrasena FROM usuarios WHERE numero_documento = ? AND estado = 'activo'");
        if (!$stmt) {
            return ['error' => 'Error al preparar la consulta: ' . $conexion->error];
        }

        $stmt->bind_param("s", $numeroDocumento);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $conexion->close();
            return ['error' => 'Usuario no encontrado o inactivo.'];
        }

        $usuario = $result->fetch_assoc();

        if (!password_verify($contrasena, $usuario['contrasena'])) {
            $stmt->close();
            $conexion->close();
            return ['error' => 'Contraseña incorrecta.'];
        }

        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
        $_SESSION['rol'] = $usuario['rol'];

        session_regenerate_id(true);

        $stmt->close();
        $conexion->close();

        // Redirigir según el rol
        if ($usuario['rol'] === 'admin') {
            return ['success' => true, 'redirect' => '../vistas/dashboard.php'];
        } else {
            // Los gestores y cajeras van directamente a pedidos
            return ['success' => true, 'redirect' => '../vistas/pedidos.php'];
        }

    } catch (Exception $e) {
        return ['error' => 'Error: ' . $e->getMessage()];
    }
}

// Función para obtener usuario por ID
function obtenerUsuarioPorId($id) {
    try {
        if (!isset($id) || !is_numeric($id)) {
            return ['error' => 'ID de usuario inválido'];
        }

        $conexion = ConectarDB();
        
        $query = "SELECT id_usuario, nombre, apellido, numero_documento, rol, estado, fecha_creacion 
                  FROM usuarios WHERE id_usuario = ?";
        
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        
        if ($usuario = mysqli_fetch_assoc($resultado)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conexion);
            return ['success' => true, 'usuario' => $usuario];
        } else {
            mysqli_stmt_close($stmt);
            mysqli_close($conexion);
            return ['error' => 'Usuario no encontrado'];
        }
        
    } catch (Exception $e) {
        return ['error' => 'Error al obtener el usuario: ' . $e->getMessage()];
    }
}
// Función para cerrar sesión
function cerrarSesion() {
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

    return ['success' => true, 'redirect' => '../vistas/login.html?success=' . urlencode("Sesión cerrada exitosamente. ¡Hasta pronto!")];
}

// Función para verificar permisos
function verificarPermisos($usuario, $permiso) {
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol'])) {
        return ['error' => 'Usuario no autenticado'];
    }

    // Definir permisos por rol
    $permisos = [
        'admin' => [
            'dashboard' => true,
            'pedidos' => true,
            'clientes' => true,
            'domiciliarios' => true,
            'zonas' => true,
            'reportes' => true,
            'crearUsu' => true,
            'tabla_usuarios' => true
        ],
        'org_domicilios' => [
            'dashboard' => false,
            'pedidos' => true,
            'clientes' => true,
            'domiciliarios' => true,
            'zonas' => true,
            'reportes' => true,
            'crearUsu' => false,
            'tabla_usuarios' => false
        ],
        'cajera' => [
            'dashboard' => false,
            'pedidos' => true,
            'clientes' => true,
            'domiciliarios' => false,
            'zonas' => false,
            'reportes' => true,
            'crearUsu' => false,
            'tabla_usuarios' => false
        ]
    ];

    $rol = $_SESSION['rol'];
    
    if (!isset($permisos[$rol])) {
        return ['error' => 'Rol no válido'];
    }
    
    $tienePermiso = isset($permisos[$rol][$permiso]) && $permisos[$rol][$permiso];
    return ['success' => true, 'tiene_permiso' => $tienePermiso];
}

// Función para actualizar usuario
function actualizarUsuario($datos) {
    try {
        $conexion = ConectarDB();
        $id = $datos['id'] ?? null;
        $nombre = trim($datos['nombre'] ?? '');
        $apellido = trim($datos['apellido'] ?? '');
        $numeroDocumento = trim($datos['numeroDocumento'] ?? '');
        $rol = $datos['rol'] ?? '';
        $estado = $datos['estado'] ?? '';
        if (!$id || !$nombre || !$apellido || !$numeroDocumento || !$rol || !$estado) {
            return ['error' => 'Faltan campos obligatorios'];
        }
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, apellido=?, numero_documento=?, rol=?, estado=? WHERE id_usuario=?");
        $stmt->bind_param("sssssi", $nombre, $apellido, $numeroDocumento, $rol, $estado, $id);
        $success = $stmt->execute();
        $stmt->close();
        $conexion->close();
        return $success ? ['success' => true] : ['error' => 'No se pudo actualizar el usuario'];
    } catch (Exception $e) {
        return ['error' => 'Error al actualizar: ' . $e->getMessage()];
    }
}

// Función para eliminar usuario
function eliminarUsuario($id) {
    try {
        $conexion = ConectarDB();
        if (!$id || !is_numeric($id)) {
            return ['error' => 'ID inválido'];
        }
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        $conexion->close();
        return $success ? ['success' => true] : ['error' => 'No se pudo eliminar el usuario'];
    } catch (Exception $e) {
        return ['error' => 'Error al eliminar: ' . $e->getMessage()];
    }
}

// Función para cambiar el estado de usuario (activo/inactivo)
function cambiarEstadoUsuario($id, $nuevoEstado) {
    try {
        $conexion = ConectarDB();
        if (!$id || !is_numeric($id) || !in_array($nuevoEstado, ['activo', 'inactivo'])) {
            return ['error' => 'Datos inválidos'];
        }
        $stmt = $conexion->prepare("UPDATE usuarios SET estado=? WHERE id_usuario=?");
        $stmt->bind_param("si", $nuevoEstado, $id);
        $success = $stmt->execute();
        $stmt->close();
        $conexion->close();
        return $success ? ['success' => true] : ['error' => 'No se pudo cambiar el estado'];
    } catch (Exception $e) {
        return ['error' => 'Error al cambiar estado: ' . $e->getMessage()];
    }
}

// Endpoint para manejar las peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'registrar':
            $resultado = registrarUsuario($_POST);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        case 'autenticar':
            $resultado = autenticarUsuario($_POST);
            // Detectar si es petición AJAX
            $isAjax = (
                (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
            );
            if (!$isAjax) {
                if (isset($resultado['success']) && $resultado['success']) {
                    header('Location: ../vistas/dashboard.php');
                    exit;
                } else {
                    $error = isset($resultado['error']) ? urlencode($resultado['error']) : urlencode('Error desconocido');
                    header('Location: ../vistas/login.html?error=' . $error);
                    exit;
                }
            }
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        case 'obtener_por_id':
            verificarAcceso('tabla_usuarios');
            $resultado = obtenerUsuarioPorId($_POST['id']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        case 'cerrar_sesion':
            $resultado = cerrarSesion();
            echo json_encode($resultado);
            break;
            
        case 'verificar_permisos':
            $resultado = verificarPermisos($_POST['usuario'], $_POST['permiso']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        case 'actualizar':
            $resultado = actualizarUsuario($_POST);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
        case 'eliminar':
            $resultado = eliminarUsuario($_POST['id']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
        case 'cambiar_estado':
            $resultado = cambiarEstadoUsuario($_POST['id'], $_POST['estado']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Para compatibilidad con el código existente
    $accion = $_GET['accion'] ?? '';
    
    if ($accion === 'obtener_por_id') {
        verificarAcceso('tabla_usuarios');
        $resultado = obtenerUsuarioPorId($_GET['id']);
        if (isset($resultado['error'])) {
            http_response_code(400);
        }
        echo json_encode($resultado);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?> 