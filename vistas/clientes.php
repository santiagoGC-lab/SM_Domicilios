<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit;
}
$nombre = $_SESSION['nombre'] ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$nombreCompleto = $nombre . ' ' . $apellido;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Gestión de Clientes</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/cliente.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <a href="pedidos.php" class="menu-item">
                <i class="fas fa-shopping-bag"></i>
                <span class="menu-text">Pedidos</span>
            </a>
            <a href="clientes.php" class="menu-item active">
                <i class="fas fa-users"></i>
                <span class="menu-text">Clientes</span>
            </a>
            <a href="domiciliarios.php" class="menu-item">
                <i class="fas fa-motorcycle"></i>
                <span class="menu-text">Domiciliarios</span>
            </a>
            <a href="zonas.php" class="menu-item">
                <i class="fas fa-map-marked-alt"></i>
                <span class="menu-text">Zonas de Entrega</span>
            </a>
            <a href="reportes.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <a href="configuracion.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Configuración</span>
            </a>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Gestión de Clientes</h2>
            <div class="user-info">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName"><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>

        <div class="clients-section">
            <div class="clients-actions">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar Cliente..." id="searchInput">
                    <button class="btn-search" onclick="loadClients()">Buscar</button>
                </div>
                <button class="btn-login" onclick="openNewClientModal()">
                    <i class="fas fa-user-plus"></i> Nuevo Cliente
                </button>
            </div>

            <div class="clients-table-container">
                <table class="clients-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Dirección y Barrio</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody id="clientsTableBody">
                        <!-- Cargado dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Cliente Simplificado -->
    <div id="modalClient" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Cliente</h2>
                <span class="close" onclick="closeModal('modalClient')">×</span>
            </div>
            <form id="formClient">
                <input type="hidden" id="clientId" name="id">

                <div class="form-group">
                    <label for="nombre">Nombre Completo *:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="documento">Documento *:</label>
                    <input type="text" id="documento" name="documento" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono *:</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección y Barrio*:</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="tipoCliente">Tipo de Cliente:</label>
                    <select id="tipoCliente" name="tipoCliente" class="form-control">
                        <option value="regular">Regular</option>
                        <option value="vip">VIP</option>
                        <option value="corporativo">Corporativo</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalClient')">Cancelar</button>
                    <button type="submit" class="btn-login">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function openNewClientModal() {
            document.getElementById('formClient').reset();
            document.getElementById('modalClient').style.display = 'block';
        }

        document.getElementById('formClient').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../servicios/guardar_cliente.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Cliente creado exitosamente');
                        closeModal('modalClient');
                        loadClients();
                    } else {
                        alert('Error: ' + (data.error || 'Error desconocido'));
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        function loadClients() {
            fetch('../servicios/obtener_clientes.php')
                .then(res => res.json())
                .then(clientes => {
                    const tbody = document.getElementById('clientsTableBody');
                    tbody.innerHTML = '';
                    clientes.forEach(cliente => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
          <td>${cliente.id_cliente}</td>
          <td>${cliente.nombre}</td>
          <td>${cliente.documento}</td>
          <td>${cliente.telefono}</td>
          <td>${cliente.direccion}</td>
          <td class="estado-${cliente.tipo_cliente.toLowerCase()}">${cliente.tipo_cliente}</td>
        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(err => console.error('Error al cargar clientes:', err));
        }

        document.addEventListener('DOMContentLoaded', loadClients);
    </script>
</body>

</html>