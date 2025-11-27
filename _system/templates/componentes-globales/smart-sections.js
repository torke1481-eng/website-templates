/**
 * SMART SECTIONS 2025
 * Sistema de secciones adaptativas que cambian según la cantidad de contenido
 * Detecta automáticamente el mejor layout para cada sección
 */

'use strict';

const SmartSections = {
    
    /**
     * Configuración de layouts por tipo de sección
     */
    layouts: {
        services: {
            1: { type: 'single', columns: 1, class: 'services-single' },
            2: { type: 'grid', columns: 2, class: 'services-grid-2' },
            3: { type: 'grid', columns: 3, class: 'services-grid-3' },
            4: { type: 'grid', columns: 2, class: 'services-grid-2x2' },
            5: { type: 'grid', columns: 3, class: 'services-grid-3-featured' },
            6: { type: 'grid', columns: 3, class: 'services-grid-3' },
            'many': { type: 'carousel', columns: 4, class: 'services-carousel' }
        },
        testimonials: {
            1: { type: 'single', columns: 1, class: 'testimonial-single' },
            2: { type: 'grid', columns: 2, class: 'testimonials-grid-2' },
            3: { type: 'grid', columns: 3, class: 'testimonials-grid-3' },
            'many': { type: 'carousel', columns: 3, class: 'testimonials-carousel' }
        },
        features: {
            2: { type: 'grid', columns: 2, class: 'features-grid-2' },
            3: { type: 'grid', columns: 3, class: 'features-grid-3' },
            4: { type: 'grid', columns: 2, class: 'features-grid-2x2' },
            6: { type: 'grid', columns: 3, class: 'features-grid-3' },
            'many': { type: 'grid', columns: 4, class: 'features-grid-4' }
        },
        faq: {
            1: { type: 'accordion', class: 'faq-accordion' },
            2: { type: 'accordion', class: 'faq-accordion' },
            3: { type: 'accordion', class: 'faq-accordion' },
            4: { type: 'accordion', class: 'faq-accordion' },
            5: { type: 'accordion', class: 'faq-accordion' },
            'many': { type: 'tabs', class: 'faq-tabs' }
        },
        gallery: {
            1: { type: 'single', class: 'gallery-single' },
            2: { type: 'grid', columns: 2, class: 'gallery-grid-2' },
            3: { type: 'grid', columns: 3, class: 'gallery-grid-3' },
            4: { type: 'grid', columns: 2, class: 'gallery-grid-2x2' },
            'many': { type: 'masonry', class: 'gallery-masonry' }
        }
    },

    /**
     * Determina el mejor layout para una sección
     */
    getOptimalLayout(sectionType, itemCount) {
        const layouts = this.layouts[sectionType];
        if (!layouts) return { type: 'grid', columns: 3, class: 'default-grid' };

        // Buscar layout exacto
        if (layouts[itemCount]) {
            return layouts[itemCount];
        }

        // Si hay más items de los definidos, usar 'many'
        if (itemCount > Math.max(...Object.keys(layouts).filter(k => k !== 'many').map(Number))) {
            return layouts['many'] || { type: 'grid', columns: 3, class: 'default-grid' };
        }

        // Buscar el más cercano
        const keys = Object.keys(layouts).filter(k => k !== 'many').map(Number).sort((a, b) => a - b);
        const closest = keys.reduce((prev, curr) => 
            Math.abs(curr - itemCount) < Math.abs(prev - itemCount) ? curr : prev
        );
        
        return layouts[closest];
    },

    /**
     * Genera CSS para el layout
     */
    generateLayoutCSS(layout) {
        const baseCSS = `
.${layout.class} {
    display: grid;
    gap: var(--space-lg, 32px);
    width: 100%;
}
`;
        let specificCSS = '';

        switch (layout.type) {
            case 'single':
                specificCSS = `
.${layout.class} {
    max-width: 800px;
    margin: 0 auto;
}
.${layout.class} > * {
    text-align: center;
}
`;
                break;

            case 'grid':
                specificCSS = `
.${layout.class} {
    grid-template-columns: repeat(${layout.columns}, 1fr);
}
@media (max-width: 992px) {
    .${layout.class} {
        grid-template-columns: repeat(${Math.min(layout.columns, 2)}, 1fr);
    }
}
@media (max-width: 576px) {
    .${layout.class} {
        grid-template-columns: 1fr;
    }
}
`;
                break;

            case 'carousel':
                specificCSS = `
.${layout.class} {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 20px;
}
.${layout.class}::-webkit-scrollbar {
    display: none;
}
.${layout.class} > * {
    flex: 0 0 calc(100% / ${layout.columns} - 24px);
    scroll-snap-align: start;
    margin-right: 24px;
}
@media (max-width: 992px) {
    .${layout.class} > * {
        flex: 0 0 calc(50% - 12px);
    }
}
@media (max-width: 576px) {
    .${layout.class} > * {
        flex: 0 0 calc(100% - 48px);
    }
}
`;
                break;

            case 'masonry':
                specificCSS = `
.${layout.class} {
    columns: 3;
    column-gap: 24px;
}
.${layout.class} > * {
    break-inside: avoid;
    margin-bottom: 24px;
}
@media (max-width: 992px) {
    .${layout.class} {
        columns: 2;
    }
}
@media (max-width: 576px) {
    .${layout.class} {
        columns: 1;
    }
}
`;
                break;

            case 'tabs':
                specificCSS = `
.${layout.class} .faq-tabs-nav {
    display: flex;
    border-bottom: 2px solid var(--border-color, #e0e0e0);
    margin-bottom: 24px;
    overflow-x: auto;
}
.${layout.class} .faq-tab-btn {
    padding: 12px 24px;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    color: var(--text-light);
    transition: all 0.3s;
    white-space: nowrap;
}
.${layout.class} .faq-tab-btn.active {
    color: var(--brand-primary);
    border-bottom: 2px solid var(--brand-primary);
    margin-bottom: -2px;
}
`;
                break;

            case 'accordion':
                specificCSS = `
.${layout.class} {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
`;
                break;
        }

        return baseCSS + specificCSS;
    },

    /**
     * Aplica layout a una sección del DOM
     */
    applyLayout(container, sectionType) {
        const items = container.children;
        const itemCount = items.length;
        const layout = this.getOptimalLayout(sectionType, itemCount);

        // Limpiar clases anteriores
        container.className = container.className.replace(/services-|testimonials-|features-|faq-|gallery-/g, '');
        
        // Aplicar nueva clase
        container.classList.add(layout.class);

        // Inyectar CSS si no existe
        const styleId = `smart-section-${layout.class}`;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = this.generateLayoutCSS(layout);
            document.head.appendChild(style);
        }

        // Si es carousel, agregar controles
        if (layout.type === 'carousel') {
            this.addCarouselControls(container);
        }

        console.log(`📐 Smart Section: ${sectionType} → ${layout.type} (${itemCount} items)`);
        return layout;
    },

    /**
     * Agrega controles de carousel
     */
    addCarouselControls(container) {
        // Evitar duplicados
        if (container.querySelector('.carousel-controls')) return;

        const controls = document.createElement('div');
        controls.className = 'carousel-controls';
        controls.innerHTML = `
            <button class="carousel-btn carousel-prev" aria-label="Anterior">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="carousel-btn carousel-next" aria-label="Siguiente">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        `;

        container.parentElement.style.position = 'relative';
        container.parentElement.appendChild(controls);

        // Event listeners
        const scrollAmount = container.firstElementChild?.offsetWidth + 24 || 300;
        
        controls.querySelector('.carousel-prev').addEventListener('click', () => {
            container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
        
        controls.querySelector('.carousel-next').addEventListener('click', () => {
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });

        // Agregar estilos de controles
        if (!document.getElementById('carousel-controls-style')) {
            const style = document.createElement('style');
            style.id = 'carousel-controls-style';
            style.textContent = `
.carousel-controls {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    transform: translateY(-50%);
    display: flex;
    justify-content: space-between;
    pointer-events: none;
    padding: 0 10px;
}
.carousel-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--glass-bg, rgba(255,255,255,0.9));
    border: 1px solid var(--border-color, #e0e0e0);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: auto;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.carousel-btn:hover {
    background: var(--brand-primary);
    color: white;
    transform: scale(1.1);
}
@media (max-width: 768px) {
    .carousel-controls {
        display: none;
    }
}
`;
            document.head.appendChild(style);
        }
    },

    /**
     * Auto-detecta y aplica layouts a todas las secciones
     */
    autoApply() {
        const sectionMappings = {
            '.services-grid, .features-grid, [data-section="services"]': 'services',
            '.testimonials-grid, [data-section="testimonials"]': 'testimonials',
            '.features-container, [data-section="features"]': 'features',
            '.faq-list, .faq-container, [data-section="faq"]': 'faq',
            '.gallery-grid, [data-section="gallery"]': 'gallery'
        };

        for (const [selector, sectionType] of Object.entries(sectionMappings)) {
            document.querySelectorAll(selector).forEach(container => {
                if (container.children.length > 0) {
                    this.applyLayout(container, sectionType);
                }
            });
        }
    },

    /**
     * Genera CSS completo para todos los layouts
     */
    generateAllCSS() {
        let css = '/* Smart Sections - Auto-generated layouts */\n\n';
        
        for (const [sectionType, layouts] of Object.entries(this.layouts)) {
            css += `/* ${sectionType.toUpperCase()} */\n`;
            for (const [count, layout] of Object.entries(layouts)) {
                css += this.generateLayoutCSS(layout);
                css += '\n';
            }
        }
        
        return css;
    }
};

// Auto-inicializar cuando el DOM esté listo
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => SmartSections.autoApply());
    } else {
        SmartSections.autoApply();
    }
}

// Exponer globalmente
if (typeof window !== 'undefined') {
    window.SmartSections = SmartSections;
}

// Export para Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SmartSections;
}
