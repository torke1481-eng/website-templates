/**
 * PERSONALITY ENGINE 2025
 * Motor de personalidad que adapta la landing según el tipo de negocio
 * Afecta: animaciones, tipografía, colores, CTAs, tonos de comunicación
 */

'use strict';

const PersonalityEngine = {
    
    // Definición de personalidades
    personalities: {
        professional: {
            name: 'Profesional',
            description: 'Elegante, serio, confiable',
            industries: ['legal', 'finanzas', 'consultoria', 'contabilidad', 'seguros'],
            styles: {
                fontFamily: "'Inter', 'Helvetica Neue', sans-serif",
                fontWeight: { heading: 600, body: 400 },
                letterSpacing: '0.02em',
                borderRadius: '8px',
                animationSpeed: '0.4s',
                animationEasing: 'cubic-bezier(0.4, 0, 0.2, 1)',
                shadowIntensity: 'subtle',
                glassOpacity: 0.85
            },
            colors: {
                recommended: ['#1e3a5f', '#2c5282', '#1a365d', '#2d3748'],
                accent: ['#c9a227', '#d69e2e', '#b7791f']
            },
            ctas: {
                primary: ['Solicitar información', 'Agendar consulta', 'Contactar ahora'],
                secondary: ['Ver servicios', 'Conocer más', 'Descargar brochure']
            },
            animations: {
                hero: 'fadeInUp',
                cards: 'fadeIn',
                hover: 'subtle-lift',
                scroll: 'fade-slide'
            }
        },

        friendly: {
            name: 'Amigable',
            description: 'Cercano, cálido, accesible',
            industries: ['restaurante', 'cafe', 'panaderia', 'tienda_ropa', 'peluqueria', 'spa'],
            styles: {
                fontFamily: "'Nunito', 'Poppins', sans-serif",
                fontWeight: { heading: 700, body: 400 },
                letterSpacing: '0',
                borderRadius: '16px',
                animationSpeed: '0.5s',
                animationEasing: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
                shadowIntensity: 'warm',
                glassOpacity: 0.75
            },
            colors: {
                recommended: ['#e07b39', '#d97706', '#ea580c', '#c2410c'],
                accent: ['#059669', '#10b981', '#34d399']
            },
            ctas: {
                primary: ['¡Reserva ahora!', '¡Visítanos!', '¡Haz tu pedido!'],
                secondary: ['Ver menú', 'Conoce más', 'Síguenos']
            },
            animations: {
                hero: 'bounceIn',
                cards: 'slideUp',
                hover: 'bounce-scale',
                scroll: 'pop-in'
            }
        },

        bold: {
            name: 'Audaz',
            description: 'Impactante, moderno, disruptivo',
            industries: ['fitness', 'gym', 'crossfit', 'deportes', 'energia', 'startup'],
            styles: {
                fontFamily: "'Montserrat', 'Oswald', sans-serif",
                fontWeight: { heading: 800, body: 500 },
                letterSpacing: '-0.02em',
                borderRadius: '4px',
                animationSpeed: '0.3s',
                animationEasing: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
                shadowIntensity: 'strong',
                glassOpacity: 0.6
            },
            colors: {
                recommended: ['#dc2626', '#ea580c', '#f97316', '#000000'],
                accent: ['#fbbf24', '#f59e0b', '#eab308']
            },
            ctas: {
                primary: ['¡EMPIEZA AHORA!', '¡ÚNETE HOY!', '¡TRANSFORMA TU VIDA!'],
                secondary: ['Ver planes', 'Conocer más', 'Prueba gratis']
            },
            animations: {
                hero: 'zoomIn',
                cards: 'flipIn',
                hover: 'aggressive-scale',
                scroll: 'slide-fast'
            }
        },

        minimal: {
            name: 'Minimalista',
            description: 'Limpio, elegante, sofisticado',
            industries: ['arquitectura', 'diseno', 'fotografia', 'arte', 'moda', 'joyeria'],
            styles: {
                fontFamily: "'Playfair Display', 'Cormorant', serif",
                fontWeight: { heading: 400, body: 300 },
                letterSpacing: '0.05em',
                borderRadius: '0px',
                animationSpeed: '0.6s',
                animationEasing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                shadowIntensity: 'none',
                glassOpacity: 0.95
            },
            colors: {
                recommended: ['#1a1a1a', '#262626', '#404040', '#525252'],
                accent: ['#a3a3a3', '#737373', '#d4d4d4']
            },
            ctas: {
                primary: ['Explorar', 'Descubrir', 'Ver portfolio'],
                secondary: ['Contacto', 'Sobre nosotros', 'Proyectos']
            },
            animations: {
                hero: 'fadeIn',
                cards: 'fadeIn',
                hover: 'underline-grow',
                scroll: 'fade-slow'
            }
        },

        tech: {
            name: 'Tecnológico',
            description: 'Futurista, innovador, digital',
            industries: ['software', 'tecnologia', 'saas', 'app', 'gaming', 'crypto'],
            styles: {
                fontFamily: "'Space Grotesk', 'JetBrains Mono', monospace",
                fontWeight: { heading: 700, body: 400 },
                letterSpacing: '-0.01em',
                borderRadius: '12px',
                animationSpeed: '0.35s',
                animationEasing: 'cubic-bezier(0.4, 0, 0.2, 1)',
                shadowIntensity: 'glow',
                glassOpacity: 0.7
            },
            colors: {
                recommended: ['#6366f1', '#8b5cf6', '#a855f7', '#7c3aed'],
                accent: ['#06b6d4', '#22d3ee', '#67e8f9']
            },
            ctas: {
                primary: ['Comenzar gratis', 'Probar demo', 'Registrarse'],
                secondary: ['Ver features', 'Documentación', 'Precios']
            },
            animations: {
                hero: 'glitchIn',
                cards: 'slideScale',
                hover: 'glow-pulse',
                scroll: 'parallax-fade'
            }
        },

        health: {
            name: 'Salud',
            description: 'Confiable, calmado, profesional',
            industries: ['medico', 'clinica', 'hospital', 'dentista', 'veterinaria', 'farmacia', 'psicologia'],
            styles: {
                fontFamily: "'Source Sans Pro', 'Open Sans', sans-serif",
                fontWeight: { heading: 600, body: 400 },
                letterSpacing: '0.01em',
                borderRadius: '12px',
                animationSpeed: '0.5s',
                animationEasing: 'cubic-bezier(0.4, 0, 0.2, 1)',
                shadowIntensity: 'soft',
                glassOpacity: 0.85
            },
            colors: {
                recommended: ['#0891b2', '#0e7490', '#06b6d4', '#0284c7'],
                accent: ['#10b981', '#059669', '#34d399']
            },
            ctas: {
                primary: ['Agendar cita', 'Consultar ahora', 'Pedir turno'],
                secondary: ['Ver servicios', 'Conocer equipo', 'Ubicación']
            },
            animations: {
                hero: 'fadeInUp',
                cards: 'fadeIn',
                hover: 'gentle-lift',
                scroll: 'fade-gentle'
            }
        }
    },

    /**
     * Detecta la personalidad basada en la industria
     */
    detectPersonality(industry) {
        const industryLower = industry.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        
        for (const [key, personality] of Object.entries(this.personalities)) {
            if (personality.industries.some(ind => industryLower.includes(ind))) {
                return key;
            }
        }
        
        // Default: professional
        return 'professional';
    },

    /**
     * Obtiene la configuración de personalidad
     */
    getPersonality(personalityKey) {
        return this.personalities[personalityKey] || this.personalities.professional;
    },

    /**
     * Genera CSS variables para la personalidad
     */
    generateCSSVariables(personalityKey) {
        const p = this.getPersonality(personalityKey);
        const s = p.styles;
        
        return `
/* Personality: ${p.name} */
:root {
    --personality-font-family: ${s.fontFamily};
    --personality-heading-weight: ${s.fontWeight.heading};
    --personality-body-weight: ${s.fontWeight.body};
    --personality-letter-spacing: ${s.letterSpacing};
    --personality-border-radius: ${s.borderRadius};
    --personality-animation-speed: ${s.animationSpeed};
    --personality-animation-easing: ${s.animationEasing};
    --personality-glass-opacity: ${s.glassOpacity};
}

/* Apply personality to elements */
body {
    font-family: var(--personality-font-family);
    font-weight: var(--personality-body-weight);
    letter-spacing: var(--personality-letter-spacing);
}

h1, h2, h3, h4, h5, h6 {
    font-weight: var(--personality-heading-weight);
}

.btn, button, .card, .feature-card, .testimonial-card {
    border-radius: var(--personality-border-radius);
    transition: all var(--personality-animation-speed) var(--personality-animation-easing);
}
`;
    },

    /**
     * Genera animaciones CSS según personalidad
     */
    generateAnimations(personalityKey) {
        const p = this.getPersonality(personalityKey);
        const anims = p.animations;
        
        const animationDefinitions = {
            // Hero animations
            fadeInUp: `
@keyframes personality-hero {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}`,
            bounceIn: `
@keyframes personality-hero {
    0% { opacity: 0; transform: scale(0.3); }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { opacity: 1; transform: scale(1); }
}`,
            zoomIn: `
@keyframes personality-hero {
    from { opacity: 0; transform: scale(0.5); }
    to { opacity: 1; transform: scale(1); }
}`,
            fadeIn: `
@keyframes personality-hero {
    from { opacity: 0; }
    to { opacity: 1; }
}`,
            glitchIn: `
@keyframes personality-hero {
    0% { opacity: 0; transform: translateX(-10px); filter: blur(5px); }
    20% { transform: translateX(10px); }
    40% { transform: translateX(-5px); }
    60% { transform: translateX(5px); filter: blur(0); }
    100% { opacity: 1; transform: translateX(0); }
}`,

            // Hover effects
            'subtle-lift': `
.personality-hover:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }`,
            'bounce-scale': `
.personality-hover:hover { transform: scale(1.05); }`,
            'aggressive-scale': `
.personality-hover:hover { transform: scale(1.08) translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }`,
            'underline-grow': `
.personality-hover { position: relative; }
.personality-hover::after { content: ''; position: absolute; bottom: 0; left: 50%; width: 0; height: 1px; background: currentColor; transition: all 0.3s; }
.personality-hover:hover::after { left: 0; width: 100%; }`,
            'gentle-lift': `
.personality-hover:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }`,
            'glow-pulse': `
.personality-hover:hover { box-shadow: 0 0 30px var(--glow-primary, rgba(99, 102, 241, 0.5)); }`
        };

        let css = `/* Personality Animations: ${p.name} */\n`;
        css += animationDefinitions[anims.hero] || animationDefinitions.fadeInUp;
        css += '\n';
        css += animationDefinitions[anims.hover] || animationDefinitions['subtle-lift'];
        css += '\n';
        css += `
.hero-content, .hero-title, .hero-subtitle {
    animation: personality-hero var(--personality-animation-speed) var(--personality-animation-easing) forwards;
}
`;
        return css;
    },

    /**
     * Aplica personalidad al documento
     */
    applyToDocument(personalityKey) {
        const styleId = 'personality-engine-styles';
        let styleEl = document.getElementById(styleId);
        
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = styleId;
            document.head.appendChild(styleEl);
        }

        const css = this.generateCSSVariables(personalityKey) + '\n' + this.generateAnimations(personalityKey);
        styleEl.textContent = css;

        // Agregar clase al body
        document.body.className = document.body.className.replace(/personality-\w+/g, '');
        document.body.classList.add(`personality-${personalityKey}`);

        console.log(`🎭 Personalidad aplicada: ${this.getPersonality(personalityKey).name}`);
    },

    /**
     * Obtiene CTAs recomendados
     */
    getRecommendedCTAs(personalityKey) {
        const p = this.getPersonality(personalityKey);
        return {
            primary: p.ctas.primary[Math.floor(Math.random() * p.ctas.primary.length)],
            secondary: p.ctas.secondary[Math.floor(Math.random() * p.ctas.secondary.length)]
        };
    },

    /**
     * Genera CSS completo para uso estático
     */
    generateFullCSS(personalityKey) {
        return this.generateCSSVariables(personalityKey) + '\n' + this.generateAnimations(personalityKey);
    }
};

// Exponer globalmente
if (typeof window !== 'undefined') {
    window.PersonalityEngine = PersonalityEngine;
}

// Export para Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PersonalityEngine;
}
