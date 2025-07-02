<?php
require_once '../servicios/verificar_sesion.php';
verificarSesion();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Gestión de Movimientos</title>
    <link rel="stylesheet" href="../componentes/movimientos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Añadir FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/componentes/movimientos.css">
    <link rel="stylesheet" href="../public/componentes/global.css">
</head>

<body>
   
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
           <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <a href="productos.php" class="menu-item">
                <i class="fas fa-box"></i>
                <span class="menu-text">Productos</span>
            </a>
            <a href="categorias.php" class="menu-item">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Categorías</span>
            </a>
            <a href="movimientos.php" class="menu-item active">
                <i class="fas fa-exchange-alt"></i>
                <span class="menu-text">Movimientos</span>
            </a>
            <a href="usuarios.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Usuarios</span>
            </a>
            <a href="reportes.html" class="menu-item">
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
    <div class="main-content">
        <div class="header">
            <h2>Gestión de Movimientos</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar movimientos...">
            </div>
            <div class="action-buttons">
                <button class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <button class="btn btn-primary" id="btnAddMovement">
                    <i class="fas fa-plus"></i> Nuevo Movimiento
                </button>
            </div>
        </div>

        <div class="filter-panel" id="filterPanel" style="display: none; margin-top: 1rem;">
            <form id="filterForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="filterTipo">Tipo de Movimiento</label>
                        <select id="filterTipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                            <option value="ajuste">Ajuste</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filterUsuario">Usuario</label>
                        <input type="text" id="filterUsuario" class="form-control" placeholder="Nombre del usuario">
                    </div>
                    <div class="form-group">
                        <label for="filterFecha">Fecha</label>
                        <input type="date" id="filterFecha" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Aplicar Filtro</button>
                <button type="button" class="btn btn-secondary" id="btnResetFilter">Restablecer</button>
            </form>
        </div>


        <div class="movements-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Usuario</th>
                        <th>Notas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td><span class="status status-entrada"></span></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="actions">
                            <div class="action-icon view"><i class="fas fa-eye"></i></div>
                            <div class="action-icon print"><i class="fas fa-print"></i></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <div class="page-item"><i class="fas fa-chevron-left"></i></div>
            <div class="page-item active">1</div>
            <div class="page-item">2</div>
            <div class="page-item">3</div>
            <div class="page-item"><i class="fas fa-chevron-right"></i></div>
        </div>
    </div>

    <!-- Modal para registrar movimiento -->
    <div class="modal" id="movementModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Registrar Nuevo Movimiento</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="movementForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="movementType">Tipo de Movimiento</label>
                        <select class="form-control" id="movementType" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                            <option value="ajuste">Ajuste</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="movementDate">Fecha</label>
                        <input type="date" class="form-control" id="movementDate" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="movementProduct">Producto</label>
                    <select class="form-control" id="movementProduct" required>
                        <option value="">Seleccionar producto</option>
                        <option value="1">Laptop HP Pavilion</option>
                        <option value="2">Monitor Dell 27"</option>
                        <option value="3">Teclado Mecánico RGB</option>
                        <option value="4">Mouse Inalámbrico</option>
                        <option value="5">Impresora Multifuncional</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="movementQuantity">Cantidad</label>
                        <input type="number" class="form-control" id="movementQuantity" required>
                    </div>
                    <div class="form-group">
                        <label for="movementUser">Usuario</label>
                        <select class="form-control" id="movementUser" required>
                            <option value="1">Admin</option>
                            <option value="2">Supervisor</option>
                            <option value="3">Vendedor</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="movementNotes">Notas</label>
                    <textarea class="form-control" id="movementNotes" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelMovement">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('movementModal');
            const btnAddMovement = document.getElementById('btnAddMovement');
            const btnCancelMovement = document.getElementById('btnCancelMovement');
            const closeModal = document.querySelector('.close-modal');

            // Abrir modal
            btnAddMovement.addEventListener('click', function () {
                modal.style.display = 'flex';
                document.getElementById('movementForm').reset();
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('movementDate').value = today;
            });

            // Filtro
            const btnFiltrar = document.querySelector('.btn-secondary');
            const filterPanel = document.getElementById('filterPanel');
            const filterForm = document.getElementById('filterForm');
            const btnResetFilter = document.getElementById('btnResetFilter');

            btnFiltrar.addEventListener('click', () => {
                filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
            });

            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const tipo = document.getElementById('filterTipo').value.toLowerCase();
                const usuario = document.getElementById('filterUsuario').value.toLowerCase();
                const fecha = document.getElementById('filterFecha').value;

                const rows = document.querySelectorAll('.movements-table tbody tr');
                rows.forEach(row => {
                    const tipoText = row.children[2]?.innerText.toLowerCase();
                    const usuarioText = row.children[5]?.innerText.toLowerCase();
                    const fechaText = row.children[1]?.innerText;

                    const matchTipo = tipo === "" || tipoText.includes(tipo);
                    const matchUsuario = usuario === "" || usuarioText.includes(usuario);
                    const matchFecha = fecha === "" || fechaText === fecha;

                    row.style.display = (matchTipo && matchUsuario && matchFecha) ? '' : 'none';
                });
            });

            btnResetFilter.addEventListener('click', () => {
                filterForm.reset();
                document.querySelectorAll('.movements-table tbody tr').forEach(row => {
                    row.style.display = '';
                });
            });

            // Cerrar modal
            function closeMovementModal() {
                modal.style.display = 'none';
            }

            btnCancelMovement.addEventListener('click', closeMovementModal);
            closeModal.addEventListener('click', closeMovementModal);

            window.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeMovementModal();
                }
            });

            // Enviar formulario
            document.getElementById('movementForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const data = {
                    tipo: document.getElementById('movementType').value,
                    fecha: document.getElementById('movementDate').value,
                    producto: document.getElementById('movementProduct').value,
                    cantidad: document.getElementById('movementQuantity').value,
                    usuario: document.getElementById('movementUser').value,
                    notas: document.getElementById('movementNotes').value
                };

                fetch('../servicios/guardar_movimiento.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(data)
                })
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            alert(response.message);
                            document.getElementById('movementForm').reset();
                            modal.style.display = 'none';
                            cargarMovimientos();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    })
                    .catch(error => {
                        alert('Error en la solicitud: ' + error);
                    });
            });

            // Cargar movimientos
            function cargarMovimientos() {
                fetch('../servicios/obtener_movimientos.php')
                    .then(res => res.json())
                    .then(data => {
                        const tbody = document.querySelector('.movements-table tbody');
                        tbody.innerHTML = '';
                        data.forEach(mov => {
                            const fila = document.createElement('tr');
                            fila.innerHTML = `
                            <td>${mov.id}</td>
                            <td>${mov.fecha}</td>
                            <td><span class="status status-${mov.tipo}">${mov.tipo}</span></td>
                            <td>${mov.producto}</td>
                            <td>${mov.cantidad}</td>
                            <td>${mov.usuario_nombre || 'Sin nombre'}</td>
                            <td>${mov.notas}</td>
                            <td class="actions">
                                <div class="action-icon view" data-id="${mov.id}"><i class="fas fa-eye"></i></div>
                                <div class="action-icon print" data-id="${mov.id}"><i class="fas fa-print"></i></div>
                            </td>
                        `;
                            tbody.appendChild(fila);

                            // Evento Ver
                            const viewBtn = fila.querySelector('.view');
                            viewBtn.addEventListener('click', () => {
                                const id = viewBtn.dataset.id;
                                fetch(`../servicios/obtener_detalle_movimiento.php?id=${id}`)
                                    .then(res => res.json())
                                    .then(mov => {
                                        alert(
                                            `Movimiento #${mov.id}\n` +
                                            `Fecha: ${mov.fecha}\n` +
                                            `Tipo: ${mov.tipo}\n` +
                                            `Producto: ${mov.producto}\n` +
                                            `Cantidad: ${mov.cantidad}\n` +
                                            `Usuario: ${mov.usuario_nombre}\n` +
                                            `Notas: ${mov.notas}`
                                        );
                                    })
                                    .catch(err => {
                                        alert('Error al obtener detalles: ' + err);
                                    });
                            });

                        });
                    })
                    .catch(err => {
                        console.error('Error al cargar movimientos:', err);
                    });
            }

            cargarMovimientos();

            // Buscar
            const searchInput = document.querySelector('.search-bar input');
            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('.movements-table tbody tr');
                tableRows.forEach(row => {
                    const rowText = row.innerText.toLowerCase();
                    row.style.display = rowText.includes(searchTerm) ? '' : 'none';
                });
            });

            setTimeout(() => {
                document.querySelectorAll('.print').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const id = this.dataset.id;
                        window.open(`../servicios/imprimir_movimiento.php?id=${id}`, '_blank');
                    });
                });
            }, 200);

        });
    </script>


</html>