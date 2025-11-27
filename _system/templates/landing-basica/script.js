/**
 * LANDING PAGE BÁSICA 2025 - JavaScript
 * Funcionalidades: Smooth scroll, animaciones, scroll activo
 * Optimizado con throttle y accesibilidad
 */

'use strict';

// Throttle para optimizar eventos de scroll
function throttle(func, wait) {
    let timeout;
    let lastRan;
    return function(...args) {
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

// Detectar preferencia de movimiento reducido
function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

document.addEventListener('DOMContentLoaded', function() {
    
    // =================================
    // SMOOTH SCROLL
    // =================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // =================================
    // SCROLL ACTIVO EN NAVEGACIÓN
    // =================================
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('nav a[href^="#"]');
    
    function highlightNav() {
        let scrollY = window.pageYOffset;
        
        sections.forEach(current => {
            const sectionHeight = current.offsetHeight;
            const sectionTop = current.offsetTop - 100;
            const sectionId = current.getAttribute('id');
            
            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + sectionId) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }
    
    window.addEventListener('scroll', throttle(highlightNav, 100));
    
    // =================================
    // ANIMACIONES AL SCROLL
    // =================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observar elementos animables
    const animatedElements = document.querySelectorAll('.feature-card, .about-content, .cta-content');
    animatedElements.forEach(el => observer.observe(el));
    
    // =================================
    // HEADER AL SCROLL
    // =================================
    let lastScroll = 0;
    const header = document.querySelector('header');
    
    const handleHeaderScroll = () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    };
    
    window.addEventListener('scroll', throttle(handleHeaderScroll, 100));
    
    console.log('✅ Landing page 2025 cargada correctamente');
});
