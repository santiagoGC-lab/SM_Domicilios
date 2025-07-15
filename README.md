# SM_Domicilios 🚚📦

Sistema de gestión de domicilios para supermercados, desarrollado en PHP con base de datos MySQL. Este proyecto permite registrar, visualizar, editar y eliminar pedidos a domicilio, facilitando la organización diaria del supermercado y mejorando la eficiencia en las entregas.

## 🧩 Funcionalidades principales

- 📋 Registro de pedidos a domicilio
- 🔎 Búsqueda de clientes por cédula
- 🛒 Gestión de alistamiento y envío
- 🧑‍💼 Interfaz para usuarios administrativos
- ✏️ Edición y eliminación de registros
- 📅 Organización diaria por fecha y estado del pedido
- 📲 Adaptado para ser usado desde navegador (versión web)
- 📊 Generación de reportes en PDF
- 👥 Gestión de usuarios y permisos
- 🗺️ Gestión de zonas de entrega

## 📁 Estructura del proyecto (Reorganizada)

```
SM_Domicilios/
│
├── index.php              # Punto de entrada principal de la aplicación
├── config.php             # Configuración central del proyecto
├── README.md              # Documentación del proyecto
│
├── servicios/             # Lógica de negocio y endpoints API
│   ├── clientes.php       # Gestión de clientes (CRUD)
│   ├── domiciliarios.php  # Gestión de domiciliarios (CRUD)
│   ├── pedidos.php        # Gestión de pedidos (CRUD)
│   ├── zonas.php          # Gestión de zonas (CRUD)
│   ├── usuarios.php       # Autenticación y gestión de usuarios
│   ├── reportes.php       # Generación de reportes
│   └── conexion.php       # Conexión a base de datos
│
├── vistas/                # Interfaces de usuario (HTML/PHP)
│   ├── login.html         # Página de inicio de sesión
│   ├── dashboard.php      # Panel principal
│   ├── clientes.php       # Gestión de clientes
│   ├── domiciliarios.php  # Gestión de domiciliarios
│   ├── pedidos.php        # Gestión de pedidos
│   ├── historial_pedidos.php # Historial de pedidos
│   ├── zonas.php          # Gestión de zonas
│   ├── reportes.php       # Reportes
│   ├── tabla_usuarios.php # Gestión de usuarios
│   ├── recuperar-contra.html # Recuperación de contraseña
│   └── reset-password.php # Reset de contraseña
│
├── componentes/           # Recursos estáticos
│   ├── *.css             # Archivos de estilos
│   └── img/              # Imágenes del proyecto
│
├── BDD/                  # Base de datos
│   └── sm_domicilios.sql # Script de la base de datos
│
├── tests/                # Archivos de prueba y debug
│   ├── test_pedidos.php
│   ├── debug_pedidos.php
│   └── insertar_datos_prueba.php
│
└── logs/                 # Archivos de registro
```

## 🧠 Base de datos

Nombre de la base de datos: `sm_domicilios`

### Tablas principales:
- `programados` - Pedidos a domicilio
- `clientes` - Información de clientes
- `domiciliarios` - Información de domiciliarios
- `zonas` - Zonas de entrega
- `usuarios` - Usuarios del sistema

### Columnas de la tabla `programados`:
- `cedula` (VARCHAR)
- `nombre` (VARCHAR)
- `barrio` (VARCHAR)
- `direccion` (VARCHAR)
- `telefono` (VARCHAR)
- `cantidad_paquetes` (INT)
- `envioya` (ENUM: 'Sí', 'No')
- `alistamiento` (ENUM: 'Sí', 'No')

⚠️ Asegúrate de tener esta base de datos creada en tu gestor MySQL y de configurar los parámetros en `config.php`.

## ⚙️ Requisitos

- Servidor local: [XAMPP](https://www.apachefriends.org/es/index.html), [MAMP](https://www.mamp.info/en/), o similar
- PHP 7.4+
- MySQL
- Navegador web moderno (Chrome, Firefox, Edge)

## 🚀 Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/santiagoGC-lab/SM_Domicilios.git
   ```

2. Coloca el proyecto en tu servidor web (htdocs, www, etc.)

3. Importa la base de datos:
   - Abre phpMyAdmin
   - Crea una nueva base de datos llamada `sm_domicilios`
   - Importa el archivo `BDD/sm_domicilios.sql`

4. Configura la conexión:
   - Edita `config.php` con tus credenciales de base de datos
   - Ajusta la URL de la aplicación si es necesario

5. Accede a la aplicación:
   ```
   http://localhost/SM_Domicilios/
   ```

## 🔧 Configuración

### Archivo config.php
El archivo `config.php` centraliza toda la configuración:
- Credenciales de base de datos
- URLs de la aplicación
- Configuración de sesiones
- Rutas de archivos
- Configuración de desarrollo/producción

### Servicios agrupados
Los servicios están organizados por entidad:
- `servicios/clientes.php` - Todas las operaciones de clientes
- `servicios/pedidos.php` - Todas las operaciones de pedidos
- `servicios/domiciliarios.php` - Todas las operaciones de domiciliarios
- `servicios/zonas.php` - Todas las operaciones de zonas
- `servicios/usuarios.php` - Autenticación y gestión de usuarios
- `servicios/reportes.php` - Generación de reportes

## 📝 Uso

1. **Acceso**: Ve a `http://localhost/SM_Domicilios/`
2. **Login**: Inicia sesión con tus credenciales
3. **Navegación**: Usa el menú para acceder a las diferentes secciones
4. **Gestión**: Crea, edita, elimina y visualiza registros según tus permisos

## 🔒 Seguridad

- Autenticación de usuarios
- Verificación de permisos
- Validación de datos
- Protección contra inyección SQL
- Configuración de sesiones seguras

## 🧪 Testing

Los archivos de prueba están en la carpeta `tests/`:
- `test_pedidos.php` - Pruebas de funcionalidad de pedidos
- `debug_pedidos.php` - Debug de pedidos
- `insertar_datos_prueba.php` - Datos de prueba

## 📊 Reportes

El sistema incluye generación de reportes:
- Reportes en PDF
- Exportación de datos
- Filtros por fecha y estado
- Estadísticas de pedidos

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 👨‍💻 Autor

**SantiagoGC-lab**
- GitHub: [@santiagoGC-lab](https://github.com/santiagoGC-lab)

## 🙏 Agradecimientos

- MAMP/XAMPP por el entorno de desarrollo
- Comunidad PHP por las mejores prácticas
- Contribuidores del proyecto
