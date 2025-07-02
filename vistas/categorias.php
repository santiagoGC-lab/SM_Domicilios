<?php
require_once '../servicios/verificar_sesion.php';
verificarSesion();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Gestión de Categorías</title>
    <link rel="stylesheet" href="../componentes/categorias.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Añadir FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <a href="productos.php" class="menu-item">
                <i class="fas fa-box"></i>
                <span class="menu-text">Productos</span>
            </a>
            <a href="categorias.php" class="menu-item active">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Categorías</span>
            </a>

            <a href="movimientos.php" class="menu-item">
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
            <h2>Gestión de Categorías</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar categorías...">
            </div>
            <div class="action-buttons">
                <button class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <button class="btn btn-primary" id="btnAddCategory">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </button>
            </div>
        </div>

        <div class="categories-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td>Electrónicos</td>
                        <td>Productos electrónicos y tecnológicos</td>
                        <td>42</td>
                        <td>Activa</td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>002</td>
                        <td>Accesorios</td>
                        <td>Accesorios para computadoras y dispositivos</td>
                        <td>28</td>
                        <td>Activa</td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>003</td>
                        <td>Oficina</td>
                        <td>Equipamiento y suministros de oficina</td>
                        <td>15</td>
                        <td>Activa</td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>004</td>
                        <td>Mobiliario</td>
                        <td>Muebles y equipamiento para oficinas</td>
                        <td>8</td>
                        <td>Activa</td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
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

    <!-- Modal para agregar/editar categoría -->
    <div class="modal" id="categoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Nueva Categoría</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="categoryForm">
                <div class="form-group">
                    <label for="categoryName">Nombre de la Categoría</label>
                    <input type="text" class="form-control" id="categoryName" required>
                </div>
                <div class="form-group">
                    <label for="categoryDescription">Descripción</label>
                    <textarea class="form-control" id="categoryDescription" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="categoryStatus">Estado</label>
                    <select class="form-control" id="categoryStatus" required>
                        <option value="1">Activa</option>
                        <option value="0">Inactiva</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelCategory">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // JavaScript para funcionalidad interactiva avanzada
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('categoryModal');
            const btnAddCategory = document.getElementById('btnAddCategory');
            const btnCancelCategory = document.getElementById('btnCancelCategory');
            const closeModal = document.querySelector('.close-modal');
            const editButtons = document.querySelectorAll('.edit');
            const deleteButtons = document.querySelectorAll('.delete');
            const searchInput = document.querySelector('.search-bar input');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const tableRows = document.querySelectorAll('tbody tr');
            const pageItems = document.querySelectorAll('.page-item');

            // Toggle sidebar en móvil
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('active');
            });

            // Cerrar sidebar al hacer clic fuera en móvil
            document.addEventListener('click', function (event) {
                if (window.innerWidth <= 768 &&
                    !sidebar.contains(event.target) &&
                    !sidebarToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            });

            // Animaciones de entrada para elementos
            function animateElements() {
                const cards = document.querySelectorAll('.categories-table, .header');
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 200);
                });
            }

            // Búsqueda en tiempo real
            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                const tableBody = document.querySelector('tbody');

                // Agregar efecto de carga
                tableBody.classList.add('loading');

                setTimeout(() => {
                    tableRows.forEach(row => {
                        const categoryName = row.cells[1].textContent.toLowerCase();
                        const categoryDesc = row.cells[2].textContent.toLowerCase();

                        if (categoryName.includes(searchTerm) || categoryDesc.includes(searchTerm)) {
                            row.style.display = '';
                            row.style.animation = 'fadeIn 0.5s ease';
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    tableBody.classList.remove('loading');
                }, 300);
            });

            // Efectos hover mejorados para filas de tabla
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function () {
                    this.style.transform = 'scale(1.02)';
                    this.style.boxShadow = '0 8px 25px rgba(76, 175, 80, 0.15)';
                });

                row.addEventListener('mouseleave', function () {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = 'none';
                });
            });

            // Abrir modal con animación mejorada
            function openModal(title, isEdit = false) {
                modal.style.display = 'flex';
                modal.style.animation = 'fadeIn 0.3s ease';

                const modalContent = document.querySelector('.modal-content');
                modalContent.style.animation = 'slideInDown 0.4s ease';

                document.querySelector('.modal-title').textContent = title;

                if (!isEdit) {
                    document.getElementById('categoryForm').reset();
                }

                // Enfocar primer campo
                setTimeout(() => {
                    document.getElementById('categoryName').focus();
                }, 400);
            }

            // Cerrar modal con animación
            function closeCategoryModal() {
                const modalContent = document.querySelector('.modal-content');
                modalContent.style.animation = 'slideOutUp 0.3s ease';

                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }

            // Event listeners para modal
            btnAddCategory.addEventListener('click', function () {
                openModal('Agregar Nueva Categoría');
            });

            btnCancelCategory.addEventListener('click', closeCategoryModal);
            closeModal.addEventListener('click', closeCategoryModal);

            // Cerrar modal al hacer clic fuera del contenido
            window.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeCategoryModal();
                }
            });

            // Cerrar modal con tecla Escape
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.style.display === 'flex') {
                    closeCategoryModal();
                }
            });

            // Editar categoría con datos simulados
            editButtons.forEach((button, index) => {
                button.addEventListener('click', function () {
                    openModal('Editar Categoría', true);

                    // Simular carga de datos
                    const row = this.closest('tr');
                    const categoryData = {
                        name: row.cells[1].textContent,
                        description: row.cells[2].textContent,
                        status: row.cells[4].textContent === 'Activa' ? '1' : '0'
                    };

                    // Llenar formulario con datos
                    setTimeout(() => {
                        document.getElementById('categoryName').value = categoryData.name;
                        document.getElementById('categoryDescription').value = categoryData.description;
                        document.getElementById('categoryStatus').value = categoryData.status;
                    }, 100);
                });
            });

            // Eliminar categoría con confirmación mejorada
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const row = this.closest('tr');
                    const categoryName = row.cells[1].textContent;

                    if (confirm(`¿Está seguro de que desea eliminar la categoría "${categoryName}"?\n\nEsta acción no se puede deshacer.`)) {
                        // Animación de eliminación
                        row.style.animation = 'slideOutRight 0.5s ease';

                        setTimeout(() => {
                            row.remove();
                            showNotification('Categoría eliminada correctamente', 'success');
                        }, 500);
                    }
                });
            });

            // Validación de formulario en tiempo real
            const formInputs = document.querySelectorAll('#categoryForm input, #categoryForm textarea, #categoryForm select');
            formInputs.forEach(input => {
                input.addEventListener('input', function () {
                    validateField(this);
                });

                input.addEventListener('blur', function () {
                    validateField(this);
                });
            });

            function validateField(field) {
                const value = field.value.trim();
                const isValid = field.checkValidity() && value.length > 0;

                if (isValid) {
                    field.style.borderColor = '#4CAF50';
                    field.style.boxShadow = '0 0 5px rgba(76, 175, 80, 0.3)';
                } else if (value.length > 0) {
                    field.style.borderColor = '#f44336';
                    field.style.boxShadow = '0 0 5px rgba(244, 67, 54, 0.3)';
                } else {
                    field.style.borderColor = '#ddd';
                    field.style.boxShadow = 'none';
                }
            }

            // Manejar envío del formulario con validación
            document.getElementById('categoryForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');

                // Mostrar estado de carga
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                submitBtn.disabled = true;

                // Simular envío de datos
                setTimeout(() => {
                    showNotification('Categoría guardada correctamente', 'success');
                    closeCategoryModal();

                    // Restaurar botón
                    submitBtn.innerHTML = 'Guardar';
                    submitBtn.disabled = false;
                }, 1500);
            });

            // Paginación interactiva
            pageItems.forEach(item => {
                item.addEventListener('click', function () {
                    if (!this.classList.contains('active')) {
                        // Remover clase active de todos los elementos
                        pageItems.forEach(p => p.classList.remove('active'));

                        // Agregar clase active al elemento clickeado
                        this.classList.add('active');

                        // Simular carga de nueva página
                        const tableBody = document.querySelector('tbody');
                        tableBody.classList.add('loading');

                        setTimeout(() => {
                            tableBody.classList.remove('loading');
                        }, 800);
                    }
                });
            });

            // Sistema de notificaciones
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                    <button class="close-notification">&times;</button>
                `;

                // Estilos para la notificación
                Object.assign(notification.style, {
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    background: type === 'success' ? '#4CAF50' : '#2196F3',
                    color: 'white',
                    padding: '15px 20px',
                    borderRadius: '5px',
                    boxShadow: '0 4px 15px rgba(0, 0, 0, 0.2)',
                    zIndex: '10000',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '10px',
                    animation: 'slideInRight 0.3s ease',
                    maxWidth: '300px'
                });

                document.body.appendChild(notification);

                // Auto-cerrar después de 3 segundos
                setTimeout(() => {
                    notification.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }, 3000);

                // Cerrar manualmente
                notification.querySelector('.close-notification').addEventListener('click', () => {
                    notification.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                });
            }

            // Agregar animaciones CSS adicionales
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideOutUp {
                    to { transform: translateY(-100%); opacity: 0; }
                }
                @keyframes slideOutRight {
                    to { transform: translateX(100%); opacity: 0; }
                }
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);

            // Inicializar animaciones
            animateElements();

            // Scroll suave para la tabla
            const categoriesTable = document.querySelector('.categories-table');
            let isScrolling = false;

            categoriesTable.addEventListener('scroll', function () {
                if (!isScrolling) {
                    window.requestAnimationFrame(function () {
                        // Agregar efecto de sombra al header cuando se hace scroll
                        const thead = categoriesTable.querySelector('thead');
                        if (categoriesTable.scrollTop > 0) {
                            thead.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
                        } else {
                            thead.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.1)';
                        }
                        isScrolling = false;
                    });
                }
                isScrolling = true;
            });
        });
    </script>
</body>

</html>