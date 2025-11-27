/* ====================================
   SISTEMA DE AUTENTICACIÓN CON MYSQL
   Reemplaza auth.js (LocalStorage) por APIs REST
   ==================================== */

// Configuración
const API_BASE = '/database/api';
const SITE_DOMAIN = window.location.hostname;

// Estado global
let currentUser = null;
let authToken = null;

// ====================================
// INICIALIZACIÓN
// ====================================

document.addEventListener('DOMContentLoaded', () => {
    // Verificar sesión existente
    authToken = localStorage.getItem('authToken');
    if (authToken) {
        verifySession();
    }
    
    // Event listeners
    setupEventListeners();
});

function setupEventListeners() {
    // Modales
    document.getElementById('loginBtn')?.addEventListener('click', () => openModal('loginModal'));
    document.getElementById('registerBtn')?.addEventListener('click', () => openModal('registerModal'));
    document.getElementById('userProfileBtn')?.addEventListener('click', () => openModal('profileModal'));
    document.getElementById('logoutBtn')?.addEventListener('click', logout);
    
    // Cerrar modales
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', (e) => {
            closeModal(e.target.closest('.modal').id);
        });
    });
    
    // Formularios
    document.getElementById('loginForm')?.addEventListener('submit', handleLogin);
    document.getElementById('registerForm')?.addEventListener('submit', handleRegister);
    document.getElementById('profileEditForm')?.addEventListener('submit', handleProfileUpdate);
    
    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const targetTab = e.target.dataset.tab;
            switchTab(targetTab);
        });
    });
}

// ====================================
// VERIFICAR SESIÓN
// ====================================

async function verifySession() {
    try {
        const response = await fetch(`${API_BASE}/auth.php?domain=${SITE_DOMAIN}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'verify',
                token: authToken
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUser = data.data.user;
            updateUILoggedIn();
            loadUserOrders();
        } else {
            // Token inválido o expirado
            logout();
        }
    } catch (error) {
        console.error('Error verificando sesión:', error);
        logout();
    }
}

// ====================================
// LOGIN
// ====================================

async function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    showLoading('loginBtn', true);
    
    try {
        const response = await fetch(`${API_BASE}/auth.php?domain=${SITE_DOMAIN}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'login',
                email: email,
                password: password
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            authToken = data.data.token;
            currentUser = data.data.user;
            
            localStorage.setItem('authToken', authToken);
            
            showNotification('¡Bienvenido de vuelta!', 'success');
            closeModal('loginModal');
            updateUILoggedIn();
            loadUserOrders();
            
            // Limpiar formulario
            document.getElementById('loginForm').reset();
        } else {
            showNotification(data.error || 'Error al iniciar sesión', 'error');
        }
    } catch (error) {
        console.error('Error en login:', error);
        showNotification('Error de conexión. Intenta nuevamente.', 'error');
    } finally {
        showLoading('loginBtn', false);
    }
}

// ====================================
// REGISTRO
// ====================================

async function handleRegister(event) {
    event.preventDefault();
    
    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('registerConfirmPassword').value;
    
    // Validación de contraseñas
    if (password !== confirmPassword) {
        showNotification('Las contraseñas no coinciden', 'error');
        return;
    }
    
    if (password.length < 6) {
        showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    showLoading('registerBtn', true);
    
    try {
        const response = await fetch(`${API_BASE}/auth.php?domain=${SITE_DOMAIN}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'register',
                name: name,
                email: email,
                password: password
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            authToken = data.data.token;
            currentUser = data.data.user;
            
            localStorage.setItem('authToken', authToken);
            
            showNotification('¡Cuenta creada exitosamente!', 'success');
            closeModal('registerModal');
            updateUILoggedIn();
            
            // Limpiar formulario
            document.getElementById('registerForm').reset();
        } else {
            showNotification(data.error || 'Error al crear cuenta', 'error');
        }
    } catch (error) {
        console.error('Error en registro:', error);
        showNotification('Error de conexión. Intenta nuevamente.', 'error');
    } finally {
        showLoading('registerBtn', false);
    }
}

// ====================================
// LOGOUT
// ====================================

async function logout() {
    if (authToken) {
        try {
            await fetch(`${API_BASE}/auth.php?domain=${SITE_DOMAIN}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'logout',
                    token: authToken
                })
            });
        } catch (error) {
            console.error('Error en logout:', error);
        }
    }
    
    // Limpiar estado
    authToken = null;
    currentUser = null;
    localStorage.removeItem('authToken');
    
    updateUILoggedOut();
    showNotification('Sesión cerrada', 'info');
}

// ====================================
// PERFIL
// ====================================

async function handleProfileUpdate(event) {
    event.preventDefault();
    
    const name = document.getElementById('profileName').value;
    const phone = document.getElementById('profilePhone').value;
    const address = document.getElementById('profileAddress').value;
    
    showLoading('profileSaveBtn', true);
    
    try {
        const response = await fetch(`${API_BASE}/profile.php`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                token: authToken,
                name: name,
                phone: phone,
                address: address
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUser = data.data;
            updateUILoggedIn();
            showNotification('Perfil actualizado', 'success');
            switchTab('profile-view');
        } else {
            showNotification(data.error || 'Error al actualizar perfil', 'error');
        }
    } catch (error) {
        console.error('Error actualizando perfil:', error);
        showNotification('Error de conexión', 'error');
    } finally {
        showLoading('profileSaveBtn', false);
    }
}

// ====================================
// PEDIDOS
// ====================================

async function loadUserOrders() {
    const container = document.getElementById('ordersHistoryList');
    if (!container) return;
    
    container.innerHTML = '<div class="loading">Cargando pedidos...</div>';
    
    try {
        const response = await fetch(`${API_BASE}/orders.php?domain=${SITE_DOMAIN}&token=${authToken}`);
        const data = await response.json();
        
        if (data.success) {
            const orders = data.data.orders || [];
            renderOrders(orders);
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error cargando pedidos:', error);
        container.innerHTML = '<div class="empty-state">Error al cargar pedidos</div>';
    }
}

function renderOrders(orders) {
    const container = document.getElementById('ordersHistoryList');
    
    if (orders.length === 0) {
        container.innerHTML = '<div class="empty-state">No tienes pedidos aún. ¡Realiza tu primera compra!</div>';
        return;
    }
    
    container.innerHTML = orders.map(order => `
        <div class="order-card">
            <div class="order-header">
                <div>
                    <strong>Pedido ${order.order_number}</strong>
                    <span class="order-date">${formatDate(order.created_at)}</span>
                </div>
                <div>
                    <span class="order-status status-${order.status}">
                        ${getStatusLabel(order.status)}
                    </span>
                    <strong class="order-total">$${parseFloat(order.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</strong>
                </div>
            </div>
            <div class="order-items">
                ${(order.items || []).map(item => `
                    <div class="order-item">
                        <span>${item.product_name} x${item.quantity}</span>
                        <span>$${parseFloat(item.subtotal).toLocaleString('es-AR', {minimumFractionDigits: 2})}</span>
                    </div>
                `).join('')}
            </div>
            ${order.shipping_address ? `
                <div class="order-address">
                    <strong>Envío a:</strong> ${order.shipping_address}
                </div>
            ` : ''}
        </div>
    `).join('');
}

// ====================================
// CREAR PEDIDO (desde carrito)
// ====================================

async function createOrder(orderData) {
    if (!authToken) {
        // Si no está logueado, pedido como invitado
        return createGuestOrder(orderData);
    }
    
    try {
        const response = await fetch(`${API_BASE}/orders.php?domain=${SITE_DOMAIN}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                token: authToken,
                items: orderData.items,
                total: orderData.total,
                shipping_address: orderData.shipping_address,
                shipping_city: orderData.shipping_city,
                shipping_state: orderData.shipping_state,
                shipping_zip: orderData.shipping_zip,
                customer_notes: orderData.customer_notes
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('¡Pedido creado exitosamente!', 'success');
            loadUserOrders(); // Recargar pedidos
            return data.data.order_id;
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error creando pedido:', error);
        showNotification('Error al crear pedido', 'error');
        return null;
    }
}

async function createGuestOrder(orderData) {
    try {
        const response = await fetch(`${API_BASE}/orders.php?domain=${SITE_DOMAIN}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                items: orderData.items,
                total: orderData.total,
                guest_name: orderData.guest_name,
                guest_email: orderData.guest_email,
                guest_phone: orderData.guest_phone,
                shipping_address: orderData.shipping_address,
                shipping_city: orderData.shipping_city,
                shipping_state: orderData.shipping_state,
                shipping_zip: orderData.shipping_zip,
                customer_notes: orderData.customer_notes
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('¡Pedido creado exitosamente!', 'success');
            return data.data.order_id;
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error creando pedido:', error);
        showNotification('Error al crear pedido', 'error');
        return null;
    }
}

// ====================================
// UI HELPERS
// ====================================

function updateUILoggedIn() {
    // Ocultar botones de login/registro
    document.getElementById('authButtons')?.classList.add('hidden');
    
    // Mostrar área de usuario
    const userArea = document.getElementById('userArea');
    if (userArea) {
        userArea.classList.remove('hidden');
        document.getElementById('userName').textContent = currentUser.name;
    }
    
    // Actualizar datos de perfil
    document.getElementById('profileName')?.setAttribute('value', currentUser.name);
    document.getElementById('profileEmail')?.setAttribute('value', currentUser.email);
    document.getElementById('profilePhone')?.setAttribute('value', currentUser.phone || '');
    document.getElementById('profileAddress')?.setAttribute('value', currentUser.address || '');
    
    // Actualizar vista de perfil
    document.getElementById('profileViewName')?.textContent = currentUser.name;
    document.getElementById('profileViewEmail')?.textContent = currentUser.email;
    document.getElementById('profileViewPhone')?.textContent = currentUser.phone || 'No especificado';
    document.getElementById('profileViewAddress')?.textContent = currentUser.address || 'No especificada';
}

function updateUILoggedOut() {
    // Mostrar botones de login/registro
    document.getElementById('authButtons')?.classList.remove('hidden');
    
    // Ocultar área de usuario
    document.getElementById('userArea')?.classList.add('hidden');
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function switchTab(tabName) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Mostrar tab seleccionado
    document.getElementById(tabName)?.classList.add('active');
    
    // Actualizar botones
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.tab === tabName) {
            btn.classList.add('active');
        }
    });
}

function showLoading(buttonId, loading) {
    const button = document.getElementById(buttonId);
    if (!button) return;
    
    if (loading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Cargando...';
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || button.textContent;
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? 'var(--color-success)' : type === 'error' ? 'var(--color-danger)' : 'var(--color-info)'};
        color: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease;
        max-width: 400px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-AR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function getStatusLabel(status) {
    const labels = {
        pending: '🟡 Pendiente',
        confirmed: '🔵 Confirmado',
        shipped: '📦 Enviado',
        delivered: '✅ Entregado',
        cancelled: '❌ Cancelado'
    };
    return labels[status] || status;
}

// ====================================
// EXPORTAR FUNCIONES GLOBALES
// ====================================

window.authAPI = {
    isLoggedIn: () => !!authToken,
    getCurrentUser: () => currentUser,
    createOrder: createOrder,
    logout: logout
};
