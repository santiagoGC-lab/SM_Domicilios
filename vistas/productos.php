<?php
require_once '../servicios/verificar_sesion.php';
verificarSesion();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Gestión de Productos</title>
    <link rel="stylesheet" href="../componentes/productos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Añadir FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <a href="productos.php" class="menu-item active">
                <i class="fas fa-box"></i>
                <span class="menu-text">Productos</span>
            </a>
            <a href="categorias.php" class="menu-item">
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
            <h2>Gestión de Productos</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar productos...">
            </div>
            <div class="action-buttons">
                <button class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <button class="btn btn-primary" id="btnAddProduct">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </button>
            </div>
        </div>
        
        <div class="products-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td><img src="" alt="Producto" style="border-radius: 4px;"></td>
                        <td>Laptop HP Pavilion</td>
                        <td>Electrónicos</td>
                        <td>25</td>
                        <td>$899.99</td>
                        <td><span class="status status-disponible">Disponible</span></td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>002</td>
                        <td><img src="" alt="Producto" style="border-radius: 4px;"></td>
                        <td>Monitor Dell 27"</td>
                        <td>Electrónicos</td>
                        <td>8</td>
                        <td>$349.99</td>
                        <td><span class="status status-bajo">Stock Bajo</span></td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>003</td>
                        <td><img src="" alt="Producto" style="border-radius: 4px;"></td>
                        <td>Teclado Mecánico RGB</td>
                        <td>Accesorios</td>
                        <td>0</td>
                        <td>$129.99</td>
                        <td><span class="status status-agotado">Agotado</span></td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>004</td>
                        <td><img src="" alt="Producto" style="border-radius: 4px;"></td>
                        <td>Mouse Inalámbrico</td>
                        <td>Accesorios</td>
                        <td>42</td>
                        <td>$49.99</td>
                        <td><span class="status status-disponible">Disponible</span></td>
                        <td class="actions">
                            <div class="action-icon edit"><i class="fas fa-edit"></i></div>
                            <div class="action-icon delete"><i class="fas fa-trash"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>005</td>
                        <td><img src="" alt="Producto" style="border-radius: 4px;"></td>
                        <td>Impresora Multifuncional</td>
                        <td>Oficina</td>
                        <td>12</td>
                        <td>$299.99</td>
                        <td><span class="status status-disponible">Disponible</span></td>
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
    
    <!-- Modal para agregar/editar producto -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Nuevo Producto</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="productForm">
                <div class="form-group">
                    <label for="productName">Nombre del Producto</label>
                    <input type="text" class="form-control" id="productName" required>
                </div>
                <div class="form-group">
                    <label for="productCategory">Categoría</label>
                    <select class="form-control" id="productCategory" required>
                        <option value="">Seleccionar categoría</option>
                        <option value="1">Electrónicos</option>
                        <option value="2">Accesorios</option>
                        <option value="3">Oficina</option>
                        <option value="4">Mobiliario</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productStock">Stock</label>
                    <input type="number" class="form-control" id="productStock" min="0" required>
                </div>
                <div class="form-group">
                    <label for="productPrice">Precio</label>
                    <input type="number" class="form-control" id="productPrice" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="productDescription">Descripción</label>
                    <textarea class="form-control" id="productDescription" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="productImage">Imagen</label>
                    <input type="file" class="form-control" id="productImage">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelProduct">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Botón toggle para sidebar en móvil -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <script>
        // JavaScript para funcionalidad interactiva avanzada
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos del DOM
            const modal = document.getElementById('productModal');
            const btnAddProduct = document.getElementById('btnAddProduct');
            const btnCancelProduct = document.getElementById('btnCancelProduct');
            const closeModal = document.querySelector('.close-modal');
            const editButtons = document.querySelectorAll('.edit');
            const deleteButtons = document.querySelectorAll('.delete');
            const searchInput = document.querySelector('.search-bar input');
            const tableRows = document.querySelectorAll('tbody tr');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const pageItems = document.querySelectorAll('.page-item');
            
            // Configurar scroll suave
            document.documentElement.style.scrollBehavior = 'smooth';
            
            // Animaciones de entrada
            function animateOnLoad() {
                const header = document.querySelector('.header');
                const table = document.querySelector('.products-table');
                
                header.style.opacity = '0';
                header.style.transform = 'translateY(-30px)';
                table.style.opacity = '0';
                table.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    header.style.transition = 'all 0.5s ease';
                    header.style.opacity = '1';
                    header.style.transform = 'translateY(0)';
                }, 100);
                
                setTimeout(() => {
                    table.style.transition = 'all 0.6s ease';
                    table.style.opacity = '1';
                    table.style.transform = 'translateY(0)';
                }, 300);
            }
            
            // Sidebar toggle para móvil
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                this.style.transform = sidebar.classList.contains('collapsed') ? 'rotate(90deg)' : 'rotate(0deg)';
            });
            
            // Funcionalidad de búsqueda en tiempo real
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const productName = row.cells[2].textContent.toLowerCase();
                    const category = row.cells[3].textContent.toLowerCase();
                    
                    if (productName.includes(searchTerm) || category.includes(searchTerm)) {
                        row.style.display = '';
                        row.style.animation = 'fadeInUp 0.3s ease';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
            
            // Efectos hover mejorados para filas de tabla
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.01)';
                    this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
                    this.style.zIndex = '10';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = 'none';
                    this.style.zIndex = '1';
                });
            });
            
            // Modal con animaciones mejoradas
            function openModal(title = 'Agregar Nuevo Producto') {
                modal.classList.add('show');
                modal.style.display = 'flex';
                document.querySelector('.modal-title').textContent = title;
                document.body.style.overflow = 'hidden';
                
                // Animación de entrada del modal
                const modalContent = document.querySelector('.modal-content');
                modalContent.style.transform = 'scale(0.7) translateY(-50px)';
                modalContent.style.opacity = '0';
                
                setTimeout(() => {
                    modalContent.style.transition = 'all 0.3s ease';
                    modalContent.style.transform = 'scale(1) translateY(0)';
                    modalContent.style.opacity = '1';
                }, 10);
            }
            
            function closeProductModal() {
                const modalContent = document.querySelector('.modal-content');
                modalContent.style.transform = 'scale(0.7) translateY(-50px)';
                modalContent.style.opacity = '0';
                
                setTimeout(() => {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                    document.getElementById('productForm').reset();
                }, 300);
            }
            
            // Event listeners para modal
            btnAddProduct.addEventListener('click', () => {
                openModal('Agregar Nuevo Producto');
                addLoadingEffect(btnAddProduct);
            });
            
            btnCancelProduct.addEventListener('click', closeProductModal);
            closeModal.addEventListener('click', closeProductModal);
            
            // Cerrar modal con Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    closeProductModal();
                }
            });
            
            // Cerrar modal al hacer clic fuera
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeProductModal();
                }
            });
            
            // Funcionalidad de edición con carga de datos
            editButtons.forEach((button, index) => {
                button.addEventListener('click', function() {
                    addLoadingEffect(this);
                    
                    setTimeout(() => {
                        openModal('Editar Producto');
                        
                        // Simular carga de datos del producto
                        const row = this.closest('tr');
                        const productData = {
                            name: row.cells[2].textContent,
                            category: row.cells[3].textContent,
                            stock: row.cells[4].textContent,
                            price: row.cells[5].textContent.replace('$', '')
                        };
                        
                        // Llenar el formulario con los datos
                        document.getElementById('productName').value = productData.name;
                        document.getElementById('productStock').value = productData.stock;
                        document.getElementById('productPrice').value = productData.price;
                        
                        removeLoadingEffect(this);
                    }, 500);
                });
            });
            
            // Funcionalidad de eliminación con confirmación mejorada
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const productName = row.cells[2].textContent;
                    
                    if (confirm(`¿Está seguro de que desea eliminar "${productName}"?\n\nEsta acción no se puede deshacer.`)) {
                        addLoadingEffect(this);
                        
                        setTimeout(() => {
                            row.style.animation = 'fadeOut 0.5s ease';
                            
                            setTimeout(() => {
                                row.remove();
                                showNotification('Producto eliminado correctamente', 'success');
                            }, 500);
                        }, 1000);
                    }
                });
            });
            
            // Paginación interactiva
            pageItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (!this.classList.contains('active')) {
                        document.querySelector('.page-item.active').classList.remove('active');
                        this.classList.add('active');
                        
                        // Simular carga de nueva página
                        const tableContainer = document.querySelector('.products-table');
                        tableContainer.style.opacity = '0.6';
                        
                        setTimeout(() => {
                            tableContainer.style.opacity = '1';
                            showNotification('Página cargada', 'info');
                        }, 800);
                    }
                });
            });
            
            // Manejar envío del formulario con validación
            document.getElementById('productForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                addLoadingEffect(submitBtn);
                
                // Simular guardado
                setTimeout(() => {
                    removeLoadingEffect(submitBtn);
                    showNotification('Producto guardado correctamente', 'success');
                    closeProductModal();
                }, 2000);
            });
            
            // Funciones auxiliares
            function addLoadingEffect(element) {
                element.classList.add('loading');
                const originalContent = element.innerHTML;
                element.innerHTML = '<span class="spinner"></span>' + originalContent;
                element.disabled = true;
            }
            
            function removeLoadingEffect(element) {
                element.classList.remove('loading');
                const spinner = element.querySelector('.spinner');
                if (spinner) {
                    spinner.remove();
                }
                element.disabled = false;
            }
            
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                `;
                
                notification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
                    color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
                    padding: 15px 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                    z-index: 3000;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    animation: slideInRight 0.3s ease;
                    max-width: 300px;
                `;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);
            }
            
            // Validación en tiempo real de formularios
            const formInputs = document.querySelectorAll('.form-control');
            formInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        validateField(this);
                    }
                });
            });
            
            function validateField(field) {
                const value = field.value.trim();
                let isValid = true;
                
                if (field.hasAttribute('required') && !value) {
                    isValid = false;
                }
                
                if (field.type === 'number' && value && (isNaN(value) || parseFloat(value) < 0)) {
                    isValid = false;
                }
                
                if (isValid) {
                    field.classList.remove('error');
                    field.style.borderColor = '#28a745';
                } else {
                    field.classList.add('error');
                    field.style.borderColor = '#dc3545';
                }
                
                return isValid;
            }
            
            // Agregar estilos CSS para animaciones adicionales
            const additionalStyles = `
                @keyframes fadeOut {
                    from { opacity: 1; transform: scale(1); }
                    to { opacity: 0; transform: scale(0.8); }
                }
                
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
                
                .form-control.error {
                    border-color: #dc3545 !important;
                    box-shadow: 0 0 5px rgba(220, 53, 69, 0.3) !important;
                }
            `;
            
            const styleSheet = document.createElement('style');
            styleSheet.textContent = additionalStyles;
            document.head.appendChild(styleSheet);
            
            // Inicializar animaciones
            animateOnLoad();
            
            // Smooth scroll para navegación
            document.querySelectorAll('.menu-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    if (this.getAttribute('href').startsWith('#')) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>