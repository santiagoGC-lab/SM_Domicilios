<?php
require_once '../servicios/verificar_permisos.php';
verificarAcceso('clientes');
$nombreCompleto = obtenerNombreUsuario();
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
            <?php if (tienePermiso('dashboard')): ?>
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <?php endif; ?>
            <a href="pedidos.php" class="menu-item">
                <i class="fas fa-shopping-bag"></i>
                <span class="menu-text">Pedidos</span>
            </a>
            <a href="clientes.php" class="menu-item active">
                <i class="fas fa-users"></i>
                <span class="menu-text">Clientes</span>
            </a>
            <?php if (tienePermiso('domiciliarios')): ?>
            <a href="domiciliarios.php" class="menu-item">
                <i class="fas fa-motorcycle"></i>
                <span class="menu-text">Domiciliarios</span>
            </a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
            <a href="zonas.php" class="menu-item">
                <i class="fas fa-map-marked-alt"></i>
                <span class="menu-text">Zonas de Entrega</span>
            </a>
            <?php endif; ?>
            <a href="reportes.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <?php if (esAdmin()): ?>
            <a href="tabla_usuarios.php" class="menu-item"><i class="fas fa-users-cog"></i><span class="menu-text">Gestionar Usuarios</span></a>
            <?php endif; ?>
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
                    <input type="text" placeholder="Buscar Cliente..." id="searchInput" oninput="loadClients()">
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
                            <th>Acciones</th>
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
                    <label for="direccion">Dirección *:</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="barrio">Barrio *:</label>
                    <input type="text" id="barrio" name="barrio" class="form-control" required>
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
                    <button type="submit" class="btn-login" id="submitButton">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar clientes al iniciar
            loadClients();
        });

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function openNewClientModal() {
            document.getElementById('formClient').reset();
            document.getElementById('clientId').value = '';
            document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
            document.getElementById('modalClient').style.display = 'block';
        }

        document.getElementById('formClient').addEventListener('submit', function(e) {
            e.preventDefault();
            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;
            // Validación básica en frontend
            const nombre = document.getElementById('nombre').value.trim();
            const documento = document.getElementById('documento').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const direccion = document.getElementById('direccion').value.trim();
            const barrio = document.getElementById('barrio').value.trim();
            if (!nombre || !documento || !telefono || !direccion || !barrio) {
                alert('Por favor, complete todos los campos obligatorios.');
                submitButton.disabled = false;
                return;
            }
            const formData = new FormData(this);
            formData.append('accion', 'guardar');
            fetch('../servicios/clientes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.status === 401) {
                    alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                    window.location.href = '../vistas/login.html';
                    return Promise.reject('Sesión expirada');
                }
                return response.json();
            })
            .then(data => {
                submitButton.disabled = false;
                if (data.success) {
                    alert('Cliente guardado exitosamente');
                    closeModal('modalClient');
                    loadClients();
                    document.getElementById('formClient').reset();
                } else {
                    alert('Error: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                if (error !== 'Sesión expirada') {
                    alert('Error al guardar el cliente');
                    console.error('Error:', error);
                }
            });
        });

        function eliminarCliente(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('accion', 'eliminar');

                fetch('../servicios/clientes.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Cliente eliminado exitosamente');
                        loadClients();
                    } else {
                        alert('Error: ' + (data.error || 'No se pudo eliminar'));
                    }
                })
                .catch(err => {
                    alert('Error al eliminar el cliente');
                    console.error(err);
                });
            }
        }

        function removeAccents(str) {
            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        }

        function loadClients() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const searchTerm = removeAccents(searchInput);

            fetch('../servicios/clientes.php', {
                method: 'POST',
                body: (() => { const fd = new FormData(); fd.append('accion', 'obtener'); return fd; })()
            })
                .then(res => res.json())
                .then(clientes => {
                    const tbody = document.getElementById('clientsTableBody');
                    tbody.innerHTML = '';

                    const filtrados = clientes.filter(cliente => {
                        return (
                            removeAccents(cliente.nombre.toLowerCase()).includes(searchTerm) ||
                            removeAccents(cliente.documento.toLowerCase()).includes(searchTerm) ||
                            removeAccents(cliente.telefono.toLowerCase()).includes(searchTerm) ||
                            removeAccents(cliente.direccion.toLowerCase()).includes(searchTerm) ||
                            removeAccents(cliente.barrio.toLowerCase()).includes(searchTerm) ||
                            removeAccents(cliente.tipo_cliente.toLowerCase()).includes(searchTerm)
                        );
                    });

                    filtrados.forEach(cliente => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${cliente.id_cliente}</td>
                            <td>${cliente.nombre}</td>
                            <td>${cliente.documento}</td>
                            <td>${cliente.telefono}</td>
                            <td>${cliente.direccion} - ${cliente.barrio}</td>
                            <td class="estado-${cliente.tipo_cliente.toLowerCase()}">${cliente.tipo_cliente}</td>
                            <td>
                                <button class="btn btn-editar" onclick="editarCliente(${cliente.id_cliente})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-eliminar" onclick="eliminarCliente(${cliente.id_cliente})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(err => console.error('Error al cargar clientes:', err));
        }

        function editarCliente(id) {
            const formData = new FormData();
            formData.append('accion', 'obtener_por_id');
            formData.append('id', id);
            fetch('../servicios/clientes.php', {
                method: 'POST',
                body: formData
            })
                .then(res => {
                    if (res.status === 401) {
                        alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                        window.location.href = '../vistas/login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    return res.json();
                })
                .then(cliente => {
                    if (cliente.error) {
                        alert('Error: ' + cliente.error);
                        return;
                    }
                    document.getElementById('modalTitle').textContent = 'Editar Cliente';
                    document.getElementById('clientId').value = cliente.id_cliente;
                    document.getElementById('nombre').value = cliente.nombre;
                    document.getElementById('documento').value = cliente.documento;
                    document.getElementById('telefono').value = cliente.telefono;
                    document.getElementById('direccion').value = cliente.direccion;
                    document.getElementById('barrio').value = cliente.barrio;
                    document.getElementById('tipoCliente').value = cliente.tipo_cliente.toLowerCase();
                    document.getElementById('modalClient').style.display = 'block';
                })
                .catch(err => {
                    if (err !== 'Sesión expirada') {
                        alert('Error al cargar el cliente');
                        console.error(err);
                    }
                });
        }

        // Manejo global de errores de sesión para todos los fetch
        (function() {
            const originalFetch = window.fetch;
            window.fetch = function() {
                return originalFetch.apply(this, arguments).then(response => {
                    if (response.status === 401) {
                        alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                        window.location.href = '../vistas/login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    return response;
                });
            };
        })();
    </script>
</body>

</html>