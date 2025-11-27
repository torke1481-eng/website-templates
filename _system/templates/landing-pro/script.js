// ============================================
// LANDING PRO 2025 - JavaScript Avanzado
// Sistema modular, seguro y optimizado
// ============================================

'use strict';

// ============================================
// CONFIGURACIÓN GLOBAL
// ============================================
const CONFIG = {
    animations: {
        duration: 600,
        easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
        stagger: 100,
        threshold: 0.1
    },
    scroll: {
        throttleMs: 16, // ~60fps
        headerHideAfter: 500,
        headerShowOffset: 100
    },
    form: {
        maxNameLength: 100,
        maxEmailLength: 254,
        maxMessageLength: 1000,
        minMessageLength: 10
    },
    counters: {
        duration: 2000,
        fps: 60
    }
};

// ============================================
// UTILITIES
// ============================================

// Throttle optimizado
function throttle(func, wait) {
    let timeout;
    let lastRan;
    return function executedFunction(...args) {
        if (!lastRan) {
            func.apply(this, args);
            lastRan = Date.now();
        } else {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if ((Date.now() - lastRan) >= wait) {
                    func.apply(this, args);
                    lastRan = Date.now();
                }
            }, wait - (Date.now() - lastRan));
        }
    };
}

// Debounce para inputs
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Sanitizar HTML para prevenir XSS
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

// Detectar si es móvil
function isMobile() {
    return window.matchMedia('(max-width: 768px)').matches;
}

// Detectar preferencia de movimiento reducido
function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

// Logger seguro
const logger = {
    error: (msg, err) => console.error(`[LandingPro] ${msg}:`, err),
    warn: (msg) => console.warn(`[LandingPro] ${msg}`),
    info: (msg) => console.info(`[LandingPro] ${msg}`)
};

// ============================================
// INICIALIZACIÓN PRINCIPAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Lista de módulos a inicializar
    const modules = [
        { name: 'Smooth Scroll', init: initSmoothScroll },
        { name: 'Active Nav', init: initActiveNav },
        { name: 'Scroll Animations', init: initScrollAnimations },
        { name: 'FAQ Accordion', init: initFAQAccordion },
        { name: 'Counter Animation', init: initCounterAnimation },
        { name: 'Header Scroll', init: initHeaderScroll },
        { name: 'Form Submit', init: initFormSubmit },
        { name: 'Cursor Glow', init: initCursorGlow },
        { name: 'Lazy Images', init: initLazyImages },
        { name: 'Magnetic Buttons', init: initMagneticButtons },
        { name: 'Tilt Cards', init: initTiltCards },
        { name: 'Progress Bar', init: initProgressBar },
        { name: 'Back to Top', init: initBackToTop }
    ];
    
    // Inicializar cada módulo con manejo de errores
    modules.forEach(module => {
        try {
            module.init();
        } catch(e) {
            logger.error(`Error en ${module.name}`, e);
        }
    });
    
    // Log de éxito
    logger.info('Todos los módulos inicializados correctamente');
});

// SMOOTH SCROLL
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

// ACTIVE NAVIGATION
function initActiveNav() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('nav a[href^="#"]');
    
    const updateActiveNav = () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.pageYOffset >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').slice(1) === current) {
                link.classList.add('active');
            }
        });
    };
    
    window.addEventListener('scroll', throttle(updateActiveNav, 100));
}

// SCROLL ANIMATIONS
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Elementos a animar
    const animateElements = document.querySelectorAll(
        '.feature-card-pro, .stat-item, .process-step, ' +
        '.testimonial-card, .faq-item, .about-content-pro, .hero-card'
    );
    
    animateElements.forEach(el => observer.observe(el));
}

// HEADER SCROLL EFFECT
function initHeaderScroll() {
    const header = document.querySelector('header');
    if (!header) return;
    
    let lastScroll = 0;
    
    const handleHeaderScroll = () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Hide/show header on scroll
        if (currentScroll > lastScroll && currentScroll > 500) {
            header.style.transform = 'translateY(-100%)';
        } else {
            header.style.transform = 'translateY(0)';
        }
        
        lastScroll = currentScroll;
    };
    
    window.addEventListener('scroll', throttle(handleHeaderScroll, 100));
}

// FAQ ACCORDION
function initFAQAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Cerrar todos
            faqItems.forEach(i => i.classList.remove('active'));
            
            // Abrir el clickeado si no estaba activo
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
}

// ANIMATED COUNTERS
function initCounterAnimation() {
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200;
    
    const observerOptions = {
        threshold: 0.5
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = counter.getAttribute('data-target');
                
                if (target) {
                    animateCounter(counter, target, speed);
                    observer.unobserve(counter);
                }
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(element, target, speed) {
    const targetNum = parseInt(target.replace(/\D/g, ''));
    const suffix = target.replace(/[0-9]/g, '');
    const increment = targetNum / speed;
    let current = 0;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= targetNum) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.ceil(current) + suffix;
        }
    }, 10);
}

// FORM SUBMIT
function initFormSubmit() {
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        let isSubmitting = false; // Protección contra doble-click
        
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Evitar envíos múltiples
            if (isSubmitting) {
                logger.warn('Formulario ya está siendo enviado');
                return;
            }
            
            // Validar campos
            const name = document.getElementById('contact-name');
            const email = document.getElementById('contact-email');
            const phone = document.getElementById('contact-phone');
            const message = document.getElementById('contact-message');
            
            let isValid = true;
            
            // Validar nombre
            if (name) {
                const nameValue = name.value.trim();
                
                if (nameValue.length < 2) {
                    showError(name, 'El nombre debe tener al menos 2 caracteres');
                    isValid = false;
                } else if (nameValue.length > 100) {
                    showError(name, 'El nombre es demasiado largo');
                    isValid = false;
                } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'-]+$/.test(nameValue)) {
                    showError(name, 'El nombre contiene caracteres no válidos');
                    isValid = false;
                } else if (/<[^>]*>/g.test(nameValue)) {
                    showError(name, 'El nombre contiene código no permitido');
                    isValid = false;
                } else if (/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|EXEC|SCRIPT)\b)/gi.test(nameValue)) {
                    showError(name, 'El nombre contiene palabras no permitidas');
                    isValid = false;
                } else {
                    clearError(name);
                }
            }
            
            // Validar email
            const emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            if (email) {
                const emailValue = email.value.trim();
                
                if (!emailRegex.test(emailValue)) {
                    showError(email, 'Por favor ingresa un email válido');
                    isValid = false;
                } else if (emailValue.length > 254) {
                    showError(email, 'El email es demasiado largo');
                    isValid = false;
                } else if (/<[^>]*>/g.test(emailValue)) {
                    showError(email, 'El email contiene código no permitido');
                    isValid = false;
                } else {
                    clearError(email);
                }
            }
            
            // Validar teléfono (si existe)
            if (phone) {
                const phoneValue = phone.value.trim();
                const phoneRegex = /^[\d\s\-\+\(\)]{7,20}$/;
                
                if (phoneValue && !phoneRegex.test(phoneValue)) {
                    showError(phone, 'Por favor ingresa un teléfono válido');
                    isValid = false;
                } else if (/<[^>]*>/g.test(phoneValue)) {
                    showError(phone, 'El teléfono contiene código no permitido');
                    isValid = false;
                } else {
                    clearError(phone);
                }
            }
            
            // Validar mensaje
            if (message) {
                const messageValue = message.value.trim();
                
                if (messageValue.length > 0 && messageValue.length < 10) {
                    showError(message, 'El mensaje debe tener al menos 10 caracteres');
                    isValid = false;
                } else if (messageValue.length > 1000) {
                    showError(message, 'El mensaje es demasiado largo (máximo 1000 caracteres)');
                    isValid = false;
                } else {
                    clearError(message);
                }
            }
            
            if (!isValid) {
                return;
            }
            
            // Deshabilitar botón mientras se envía
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Enviando...</span>';
            isSubmitting = true;
            
            // Simular envío (aquí puedes agregar tu lógica real)
            setTimeout(() => {
                // Mostrar mensaje de éxito con animación
                showSuccessMessage('¡Gracias por tu mensaje! Te contactaremos pronto.');
                
                // Resetear formulario
                this.reset();
                
                // Restaurar botón y estado
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                isSubmitting = false;
            }, 1500);
        });
        
        // Función para mostrar error
        function showError(input, message) {
            const formGroup = input.closest('.form-group');
            const existingError = formGroup.querySelector('.error-message');
            if (existingError) existingError.remove();
            
            input.classList.add('error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            errorDiv.style.color = '#dc3545';
            errorDiv.style.fontSize = '14px';
            errorDiv.style.marginTop = '5px';
            formGroup.appendChild(errorDiv);
        }
        
        // Función para limpiar error
        function clearError(input) {
            const formGroup = input.closest('.form-group');
            const errorMessage = formGroup.querySelector('.error-message');
            if (errorMessage) errorMessage.remove();
            input.classList.remove('error');
        }
        
        // Función para mostrar éxito
        function showSuccessMessage(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-notification';
            successDiv.innerHTML = `
                <div style="background: #10b981; color: white; padding: 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3); display: flex; align-items: center; gap: 15px; animation: fadeInDown 0.5s ease-out;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            successDiv.style.position = 'fixed';
            successDiv.style.top = '20px';
            successDiv.style.right = '20px';
            successDiv.style.zIndex = '9999';
            document.body.appendChild(successDiv);
            
            setTimeout(() => {
                successDiv.style.animation = 'fadeOutUp 0.5s ease-out';
                setTimeout(() => successDiv.remove(), 500);
            }, 3000);
        }
    }
}

// PARALLAX EFFECT (Optional) - Optimizado con RAF
const handleParallax = () => {
    const scrolled = window.pageYOffset;
    const parallaxElements = document.querySelectorAll('[data-parallax]');
    
    requestAnimationFrame(() => {
        parallaxElements.forEach(el => {
            const speed = el.getAttribute('data-parallax') || 0.5;
            el.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
};

window.addEventListener('scroll', throttle(handleParallax, 16)); // ~60fps

// ============================================
// 2025: NUEVOS MÓDULOS MODERNOS
// ============================================

// CURSOR GLOW - Efecto de brillo que sigue al cursor (solo desktop)
function initCursorGlow() {
    if (isMobile() || prefersReducedMotion()) return;
    
    const glow = document.createElement('div');
    glow.className = 'cursor-glow';
    glow.style.cssText = `
        position: fixed;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, var(--glow-primary, rgba(102, 126, 234, 0.15)) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        transform: translate(-50%, -50%);
        transition: opacity 0.3s;
        opacity: 0;
    `;
    document.body.appendChild(glow);
    
    let mouseX = 0, mouseY = 0;
    let glowX = 0, glowY = 0;
    
    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
        glow.style.opacity = '1';
    });
    
    document.addEventListener('mouseleave', () => {
        glow.style.opacity = '0';
    });
    
    // Animación suave con RAF
    function animateGlow() {
        glowX += (mouseX - glowX) * 0.1;
        glowY += (mouseY - glowY) * 0.1;
        glow.style.left = glowX + 'px';
        glow.style.top = glowY + 'px';
        requestAnimationFrame(animateGlow);
    }
    animateGlow();
}

// LAZY IMAGES - Carga diferida de imágenes con blur
function initLazyImages() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        }, { rootMargin: '50px' });
        
        images.forEach(img => {
            img.style.filter = 'blur(10px)';
            img.style.transition = 'filter 0.5s ease';
            img.addEventListener('load', () => {
                img.style.filter = 'blur(0)';
            });
            imageObserver.observe(img);
        });
    } else {
        // Fallback para navegadores antiguos
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// MAGNETIC BUTTONS - Botones que se atraen al cursor
function initMagneticButtons() {
    if (isMobile() || prefersReducedMotion()) return;
    
    const buttons = document.querySelectorAll('.btn-hero-primary, .btn-primary-large, .btn-form-submit');
    
    buttons.forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            
            btn.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
        });
        
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = 'translate(0, 0)';
        });
    });
}

// TILT CARDS - Efecto 3D en cards al hover
function initTiltCards() {
    if (isMobile() || prefersReducedMotion()) return;
    
    const cards = document.querySelectorAll('.feature-card-pro, .testimonial-card, .hero-card');
    
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;
            
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
        });
    });
}

// PROGRESS BAR - Barra de progreso de lectura
function initProgressBar() {
    const progressBar = document.createElement('div');
    progressBar.className = 'reading-progress';
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        height: 3px;
        background: var(--gradient-mesh, linear-gradient(90deg, #667eea, #764ba2));
        background-size: 200% 200%;
        z-index: 10000;
        transition: width 0.1s ease;
        width: 0%;
    `;
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', throttle(() => {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = (scrollTop / docHeight) * 100;
        progressBar.style.width = `${Math.min(progress, 100)}%`;
    }, 16));
}

// BACK TO TOP - Botón para volver arriba
function initBackToTop() {
    const btn = document.createElement('button');
    btn.className = 'back-to-top';
    btn.setAttribute('aria-label', 'Volver arriba');
    btn.innerHTML = `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
    `;
    btn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--gradient-mesh, linear-gradient(135deg, #667eea, #764ba2));
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 9999;
    `;
    document.body.appendChild(btn);
    
    // Mostrar/ocultar según scroll
    window.addEventListener('scroll', throttle(() => {
        if (window.pageYOffset > 500) {
            btn.style.opacity = '1';
            btn.style.visibility = 'visible';
            btn.style.transform = 'translateY(0)';
        } else {
            btn.style.opacity = '0';
            btn.style.visibility = 'hidden';
            btn.style.transform = 'translateY(20px)';
        }
    }, 100));
    
    // Click para volver arriba
    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    // Hover effect
    btn.addEventListener('mouseenter', () => {
        btn.style.transform = 'translateY(-5px) scale(1.1)';
    });
    btn.addEventListener('mouseleave', () => {
        btn.style.transform = window.pageYOffset > 500 ? 'translateY(0)' : 'translateY(20px)';
    });
}

// ============================================
// WHATSAPP INTEGRATION
// ============================================
function openWhatsApp(phone, message = '') {
    const encodedMessage = encodeURIComponent(message);
    const url = `https://wa.me/${phone}${message ? '?text=' + encodedMessage : ''}`;
    window.open(url, '_blank', 'noopener,noreferrer');
}

// Exponer función globalmente para uso en HTML
window.openWhatsApp = openWhatsApp;

// ============================================
// FORM TO WHATSAPP
// ============================================
function sendFormToWhatsApp(formId, phone) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const formData = new FormData(form);
    let message = '¡Hola! Me contacto desde la web:\n\n';
    
    formData.forEach((value, key) => {
        if (value.trim()) {
            const label = key.charAt(0).toUpperCase() + key.slice(1);
            message += `*${label}:* ${sanitizeHTML(value)}\n`;
        }
    });
    
    openWhatsApp(phone, message);
}

window.sendFormToWhatsApp = sendFormToWhatsApp;

// ============================================
// ANALYTICS HELPERS (sin dependencias externas)
// ============================================
const Analytics = {
    track: function(event, data = {}) {
        // Google Analytics 4
        if (typeof gtag === 'function') {
            gtag('event', event, data);
        }
        // Facebook Pixel
        if (typeof fbq === 'function') {
            fbq('track', event, data);
        }
        // Log para debug
        logger.info(`Analytics: ${event} - ${JSON.stringify(data)}`);
    },
    
    trackClick: function(element, eventName) {
        element.addEventListener('click', () => {
            this.track(eventName, { element: element.textContent || element.className });
        });
    },
    
    trackScroll: function(percentage) {
        let tracked = {};
        window.addEventListener('scroll', throttle(() => {
            const scrollTop = window.pageYOffset;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = Math.round((scrollTop / docHeight) * 100);
            
            [25, 50, 75, 100].forEach(milestone => {
                if (progress >= milestone && !tracked[milestone]) {
                    tracked[milestone] = true;
                    this.track('scroll_depth', { percentage: milestone });
                }
            });
        }, 500));
    }
};

window.Analytics = Analytics;

// ============================================
// PERFORMANCE MONITORING
// ============================================
if ('PerformanceObserver' in window) {
    // Observar LCP (Largest Contentful Paint)
    try {
        const lcpObserver = new PerformanceObserver((list) => {
            const entries = list.getEntries();
            const lastEntry = entries[entries.length - 1];
            logger.info(`LCP: ${lastEntry.startTime.toFixed(0)}ms`);
        });
        lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true });
    } catch (e) {
        // Silently fail if not supported
    }
    
    // Observar CLS (Cumulative Layout Shift)
    try {
        let clsValue = 0;
        const clsObserver = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            }
        });
        clsObserver.observe({ type: 'layout-shift', buffered: true });
        
        // Reportar CLS al salir de la página
        window.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                logger.info(`CLS: ${clsValue.toFixed(3)}`);
            }
        });
    } catch (e) {
        // Silently fail if not supported
    }
}
