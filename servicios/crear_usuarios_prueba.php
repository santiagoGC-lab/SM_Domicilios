<?php
require_once 'conexion.php';

try {
    $conexion = ConectarDB();
    
    // Usuarios de prueba
    $usuarios = [
        [
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'numero_documento' => '123456789',
            'contrasena' => 'admin123',
            'rol' => 'admin'
        ],
        [
            'nombre' => 'Gestor',
            'apellido' => 'Domicilios',
            'numero_documento' => '987654321',
            'contrasena' => 'gestor123',
            'rol' => 'org_domicilios'
        ],
        [
            'nombre' => 'Cajera',
            'apellido' => 'Sistema',
            'numero_documento' => '456789123',
            'contrasena' => 'cajera123',
            'rol' => 'cajera'
        ]
    ];
    
    foreach ($usuarios as $usuario) {
        // Verificar si el usuario ya existe
        $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE numero_documento = ?");
        $stmt->bind_param("s", $usuario['numero_documento']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Crear el usuario
            $contrasena_hash = password_hash($usuario['contrasena'], PASSWORD_DEFAULT);
            
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, numero_documento, contrasena, rol, estado) VALUES (?, ?, ?, ?, ?, 'activo')");
            $stmt->bind_param("sssss", $usuario['nombre'], $usuario['apellido'], $usuario['numero_documento'], $contrasena_hash, $usuario['rol']);
            
            if ($stmt->execute()) {
                echo "Usuario {$usuario['nombre']} {$usuario['apellido']} creado exitosamente.<br>";
            } else {
                echo "Error al crear usuario {$usuario['nombre']}: " . $stmt->error . "<br>";
            }
        } else {
            echo "El usuario {$usuario['nombre']} ya existe.<br>";
        }
    }
    
    echo "<br><strong>Credenciales de prueba:</strong><br>";
    echo "Admin: 123456789 / admin123<br>";
    echo "Gestor: 987654321 / gestor123<br>";
    echo "Cajera: 456789123 / cajera123<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 