/* ====================================
   PANEL DE ADMINISTRACIÓN - JAVASCRIPT
   Gestión de productos, pedidos y envíos
   ==================================== */

// Configuración
const API_BASE = '/database/api';
let adminToken = '';
let siteDomain = '';
let currentProducts = [];
let currentOrders = [];

// ====================================
// INICIALIZACIÓN
// ====================================

document.addEventListener('DOMContentLoaded', () => {
    // Obtener parámetros
    const urlParams = new URLSearchParams(window.location.search);
    siteDomain = urlParams.get('site') || window.location.hostname;
    
    // Verificar token guardado
    adminToken = localStorage.getItem('adminToken');
    
    if (!adminToken) {
        showLoginPrompt();
    } else {
        initDashboard();
    }
});

// ====================================
// LOGIN
// ====================================

function showLoginPrompt() {
    const token = prompt('Ingresa tu token de administrador:');
    if (token) {
        adminToken = token;
        localStorage.setItem('adminToken', token);
        initDashboard();
    } else {
        document.body.innerHTML = '<div style="padding: 2rem; text-align: center;"><h2>Token requerido</h2><button onclick="location.reload()">Reintentar</button></div>';
    }
}

function logout() {
    if (confirm('¿Cerrar sesión?')) {
        localStorage.removeItem('adminToken');
        location.reload();
    }
}

// ====================================
// INICIALIZAR DASHBOARD
// ====================================

async function initDashboard() {
    try {
        // Cargar datos iniciales
        await loadProducts();
        await loadOrders();
        
        // Mostrar sección de productos por defecto
        showSection('products');
    } catch (error) {
        console.error('Error al inicializar:', error);
        alert('Error al cargar el dashboard. Verifica tu token.');
        localStorage.removeItem('adminToken');
        location.reload();
    }
}

// ====================================
// NAVEGACIÓN
// ====================================

function showSection(sectionName) {
    // Ocultar todas las secciones
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Mostrar sección seleccionada
    const section = document.getElementById(`section-${sectionName}`);
    if (section) {
        section.classList.add('active');
    }
    
    // Actualizar navegación
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    event?.target.classList.add('active');
    
    // Actualizar título
    const titles = {
        products: 'Gestión de Productos',
        orders: 'Gestión de Pedidos',
        shipping: 'Seguimiento de Envíos',
        settings: 'Configuración del Sitio'
    };
    document.getElementById('sectionTitle').textContent = titles[sectionName] || '';
    
    // Cargar datos según sección
    if (sectionName === 'shipping') {
        loadShipping();
    }
}

// ====================================
// PRODUCTOS
// ====================================

async function loadProducts() {
    try {
        const response = await fetch(`${API_BASE}/products.php?domain=${siteDomain}&active=1`);
        const data = await response.json();
        
        if (data.success) {
            currentProducts = data.data.products;
            renderProducts();
            updateProductStats();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error cargando productos:', error);
        document.getElementById('productsTableBody').innerHTML = 
            '<tr><td colspan="7" class="loading">Error al cargar productos</td></tr>';
    }
}

function renderProducts() {
    const tbody = document.getElementById('productsTableBody');
    
    if (currentProducts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="loading">No hay productos. ¡Agrega el primero!</td></tr>';
        return;
    }
    
    tbody.innerHTML = currentProducts.map(product => `
        <tr>
            <td>
                <img src="${product.image_url || 'https://via.placeholder.com/60'}" 
                     alt="${product.name}" 
                     class="product-img"
                     onerror="this.src='https://via.placeholder.com/60'">
            </td>
            <td><strong>${product.name}</strong></td>
            <td>${product.category || '-'}</td>
            <td>$${parseFloat(product.price).toLocaleString('es-AR', {minimumFractionDigits: 2})}</td>
            <td>${product.stock || 0}</td>
            <td>
                <span class="badge ${product.active ? 'badge-success' : 'badge-danger'}">
                    ${product.active ? 'Activo' : 'Inactivo'}
                </span>
                ${product.badge ? `<span class="badge badge-info">${product.badge}</span>` : ''}
            </td>
            <td>
                <button class="btn-icon" onclick="editProduct(${product.id})" title="Editar">✏️</button>
                <button class="btn-icon" onclick="deleteProduct(${product.id})" title="Eliminar">🗑️</button>
            </td>
        </tr>
    `).join('');
}

function updateProductStats() {
    const total = currentProducts.length;
    const active = currentProducts.filter(p => p.active).length;
    const outOfStock = currentProducts.filter(p => p.stock === 0 || p.stock === '0').length;
    
    document.getElementById('totalProducts').textContent = total;
    document.getElementById('activeProducts').textContent = active;
    document.getElementById('outOfStock').textContent = outOfStock;
}

// ====================================
// MODAL PRODUCTO
// ====================================

function showAddProductModal() {
    document.getElementById('productModalTitle').textContent = 'Agregar Producto';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('productModal').classList.add('active');
}

function editProduct(productId) {
    const product = currentProducts.find(p => p.id == productId);
    if (!product) return;
    
    document.getElementById('productModalTitle').textContent = 'Editar Producto';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productDescription').value = product.description || '';
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productStock').value = product.stock || 0;
    document.getElementById('productCategory').value = product.category || '';
    document.getElementById('productBadge').value = product.badge || '';
    document.getElementById('productImage').value = product.image_url || '';
    
    document.getElementById('productModal').classList.add('active');
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('active');
}

async function saveProduct(event) {
    event.preventDefault();
    
    const productId = document.getElementById('productId').value;
    const productData = {
        admin_token: adminToken,
        name: document.getElementById('productName').value,
        description: document.getElementById('productDescription').value,
        price: parseFloat(document.getElementById('productPrice').value),
        stock: parseInt(document.getElementById('productStock').value),
        category: document.getElementById('productCategory').value,
        badge: document.getElementById('productBadge').value,
        image_url: document.getElementById('productImage').value
    };
    
    try {
        let url = `${API_BASE}/products.php?domain=${siteDomain}`;
        let method = 'POST';
        
        if (productId) {
            // Editar
            url += `&id=${productId}`;
            method = 'PUT';
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(productData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Producto guardado exitosamente', 'success');
            closeProductModal();
            await loadProducts();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error guardando producto:', error);
        showNotification('Error al guardar producto: ' + error.message, 'error');
    }
}

async function deleteProduct(productId) {
    if (!confirm('¿Eliminar este producto? Se ocultará de la tienda pero los pedidos históricos lo seguirán mostrando.')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/products.php?domain=${siteDomain}&id=${productId}&admin_token=${adminToken}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Producto eliminado', 'success');
            await loadProducts();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error eliminando producto:', error);
        showNotification('Error al eliminar producto', 'error');
    }
}

// ====================================
// PEDIDOS
// ====================================

async function loadOrders() {
    try {
        const response = await fetch(`${API_BASE}/orders.php?domain=${siteDomain}&admin_token=${adminToken}&all=1`);
        const data = await response.json();
        
        if (data.success) {
            currentOrders = data.data.orders || [];
            renderOrders();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error cargando pedidos:', error);
        document.getElementById('ordersTableBody').innerHTML = 
            '<tr><td colspan="6" class="loading">Error al cargar pedidos</td></tr>';
    }
}

function renderOrders(filter = 'all') {
    const tbody = document.getElementById('ordersTableBody');
    
    let orders = currentOrders;
    if (filter !== 'all') {
        orders = orders.filter(o => o.status === filter);
    }
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="loading">No hay pedidos</td></tr>';
        return;
    }
    
    tbody.innerHTML = orders.map(order => `
        <tr>
            <td><strong>${order.order_number}</strong></td>
            <td>${order.guest_name || 'Usuario registrado'}</td>
            <td>${formatDate(order.created_at)}</td>
            <td><strong>$${parseFloat(order.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</strong></td>
            <td>${getStatusBadge(order.status)}</td>
            <td>
                <button class="btn-icon" onclick="viewOrder(${order.id})" title="Ver detalles">👁️</button>
                <button class="btn-icon" onclick="updateOrderStatus(${order.id})" title="Actualizar">✏️</button>
            </td>
        </tr>
    `).join('');
}

function filterOrders() {
    const filter = document.getElementById('orderStatusFilter').value;
    renderOrders(filter);
}

function getStatusBadge(status) {
    const badges = {
        pending: '<span class="badge badge-warning">🟡 Pendiente</span>',
        confirmed: '<span class="badge badge-info">🔵 Confirmado</span>',
        shipped: '<span class="badge badge-info">📦 Enviado</span>',
        delivered: '<span class="badge badge-success">✅ Entregado</span>',
        cancelled: '<span class="badge badge-danger">❌ Cancelado</span>'
    };
    return badges[status] || status;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    
    if (date.toDateString() === today.toDateString()) {
        return 'Hoy ' + date.toLocaleTimeString('es-AR', {hour: '2-digit', minute: '2-digit'});
    } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Ayer';
    } else {
        return date.toLocaleDateString('es-AR');
    }
}

async function viewOrder(orderId) {
    const order = currentOrders.find(o => o.id == orderId);
    if (!order) return;
    
    const detailsHTML = `
        <div style="padding: 1.5rem;">
            <h3>Pedido ${order.order_number}</h3>
            <hr>
            
            <h4>Cliente</h4>
            <p><strong>Nombre:</strong> ${order.guest_name || 'Usuario registrado'}</p>
            <p><strong>Email:</strong> ${order.guest_email || '-'}</p>
            <p><strong>Teléfono:</strong> ${order.guest_phone || '-'}</p>
            
            <h4>Dirección de Envío</h4>
            <p>${order.shipping_address || 'No especificada'}</p>
            <p>${order.shipping_city || ''} ${order.shipping_state || ''} ${order.shipping_zip || ''}</p>
            
            <h4>Productos</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${(order.items || []).map(item => `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>$${parseFloat(item.product_price).toLocaleString('es-AR', {minimumFractionDigits: 2})}</td>
                            <td>${item.quantity}</td>
                            <td><strong>$${parseFloat(item.subtotal).toLocaleString('es-AR', {minimumFractionDigits: 2})}</strong></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            
            <h3 style="text-align: right; margin-top: 1rem;">Total: $${parseFloat(order.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</h3>
            
            <p><strong>Estado:</strong> ${getStatusBadge(order.status)}</p>
            ${order.customer_notes ? `<p><strong>Notas del cliente:</strong> ${order.customer_notes}</p>` : ''}
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button class="btn btn-primary" onclick="markAsShipped(${order.id})">📦 Marcar como Enviado</button>
                <button class="btn btn-secondary" onclick="closeOrderModal()">Cerrar</button>
            </div>
        </div>
    `;
    
    document.getElementById('orderDetails').innerHTML = detailsHTML;
    document.getElementById('orderModal').classList.add('active');
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('active');
}

function markAsShipped(orderId) {
    closeOrderModal();
    // Aquí se abrirá el formulario de envío
    showShippingForm(orderId);
}

// ====================================
// ENVÍOS
// ====================================

async function loadShipping() {
    const container = document.getElementById('shippingList');
    container.innerHTML = '<p class="loading">Cargando envíos...</p>';
    
    try {
        const response = await fetch(`${API_BASE}/shipping.php?domain=${siteDomain}&admin_token=${adminToken}`);
        const data = await response.json();
        
        if (data.success) {
            renderShipping(data.data.shipments || []);
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error cargando envíos:', error);
        container.innerHTML = '<p class="loading">Error al cargar envíos</p>';
    }
}

function renderShipping(shipments) {
    const container = document.getElementById('shippingList');
    
    if (shipments.length === 0) {
        container.innerHTML = '<p class="loading">No hay envíos activos</p>';
        return;
    }
    
    container.innerHTML = shipments.map(shipment => `
        <div class="shipping-card">
            <div class="shipping-header">
                <div>
                    <h3>Pedido ${shipment.order_number}</h3>
                    <p>${shipment.customer_name}</p>
                </div>
                <div>${getStatusBadge(shipment.status)}</div>
            </div>
            <p><strong>${shipment.carrier}</strong> - ${shipment.tracking_number}</p>
            ${shipment.tracking_url ? `<p><a href="${shipment.tracking_url}" target="_blank">🔍 Rastrear envío</a></p>` : ''}
            <p>Entrega estimada: ${formatDate(shipment.estimated_delivery)}</p>
            
            <div class="shipping-timeline">
                <h4>Timeline</h4>
                ${(shipment.events || []).map(event => `
                    <div class="timeline-event">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">${formatDate(event.event_date)}</div>
                            <div class="timeline-description">${event.description}</div>
                            ${event.location ? `<small>${event.location}</small>` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
            
            <button class="btn btn-small btn-primary" onclick="addShippingEvent(${shipment.id})">
                ➕ Agregar Actualización
            </button>
        </div>
    `).join('');
}

function showShippingForm(orderId) {
    const order = currentOrders.find(o => o.id == orderId);
    if (!order) return;
    
    const html = `
        <div style="padding: 1.5rem;">
            <h3>Marcar Pedido como Enviado</h3>
            <p>Pedido: <strong>${order.order_number}</strong></p>
            <hr>
            
            <form id="shippingForm" onsubmit="submitShipping(event, ${orderId})">
                <div class="form-group">
                    <label>Empresa de Envío *</label>
                    <select id="carrier" required>
                        <option value="">Seleccionar...</option>
                        <option value="Correo Argentino">Correo Argentino</option>
                        <option value="Andreani">Andreani</option>
                        <option value="OCA">OCA</option>
                        <option value="MercadoEnvíos">MercadoEnvíos</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Número de Seguimiento *</label>
                    <input type="text" id="trackingNumber" required placeholder="RA123456789AR">
                </div>
                
                <div class="form-group">
                    <label>URL de Tracking (opcional)</label>
                    <input type="url" id="trackingUrl" placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label>Fecha Estimada de Entrega</label>
                    <input type="date" id="estimatedDelivery" required>
                </div>
                
                <div class="form-group">
                    <label>Notas (opcional)</label>
                    <textarea id="shippingNotes" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeOrderModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar y Notificar Cliente</button>
                </div>
            </form>
        </div>
    `;
    
    document.getElementById('orderDetails').innerHTML = html;
    document.getElementById('orderModal').classList.add('active');
}

async function submitShipping(event, orderId) {
    event.preventDefault();
    
    const shippingData = {
        admin_token: adminToken,
        order_id: orderId,
        carrier: document.getElementById('carrier').value,
        tracking_number: document.getElementById('trackingNumber').value,
        tracking_url: document.getElementById('trackingUrl').value,
        estimated_delivery: document.getElementById('estimatedDelivery').value,
        notes: document.getElementById('shippingNotes').value
    };
    
    try {
        const response = await fetch(`${API_BASE}/shipping.php?domain=${siteDomain}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(shippingData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Envío registrado. Cliente notificado por email.', 'success');
            closeOrderModal();
            await loadOrders();
            await loadShipping();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error registrando envío:', error);
        showNotification('Error al registrar envío: ' + error.message, 'error');
    }
}

// ====================================
// CONFIGURACIÓN
// ====================================

function showToken() {
    const tokenElement = document.getElementById('adminToken');
    if (tokenElement.textContent.includes('•')) {
        tokenElement.textContent = adminToken;
        event.target.textContent = '🔒 Ocultar';
    } else {
        tokenElement.textContent = '••••••••••••••••••••';
        event.target.textContent = '👁️ Mostrar';
    }
}

// ====================================
// NOTIFICACIONES
// ====================================

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#48bb78' : type === 'error' ? '#f56565' : '#4299e1'};
        color: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Agregar estilos de animación
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);
