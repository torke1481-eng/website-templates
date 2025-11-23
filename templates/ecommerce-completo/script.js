/* ====================================
   E-COMMERCE - JAVASCRIPT
   Funcionalidad de carrito y filtros
   ==================================== */

// Estado del carrito
let cart = [];

// Productos (se cargarán desde config.json o serán generados por IA)
let products = [
    {
        id: 1,
        name: "{{PRODUCT_1_NAME}}",
        description: "{{PRODUCT_1_DESCRIPTION}}",
        price: {{PRODUCT_1_PRICE}},
        image: "{{PRODUCT_1_IMAGE}}",
        category: "{{PRODUCT_1_CATEGORY}}",
        badge: "{{PRODUCT_1_BADGE}}"
    },
    {
        id: 2,
        name: "{{PRODUCT_2_NAME}}",
        description: "{{PRODUCT_2_DESCRIPTION}}",
        price: {{PRODUCT_2_PRICE}},
        image: "{{PRODUCT_2_IMAGE}}",
        category: "{{PRODUCT_2_CATEGORY}}",
        badge: "{{PRODUCT_2_BADGE}}"
    },
    {
        id: 3,
        name: "{{PRODUCT_3_NAME}}",
        description: "{{PRODUCT_3_DESCRIPTION}}",
        price: {{PRODUCT_3_PRICE}},
        image: "{{PRODUCT_3_IMAGE}}",
        category: "{{PRODUCT_3_CATEGORY}}",
        badge: "{{PRODUCT_3_BADGE}}"
    },
    {
        id: 4,
        name: "{{PRODUCT_4_NAME}}",
        description: "{{PRODUCT_4_DESCRIPTION}}",
        price: {{PRODUCT_4_PRICE}},
        image: "{{PRODUCT_4_IMAGE}}",
        category: "{{PRODUCT_4_CATEGORY}}",
        badge: "{{PRODUCT_4_BADGE}}"
    },
    {
        id: 5,
        name: "{{PRODUCT_5_NAME}}",
        description: "{{PRODUCT_5_DESCRIPTION}}",
        price: {{PRODUCT_5_PRICE}},
        image: "{{PRODUCT_5_IMAGE}}",
        category: "{{PRODUCT_5_CATEGORY}}",
        badge: "{{PRODUCT_5_BADGE}}"
    },
    {
        id: 6,
        name: "{{PRODUCT_6_NAME}}",
        description: "{{PRODUCT_6_DESCRIPTION}}",
        price: {{PRODUCT_6_PRICE}},
        image: "{{PRODUCT_6_IMAGE}}",
        category: "{{PRODUCT_6_CATEGORY}}",
        badge: "{{PRODUCT_6_BADGE}}"
    }
];

// ====================================
// INICIALIZACIÓN
// ====================================

document.addEventListener('DOMContentLoaded', function() {
    renderProducts();
    loadCartFromStorage();
    updateCartCount();
});

// ====================================
// RENDERIZAR PRODUCTOS
// ====================================

function renderProducts(filteredProducts = products) {
    const grid = document.getElementById('productsGrid');
    grid.innerHTML = '';
    
    filteredProducts.forEach(product => {
        const productCard = `
            <div class="product-card" data-category="${product.category}" data-price="${product.price}">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.name}">
                    ${product.badge ? `<div class="product-badge">${product.badge}</div>` : ''}
                </div>
                <div class="product-info">
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    <div class="product-footer">
                        <span class="product-price">$${product.price}</span>
                        <button class="btn-add-cart" onclick="addToCart(${product.id})">Agregar 🛒</button>
                    </div>
                </div>
            </div>
        `;
        grid.innerHTML += productCard;
    });
}

// ====================================
// FILTRAR POR CATEGORÍA
// ====================================

function filterCategory(category) {
    // Actualizar botones activos
    document.querySelectorAll('.category-card').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.closest('.category-card').classList.add('active');
    
    // Filtrar productos
    if (category === 'all') {
        renderProducts(products);
    } else {
        const filtered = products.filter(p => p.category === category);
        renderProducts(filtered);
    }
}

// ====================================
// ORDENAR PRODUCTOS
// ====================================

function sortProducts(sortType) {
    let sorted = [...products];
    
    switch(sortType) {
        case 'price-asc':
            sorted.sort((a, b) => a.price - b.price);
            break;
        case 'price-desc':
            sorted.sort((a, b) => b.price - a.price);
            break;
        case 'name':
            sorted.sort((a, b) => a.name.localeCompare(b.name));
            break;
        default:
            sorted = products;
    }
    
    renderProducts(sorted);
}

// ====================================
// CARRITO DE COMPRAS
// ====================================

function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    
    if (!product) return;
    
    // Verificar si ya está en el carrito
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            ...product,
            quantity: 1
        });
    }
    
    updateCart();
    saveCartToStorage();
    
    // Feedback visual
    showNotification('Producto agregado al carrito');
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCart();
    saveCartToStorage();
}

function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    
    if (!item) return;
    
    item.quantity += change;
    
    if (item.quantity <= 0) {
        removeFromCart(productId);
    } else {
        updateCart();
        saveCartToStorage();
    }
}

function updateCart() {
    const cartItems = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="empty-cart">Tu carrito está vacío</p>';
    } else {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">$${item.price}</div>
                    <div class="cart-item-quantity">
                        <button onclick="updateQuantity(${item.id}, -1)">-</button>
                        <span>${item.quantity}</span>
                        <button onclick="updateQuantity(${item.id}, 1)">+</button>
                        <button onclick="removeFromCart(${item.id})" style="margin-left: auto; color: #f56565;">🗑️</button>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    updateCartTotal();
    updateCartCount();
}

function updateCartTotal() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.querySelector('.total-amount').textContent = `$${total.toFixed(2)}`;
}

function updateCartCount() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    document.querySelector('.cart-count').textContent = count;
}

// ====================================
// TOGGLE CARRITO
// ====================================

function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    cartSidebar.classList.toggle('active');
}

// ====================================
// BÚSQUEDA
// ====================================

function toggleSearch() {
    const searchTerm = prompt('¿Qué producto buscas?');
    
    if (!searchTerm) return;
    
    const filtered = products.filter(p => 
        p.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        p.description.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    if (filtered.length > 0) {
        renderProducts(filtered);
        showNotification(`${filtered.length} producto(s) encontrado(s)`);
    } else {
        showNotification('No se encontraron productos');
        renderProducts(products);
    }
}

// ====================================
// MENÚ MÓVIL
// ====================================

function toggleMobileMenu() {
    const nav = document.querySelector('.nav');
    nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
}

// ====================================
// FINALIZAR COMPRA
// ====================================

function checkout() {
    if (cart.length === 0) {
        alert('Tu carrito está vacío');
        return;
    }
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const itemsList = cart.map(item => `${item.name} x${item.quantity}`).join('\n');
    
    // Aquí se puede integrar con WhatsApp, Email, o plataforma de pago
    const whatsappNumber = '{{WHATSAPP_NUMBER}}';
    const message = encodeURIComponent(
        `Hola! Quiero realizar el siguiente pedido:\n\n${itemsList}\n\nTotal: $${total.toFixed(2)}`
    );
    
    // Opción 1: WhatsApp
    if (whatsappNumber && whatsappNumber !== '{{WHATSAPP_NUMBER}}') {
        window.open(`https://wa.me/${whatsappNumber}?text=${message}`, '_blank');
    } else {
        // Opción 2: Email
        const email = '{{CONTACT_EMAIL}}';
        window.location.href = `mailto:${email}?subject=Pedido&body=${message}`;
    }
    
    // Limpiar carrito después de enviar
    setTimeout(() => {
        cart = [];
        updateCart();
        saveCartToStorage();
        toggleCart();
    }, 1000);
}

// ====================================
// PERSISTENCIA (LocalStorage)
// ====================================

function saveCartToStorage() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function loadCartFromStorage() {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCart();
    }
}

// ====================================
// NOTIFICACIONES
// ====================================

function showNotification(message) {
    // Crear notificación simple
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #48bb78;
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
    }, 2000);
}

// Añadir animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ====================================
// SMOOTH SCROLL
// ====================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
