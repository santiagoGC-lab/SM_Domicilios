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

## 📁 Estructura del proyecto

SM_Domicilios/
│
├── servicios/ # Servicios PHP para operaciones CRUD y conexiones
│ ├── obtener_clientes.php
│ ├── insertar_cliente.php
│ ├── editar_cliente.php
│ ├── eliminar_cliente.php
│ └── conexion.php
│
├── interfaces/ # Archivos HTML y CSS
│ ├── index.html # Página principal con formulario de registro
│ ├── gestion.html # Gestión y visualización de pedidos
│ └── estilos.css # Estilos personalizados
│
├── bd/ # Archivos relacionados con la base de datos
│ └── sm_domicilios.sql # Script para crear y poblar la base de datos
│
└── README.md # Documentación del proyecto# SM_Domicilios

## 🧠 Base de datos

Nombre de la base de datos: `sm_domicilios`

Tabla principal: `programados`

Columnas:
- `cedula` (VARCHAR)
- `nombre` (VARCHAR)
- `barrio` (VARCHAR)
- `direccion` (VARCHAR)
- `telefono` (VARCHAR)
- `cantidad_paquetes` (INT)
- `envioya` (ENUM: 'Sí', 'No')
- `alistamiento` (ENUM: 'Sí', 'No')

⚠️ Asegúrate de tener esta base de datos creada en tu gestor MySQL y de configurar los parámetros en `conexion.php`.

## ⚙️ Requisitos

- Servidor local: [XAMPP](https://www.apachefriends.org/es/index.html), [MAMP](https://www.mamp.info/en/), o similar
- PHP 7.4+
- MySQL
- Navegador web moderno (Chrome, Firefox, Edge)

## 🚀 Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/santiagoGC-lab/SM_Domicilios.git
