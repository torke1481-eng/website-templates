/**
 * TEMPLATE TOOLKIT 2025
 * Bundle que integra todos los módulos del sistema de templates
 * 
 * Incluye:
 * - ColorPalette: Generador de paletas de colores
 * - PersonalityEngine: Motor de personalidad por industria
 * - IndustryDetector: Detector automático de industria
 * - SmartSections: Layouts adaptativos
 * - QualityGate: Validación de calidad
 */

'use strict';

// ============================================
// TEMPLATE TOOLKIT - INTEGRACIÓN
// ============================================

const TemplateToolkit = {
    version: '2.0.0',
    
    // Referencias a módulos (se cargan dinámicamente o se incluyen)
    modules: {
        ColorPalette: null,
        PersonalityEngine: null,
        IndustryDetector: null,
        SmartSections: null,
        QualityGate: null
    },

    /**
     * Inicializa el toolkit
     */
    init(options = {}) {
        // Cargar módulos si están disponibles globalmente
        this.modules.ColorPalette = window.ColorPalette || null;
        this.modules.PersonalityEngine = window.PersonalityEngine || null;
        this.modules.IndustryDetector = window.IndustryDetector || null;
        this.modules.SmartSections = window.SmartSections || null;
        this.modules.QualityGate = window.QualityGate || null;

        console.log('🛠️ Template Toolkit v' + this.version + ' inicializado');
        console.log('📦 Módulos disponibles:', Object.keys(this.modules).filter(k => this.modules[k]));

        // Auto-aplicar si hay datos en el DOM
        if (options.autoApply !== false) {
            this.autoApply();
        }

        return this;
    },

    /**
     * Procesa datos de negocio y aplica todo automáticamente
     */
    processBusinessData(businessData) {
        const result = {
            industry: null,
            personality: null,
            colors: null,
            recommendations: {}
        };

        // 1. Detectar industria
        if (this.modules.IndustryDetector) {
            const detection = this.modules.IndustryDetector.detectFromBusiness(businessData);
            result.industry = detection.industry;
            result.personality = detection.personality;
            result.industryConfig = this.modules.IndustryDetector.getIndustryConfig(detection.industry);
        }

        // 2. Aplicar personalidad
        if (this.modules.PersonalityEngine && result.personality) {
            this.modules.PersonalityEngine.applyToDocument(result.personality);
            result.recommendations.ctas = this.modules.PersonalityEngine.getRecommendedCTAs(result.personality);
        }

        // 3. Generar y aplicar paleta de colores
        if (this.modules.ColorPalette) {
            const colors = businessData.colores || result.industryConfig?.colors || {};
            const primary = colors.primary || '#667eea';
            const secondary = colors.secondary || '#764ba2';
            
            this.modules.ColorPalette.applyToDocument(primary, secondary);
            result.colors = { primary, secondary };
        }

        // 4. Aplicar smart sections
        if (this.modules.SmartSections) {
            this.modules.SmartSections.autoApply();
        }

        console.log('✅ Negocio procesado:', result);
        return result;
    },

    /**
     * Auto-detecta y aplica configuraciones
     */
    autoApply() {
        // Buscar datos de negocio en el DOM
        const businessDataEl = document.querySelector('[data-business-config]');
        if (businessDataEl) {
            try {
                const businessData = JSON.parse(businessDataEl.textContent || businessDataEl.getAttribute('data-business-config'));
                this.processBusinessData(businessData);
            } catch (e) {
                console.warn('No se pudo parsear datos de negocio:', e);
            }
        }

        // Aplicar smart sections de todas formas
        if (this.modules.SmartSections) {
            this.modules.SmartSections.autoApply();
        }
    },

    /**
     * Genera CSS completo para un negocio
     */
    generateFullCSS(businessData) {
        let css = '/* Template Toolkit - Generated CSS */\n\n';

        // Detectar industria
        let personality = 'professional';
        if (this.modules.IndustryDetector) {
            const detection = this.modules.IndustryDetector.detectFromBusiness(businessData);
            personality = detection.personality;
        }

        // Paleta de colores
        if (this.modules.ColorPalette) {
            const colors = businessData.colores || {};
            css += this.modules.ColorPalette.generateCSSString(
                colors.primary || '#667eea',
                colors.secondary || '#764ba2'
            );
            css += '\n';
        }

        // Personalidad
        if (this.modules.PersonalityEngine) {
            css += this.modules.PersonalityEngine.generateFullCSS(personality);
            css += '\n';
        }

        // Smart sections
        if (this.modules.SmartSections) {
            css += this.modules.SmartSections.generateAllCSS();
        }

        return css;
    },

    /**
     * Valida HTML generado
     */
    validateHTML(html, options = {}) {
        if (!this.modules.QualityGate) {
            return { valid: true, score: 100, message: 'QualityGate no disponible' };
        }

        return this.modules.QualityGate.validate(html, options);
    },

    /**
     * Obtiene recomendaciones para un negocio
     */
    getRecommendations(businessData) {
        const recommendations = {
            colors: null,
            ctas: null,
            trustBadges: null,
            personality: null
        };

        // Detectar industria
        if (this.modules.IndustryDetector) {
            const detection = this.modules.IndustryDetector.detectFromBusiness(businessData);
            const config = this.modules.IndustryDetector.getIndustryConfig(detection.industry);
            
            recommendations.colors = config.colors;
            recommendations.trustBadges = config.trustBadges;
            recommendations.personality = detection.personality;
            recommendations.emoji = config.emoji;
        }

        // CTAs recomendados
        if (this.modules.PersonalityEngine && recommendations.personality) {
            recommendations.ctas = this.modules.PersonalityEngine.getRecommendedCTAs(recommendations.personality);
        }

        return recommendations;
    },

    /**
     * Crea un preview rápido
     */
    createPreview(businessData, container) {
        if (typeof container === 'string') {
            container = document.querySelector(container);
        }
        if (!container) return;

        const recommendations = this.getRecommendations(businessData);
        
        // Aplicar estilos
        const css = this.generateFullCSS(businessData);
        const styleEl = document.createElement('style');
        styleEl.textContent = css;
        container.appendChild(styleEl);

        // Crear preview básico
        container.innerHTML += `
            <div class="preview-container" style="padding: 40px; background: var(--primary-50, #f5f5f5);">
                <div class="preview-header" style="text-align: center; margin-bottom: 40px;">
                    <span style="font-size: 48px;">${recommendations.emoji || '🏢'}</span>
                    <h1 style="margin: 16px 0 8px;">${businessData.nombre || 'Tu Negocio'}</h1>
                    <p style="color: var(--text-light, #666);">${businessData.descripcion || 'Descripción del negocio'}</p>
                </div>
                <div class="preview-ctas" style="display: flex; gap: 16px; justify-content: center;">
                    <button class="btn btn-primary" style="padding: 12px 24px; background: var(--primary-500, #667eea); color: white; border: none; border-radius: var(--personality-border-radius, 8px); cursor: pointer;">
                        ${recommendations.ctas?.primary || 'Contactar'}
                    </button>
                    <button class="btn btn-secondary" style="padding: 12px 24px; background: transparent; border: 2px solid var(--primary-500, #667eea); color: var(--primary-500, #667eea); border-radius: var(--personality-border-radius, 8px); cursor: pointer;">
                        ${recommendations.ctas?.secondary || 'Ver más'}
                    </button>
                </div>
                <div class="preview-badges" style="display: flex; gap: 12px; justify-content: center; margin-top: 24px; flex-wrap: wrap;">
                    ${(recommendations.trustBadges || []).map(badge => 
                        `<span style="padding: 8px 16px; background: white; border-radius: 20px; font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">${badge}</span>`
                    ).join('')}
                </div>
            </div>
        `;

        console.log('🎨 Preview creado');
    }
};

// Auto-inicializar cuando el DOM esté listo
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => TemplateToolkit.init());
    } else {
        TemplateToolkit.init();
    }
}

// Exponer globalmente
if (typeof window !== 'undefined') {
    window.TemplateToolkit = TemplateToolkit;
}

// Export para Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TemplateToolkit;
}
