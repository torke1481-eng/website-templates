/* ====================================
   SISTEMA DE AUTENTICACIÓN
   Frontend-only con LocalStorage
   ==================================== */

// Estado de autenticación
let currentUser = null;

// ====================================
// INICIALIZACIÓN
// ====================================

document.addEventListener('DOMContentLoaded', function() {
    checkAuth();
    loadOrderHistory();
});

// ====================================
// VERIFICAR AUTENTICACIÓN
// ====================================

function checkAuth() {
    const userSession = localStorage.getItem('userSession');
    
    if (userSession) {
        currentUser = JSON.parse(userSession);
        updateUIForLoggedInUser();
    } else {
        updateUIForLoggedOutUser();
    }
}

function updateUIForLoggedInUser() {
    // Ocultar botón de login
    document.getElementById('btnLogin').style.display = 'none';
    
    // Mostrar dropdown de usuario
    document.getElementById('userDropdown').style.display = 'block';
    
    // Actualizar iniciales
    const initials = getInitials(currentUser.name);
    document.getElementById('userInitials').textContent = initials;
    
    // Actualizar info en dropdown
    document.getElementById('userName').textContent = currentUser.name;
    document.getElementById('userEmail').textContent = currentUser.email;
    
    // Mostrar prompt de login en carrito si no está logueado
    const loginPrompt = document.getElementById('loginPrompt');
    if (loginPrompt) {
        loginPrompt.style.display = 'none';
    }
}

function updateUIForLoggedOutUser() {
    // Mostrar botón de login
    document.getElementById('btnLogin').style.display = 'block';
    
    // Ocultar dropdown
    document.getElementById('userDropdown').style.display = 'none';
    
    // Mostrar prompt de login en carrito
    const loginPrompt = document.getElementById('loginPrompt');
    if (loginPrompt) {
        loginPrompt.style.display = 'block';
    }
}

function getInitials(name) {
    const names = name.trim().split(' ');
    if (names.length >= 2) {
        return (names[0][0] + names[1][0]).toUpperCase();
    }
    return names[0][0].toUpperCase();
}

// ====================================
// MODAL DE AUTENTICACIÓN
// ====================================

function showLoginModal() {
    const modal = document.getElementById('authModal');
    modal.classList.add('active');
    showLoginTab();
}

function showRegisterModal() {
    const modal = document.getElementById('authModal');
    modal.classList.add('active');
    showRegisterTab();
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    modal.classList.remove('active');
}

function showLoginTab() {
    // Update tabs
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Show login form
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('registerForm').style.display = 'none';
}

function showRegisterTab() {
    // Update tabs
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Show register form
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
}

// ====================================
// LOGIN
// ====================================

function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    // Buscar usuario en localStorage
    const users = JSON.parse(localStorage.getItem('users') || '[]');
    const user = users.find(u => u.email === email && u.password === password);
    
    if (user) {
        // Login exitoso
        const userSession = {
            id: user.id,
            name: user.name,
            email: user.email,
            phone: user.phone || '',
            address: user.address || ''
        };
        
        localStorage.setItem('userSession', JSON.stringify(userSession));
        currentUser = userSession;
        
        updateUIForLoggedInUser();
        closeAuthModal();
        showNotification('¡Bienvenido de vuelta! 👋');
        
        // Limpiar form
        event.target.reset();
    } else {
        showNotification('Email o contraseña incorrectos ❌', 'error');
    }
}

// ====================================
// REGISTRO
// ====================================

function handleRegister(event) {
    event.preventDefault();
    
    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const passwordConfirm = document.getElementById('registerPasswordConfirm').value;
    
    // Validar que las contraseñas coincidan
    if (password !== passwordConfirm) {
        showNotification('Las contraseñas no coinciden ❌', 'error');
        return;
    }
    
    // Verificar que el email no esté registrado
    const users = JSON.parse(localStorage.getItem('users') || '[]');
    const existingUser = users.find(u => u.email === email);
    
    if (existingUser) {
        showNotification('Este email ya está registrado ❌', 'error');
        return;
    }
    
    // Crear nuevo usuario
    const newUser = {
        id: Date.now().toString(),
        name: name,
        email: email,
        password: password,
        createdAt: new Date().toISOString(),
        phone: '',
        address: ''
    };
    
    users.push(newUser);
    localStorage.setItem('users', JSON.stringify(users));
    
    // Login automático
    const userSession = {
        id: newUser.id,
        name: newUser.name,
        email: newUser.email,
        phone: '',
        address: ''
    };
    
    localStorage.setItem('userSession', JSON.stringify(userSession));
    currentUser = userSession;
    
    updateUIForLoggedInUser();
    closeAuthModal();
    showNotification('¡Cuenta creada exitosamente! 🎉');
    
    // Limpiar form
    event.target.reset();
}

// ====================================
// LOGOUT
// ====================================

function logout() {
    if (confirm('¿Seguro que quieres cerrar sesión?')) {
        localStorage.removeItem('userSession');
        currentUser = null;
        updateUIForLoggedOutUser();
        toggleUserMenu(); // Cerrar dropdown
        showNotification('Sesión cerrada. ¡Hasta pronto! 👋');
    }
}

// ====================================
// TOGGLE USER MENU
// ====================================

function toggleUserMenu() {
    const menu = document.getElementById('dropdownMenu');
    menu.classList.toggle('active');
}

// Cerrar dropdown al hacer click fuera
document.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-dropdown');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    if (userMenu && !userMenu.contains(event.target)) {
        dropdownMenu.classList.remove('active');
    }
});

// ====================================
// PERFIL DE USUARIO
// ====================================

function showUserProfile() {
    if (!currentUser) {
        showLoginModal();
        return;
    }
    
    const modal = document.getElementById('profileModal');
    modal.classList.add('active');
    
    // Llenar datos del perfil
    const initials = getInitials(currentUser.name);
    document.getElementById('profileInitials').textContent = initials;
    document.getElementById('profileName').textContent = currentUser.name;
    document.getElementById('profileEmail').textContent = currentUser.email;
    document.getElementById('profileNameInput').value = currentUser.name;
    document.getElementById('profilePhone').value = currentUser.phone || '';
    document.getElementById('profileAddress').value = currentUser.address || '';
    
    // Cerrar dropdown
    toggleUserMenu();
}

function closeProfileModal() {
    const modal = document.getElementById('profileModal');
    modal.classList.remove('active');
}

function updateProfile(event) {
    event.preventDefault();
    
    const name = document.getElementById('profileNameInput').value;
    const phone = document.getElementById('profilePhone').value;
    const address = document.getElementById('profileAddress').value;
    
    // Actualizar currentUser
    currentUser.name = name;
    currentUser.phone = phone;
    currentUser.address = address;
    
    // Actualizar en localStorage
    localStorage.setItem('userSession', JSON.stringify(currentUser));
    
    // También actualizar en la lista de usuarios
    const users = JSON.parse(localStorage.getItem('users') || '[]');
    const userIndex = users.findIndex(u => u.id === currentUser.id);
    if (userIndex !== -1) {
        users[userIndex] = { ...users[userIndex], ...currentUser };
        localStorage.setItem('users', JSON.stringify(users));
    }
    
    // Actualizar UI
    updateUIForLoggedInUser();
    document.getElementById('profileName').textContent = name;
    
    showNotification('Perfil actualizado correctamente ✅');
}

// ====================================
// HISTORIAL DE PEDIDOS
// ====================================

function showOrderHistory() {
    if (!currentUser) {
        showLoginModal();
        return;
    }
    
    const modal = document.getElementById('ordersModal');
    modal.classList.add('active');
    
    renderOrderHistory();
    
    // Cerrar dropdown
    toggleUserMenu();
}

function closeOrdersModal() {
    const modal = document.getElementById('ordersModal');
    modal.classList.remove('active');
}

function loadOrderHistory() {
    // Los pedidos se cargan cuando se muestra el modal
}

function renderOrderHistory() {
    const ordersList = document.getElementById('ordersList');
    
    if (!currentUser) {
        ordersList.innerHTML = '<p class="empty-message">Debes iniciar sesión para ver tus pedidos</p>';
        return;
    }
    
    // Obtener pedidos del usuario
    const allOrders = JSON.parse(localStorage.getItem('orders') || '[]');
    const userOrders = allOrders.filter(o => o.userId === currentUser.id);
    
    if (userOrders.length === 0) {
        ordersList.innerHTML = '<p class="empty-message">Aún no has realizado ningún pedido</p>';
        return;
    }
    
    // Renderizar pedidos
    ordersList.innerHTML = userOrders.reverse().map(order => `
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-number">Pedido #${order.id}</div>
                    <div class="order-date">${new Date(order.date).toLocaleDateString('es-ES')}</div>
                </div>
                <div class="order-status ${order.status}">${getStatusText(order.status)}</div>
            </div>
            <div class="order-items">
                ${order.items.map(item => `
                    <div class="order-item">
                        <span>${item.name} x${item.quantity}</span>
                        <span>$${(item.price * item.quantity).toFixed(2)}</span>
                    </div>
                `).join('')}
            </div>
            <div class="order-total">
                <span>Total:</span>
                <span>$${order.total.toFixed(2)}</span>
            </div>
        </div>
    `).join('');
}

function getStatusText(status) {
    const statusTexts = {
        'pending': 'Pendiente',
        'completed': 'Completado',
        'cancelled': 'Cancelado'
    };
    return statusTexts[status] || status;
}

// ====================================
// CHECKOUT CON AUTENTICACIÓN
// ====================================

function proceedToCheckout() {
    if (cart.length === 0) {
        showNotification('Tu carrito está vacío ❌', 'error');
        return;
    }
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    // Si está logueado, guardar el pedido
    if (currentUser) {
        saveOrder(cart, total);
    }
    
    // Continuar con checkout normal (WhatsApp/Email)
    checkout();
}

function saveOrder(items, total) {
    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    
    const newOrder = {
        id: Date.now().toString().slice(-6),
        userId: currentUser.id,
        items: items.map(item => ({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: item.quantity
        })),
        total: total,
        date: new Date().toISOString(),
        status: 'pending'
    };
    
    orders.push(newOrder);
    localStorage.setItem('orders', JSON.stringify(orders));
    
    showNotification('Pedido guardado en tu historial 📦');
}

// ====================================
// NOTIFICACIONES
// ====================================

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${type === 'error' ? '#f56565' : '#48bb78'};
        color: white;
        padding: 1rem 2rem;
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

// Cerrar modales con ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAuthModal();
        closeProfileModal();
        closeOrdersModal();
    }
});
