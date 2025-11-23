/**
 * LANDING PAGE BÁSICA - JavaScript
 * Funcionalidades adicionales opcionales
 */

// Smooth scroll para anchor links
document.addEventListener('DOMContentLoaded', function() {
    
    // Animaciones al scroll
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
    
    console.log('✅ Landing page cargada correctamente');
});
