<?php
// --- Verificación de permisos y obtención de nombre de usuario ---
require_once '../servicios/verificar_permisos.php';
verificarAcceso('domiciliarios');
$nombreCompleto = obtenerNombreUsuario();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SM - Domiciliarios</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/domiciliarios.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>

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
            <?php if (tienePermiso('pedidos')): ?>
                <a href="pedidos.php" class="menu-item">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="menu-text">Pedidos</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('coordinador')): ?>
                <a href="coordinador.php" class="menu-item">
                    <i class="fas fa-truck"></i>
                    <span class="menu-text">Coordinador</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('clientes')): ?>
                <a href="clientes.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Clientes</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('domiciliarios')): ?>
                <a href="domiciliarios.php" class="menu-item active">
                    <i class="fas fa-motorcycle"></i>
                    <span class="menu-text">Domiciliarios</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('vehiculos')): ?>
                <a href="vehiculos.php" class="menu-item">
                    <i class="fas fa-car"></i>
                    <span class="menu-text">Vehiculos</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
                <a href="zonas.php" class="menu-item">
                    <i class="fas fa-map-marked-alt"></i>
                    <span class="menu-text">Zonas de Entrega</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('reportes')): ?>
                <a href="reportes.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-text">Reportes</span>
                </a>
            <?php endif; ?>
            <?php if (esAdmin()): ?>
                <a href="tabla_usuarios.php" class="menu-item"><i class="fas fa-users-cog"></i><span class="menu-text">Gestionar Usuarios</span></a>
            <?php endif; ?>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar"><i class="fas fa-sign-out-alt"></i><span class="menu-text">Cerrar Sesión</span></a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Gestión de Domiciliarios</h2>
            <div class="user-info" onclick="showUserMenu()">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName"><?= htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>

        <div class="users-section">
            <div class="users-actions">
                <div class="search-and-filter">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar domiciliario..." oninput="filterDomiciliarios()">
                    </div>
                    <select id="filterStatus" class="filter-select" onchange="filterDomiciliarios()">
                        <option value="">Todos</option> <!-- Agregado -->
                        <option value="disponible">Disponible</option>
                        <option value="en servicio">En Servicio</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <button class="btn-login" onclick="openNewDomiciliarioModal()">
                    <i class="fas fa-user-plus"></i> Nuevo Domiciliario
                </button>
            </div>

            <div class="users-table-container" style="background: #fff; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.07); overflow-x: auto;">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Zona Asignada</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="domiciliariosTableBody"></tbody>
                </table>
                <div id="paginationDomiciliarios" class="pagination"></div>
            </div>
        </div>
    </div>

    <!-- Modal para crear o editar un domiciliario -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Domiciliario</h2>
                <span class="close" onclick="closeModal('modalEditar')">×</span>
            </div>
            <form id="formEditar">
                <input type="hidden" id="domiciliarioId" name="id">
                <div class="form-group">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" required>
                </div>
                <div class="form-group" id="zonaGroup" style="display: none;">
                    <label for="zona">Zona Asignada:</label>
                    <select id="zona" name="zona" class="form-control">
                        <option value="">Sin zona asignada</option>
                        <option value="1">Occidente</option>
                        <option value="2">Centro</option>
                        <option value="3">Sur</option>
                        <option value="4">Norte</option>
                        <option value="6">Oriente</option>
                    </select>
                </div>
                <div class="form-group" id="estadoGroup" style="display: none;">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" class="form-control">
                        <option value="disponible">Disponible</option>
                        <option value="en servicio">En Servicio</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn-login">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // --- Variables de paginación y estado global ---
        const rowsPerPage = 5;
        let currentPage = 1;
        let totalDomiciliarios = 0;

        // --- Inicialización de eventos y carga de domiciliarios al cargar la página ---
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            loadDomiciliarios(currentPage);
        });

        // Configura los listeners de eventos principales
        function setupEventListeners() {
            document.getElementById('sidebarToggle').addEventListener('click', () => {
                document.getElementById('sidebar').classList.toggle('collapsed');
            });
            document.getElementById('formEditar').addEventListener('submit', handleDomiciliarioSubmit);
        }

        // Abre el modal para crear un nuevo domiciliario
        function openNewDomiciliarioModal() {
            document.getElementById('modalTitle').textContent = 'Nuevo Domiciliario';
            document.getElementById('formEditar').reset();
            document.getElementById('domiciliarioId').value = '';
            document.getElementById('zonaGroup').style.display = 'none';
            document.getElementById('estadoGroup').style.display = 'none';
            document.getElementById('modalEditar').style.display = 'block';
        }

        // Carga los datos de un domiciliario y abre el modal para editar
        function editarDomiciliario(id) {
            fetch(`../servicios/domiciliarios.php`, {
                    method: 'POST',
                    body: (() => {
                        const fd = new FormData();
                        fd.append('accion', 'obtener_por_id');
                        fd.append('id', id);
                        return fd;
                    })()
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
                    document.getElementById('modalTitle').textContent = 'Editar Domiciliario';
                    document.getElementById('domiciliarioId').value = id;
                    document.getElementById('nombre').value = data.nombre || '';
                    document.getElementById('telefono').value = data.telefono || '';
                    document.getElementById('zona').value = data.id_zona || '';
                    document.getElementById('estado').value = data.estado || 'disponible';
                    document.getElementById('zonaGroup').style.display = 'block';
                    document.getElementById('estadoGroup').style.display = 'block';
                    document.getElementById('modalEditar').style.display = 'block';
                })
                .catch(error => {
                    if (error !== 'Sesión expirada') {
                        alert('Error al cargar el domiciliario');
                        console.error(error);
                    }
                });
        }

        // Elimina un domiciliario por su id
        function eliminarDomiciliario(id) {
            if (confirm('¿Deseas eliminar este domiciliario?')) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('accion', 'eliminar');
                fetch('../servicios/domiciliarios.php', {
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
                    .then(() => loadDomiciliarios())
                    .catch(error => {
                        if (error !== 'Sesión expirada') {
                            alert('Error al eliminar el domiciliario');
                            console.error(error);
                        }
                    });
            }
        }

        // Cierra el modal especificado por id
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // Maneja el envío del formulario para crear/editar domiciliario
        function handleDomiciliarioSubmit(e) {
            e.preventDefault();

            const telefono = document.getElementById('telefono').value;

            if (telefono.length !== 10 || isNaN(telefono)) {
                alert('Teléfono debe tener 10 dígitos.');
                return;
            }

            const formData = new FormData(e.target);
            formData.append('accion', 'guardar');
            fetch('../servicios/domiciliarios.php', {
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
                .then(data => {
                    if (data.success) {
                        alert('Domiciliario guardado correctamente.');
                        closeModal('modalEditar');
                        loadDomiciliarios();
                    } else {
                        alert('Error: ' + (data.error || 'No se pudo guardar'));
                    }
                })
                .catch(error => {
                    if (error !== 'Sesión expirada') {
                        alert('Error al guardar el domiciliario');
                        console.error(error);
                    }
                });
        }

        // Redirige al menú de usuario (en este caso, al login)
        function showUserMenu() {
            window.location.href = '../vistas/login.html';
        }

        // Quita tildes para mejorar la búsqueda
        function quitarTildes(texto) {
            return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        // Filtra los domiciliarios en la tabla según búsqueda y estado
        function filterDomiciliarios() {
            const searchInput = document.getElementById('searchInput').value.trim();
            const search = quitarTildes(searchInput.toLowerCase());
            const status = document.getElementById('filterStatus').value.toLowerCase();
            const rows = document.querySelectorAll('#domiciliariosTableBody tr');

            rows.forEach(row => {
                const nombre = quitarTildes(row.children[0].textContent);
                const telefono = row.children[1].textContent.toLowerCase();
                const zona = quitarTildes((row.children[2].textContent || ''));
                const estado = quitarTildes(row.children[3].textContent);

                const coincideTexto =
                    nombre.includes(search) ||
                    telefono.includes(search) ||
                    zona.includes(search);

                const coincideEstado = status === '' || estado === status;

                row.style.display = (coincideTexto && coincideEstado) ? '' : 'none';
            });
        }

        // Carga y muestra los domiciliarios, aplica paginación
        function loadDomiciliarios(page = 1) {
            fetch('../servicios/domiciliarios.php', {
                    method: 'POST',
                    body: (() => {
                        const fd = new FormData();
                        fd.append('accion', 'paginar');
                        fd.append('pagina', page);
                        fd.append('por_pagina', rowsPerPage);
                        return fd;
                    })()
                })
                .then(res => res.json())
                .then(data => {
                    totalDomiciliarios = data.total;
                    renderDomiciliarios(data.domiciliarios);
                    renderPaginationDomiciliarios(page);
                })
                .catch(error => {
                    alert('Error al cargar domiciliarios');
                    console.error(error);
                });
        }

        // Renderiza la tabla de domiciliarios
        function renderDomiciliarios(domiciliarios) {
            const tbody = document.getElementById('domiciliariosTableBody');
            let html = '';
            domiciliarios.forEach(d => {
                // Cambiar el texto del estado para mostrar
                let estadoTexto = d.estado;
                if (d.estado === 'ocupado') {
                    estadoTexto = 'en servicio';
                }

                html += `
                <tr>
                    <td>${d.nombre}</td>
                    <td>${d.telefono}</td>
                    <td>${d.zona || 'Sin asignar'}</td>
                    <td><span class="estado-${d.estado.replace(/\s/g, '')}">${estadoTexto}</span></td>
                    <td>
                        <button class="btn btn-editar" onclick="editarDomiciliario(${d.id_domiciliario})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-eliminar" onclick="eliminarDomiciliario(${d.id_domiciliario})"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
            filterDomiciliarios();
        }

        // Renderiza los controles de paginación
        function renderPaginationDomiciliarios(page) {
            const pagination = document.getElementById('paginationDomiciliarios');
            const totalPages = Math.ceil(totalDomiciliarios / rowsPerPage);
            let html = '';
            if (totalPages > 1) {
                html += `<button onclick="loadDomiciliarios(1)" ${page === 1 ? 'disabled' : ''}>Primera</button>`;
                html += `<button onclick="loadDomiciliarios(${page - 1})" ${page === 1 ? 'disabled' : ''}>Anterior</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    if (i === page) {
                        html += `<button class="active">${i}</button>`;
                    } else {
                        html += `<button onclick="loadDomiciliarios(${i})">${i}</button>`;
                    }
                }
                html += `<button onclick="loadDomiciliarios(${page + 1})" ${page === totalPages ? 'disabled' : ''}>Siguiente</button>`;
                html += `<button onclick="loadDomiciliarios(${totalPages})" ${page === totalPages ? 'disabled' : ''}>Última</button>`;
            }
            pagination.innerHTML = html;
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