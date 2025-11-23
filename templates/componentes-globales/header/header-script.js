/**
 * HEADER MODERNO MODULAR - JAVASCRIPT
 * Funcionalidades interactivas reutilizables
 */

// Toggle búsqueda
function toggleSearch() {
    const searchOverlay = document.getElementById('searchOverlay');
    searchOverlay.classList.toggle('active');
    
    if (searchOverlay.classList.contains('active')) {
        setTimeout(() => {
            const searchInput = searchOverlay.querySelector('.search-input-modern');
            if (searchInput) searchInput.focus();
        }, 100);
    }
}

// Toggle menú móvil
function toggleMobileMenu() {
    const nav = document.querySelector('.nav-modern');
    const btn = document.querySelector('.mobile-menu-btn');
    
    if (nav && btn) {
        nav.classList.toggle('active');
        btn.classList.toggle('active');
        
        if (nav.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
}

// Efecto scroll en header
window.addEventListener('scroll', function() {
    const header = document.getElementById('mainHeader');
    
    if (header) {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
});

// Cerrar búsqueda con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const searchOverlay = document.getElementById('searchOverlay');
        if (searchOverlay && searchOverlay.classList.contains('active')) {
            toggleSearch();
        }
    }
});

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        
        if (!href || href === '#' || href.length <= 1) {
            e.preventDefault();
            return;
        }
        
        try {
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        } catch (error) {
            console.warn('Selector inválido:', href);
        }
    });
});

// Actualizar badge del carrito
function updateCartBadge(count) {
    const badge = document.querySelector('.cart-badge-modern');
    if (badge) {
        badge.textContent = count;
        badge.style.transform = 'scale(1.3)';
        setTimeout(() => {
            badge.style.transform = 'scale(1)';
        }, 200);
    }
}

// Log de inicialización
console.log('✅ Header modular cargado correctamente');
