/**
 * INDUSTRY DETECTOR 2025
 * Detecta automáticamente la industria/sector del negocio
 * Basado en keywords, servicios y descripción
 */

'use strict';

const IndustryDetector = {
    
    /**
     * Patrones de detección por industria
     */
    patterns: {
        medico: {
            keywords: ['médico', 'medico', 'doctor', 'doctora', 'clínica', 'clinica', 'hospital', 'salud', 'medicina', 'paciente', 'consulta médica', 'diagnóstico', 'tratamiento', 'especialista', 'consultorio'],
            services: ['consulta', 'diagnóstico', 'tratamiento', 'cirugía', 'laboratorio', 'radiología', 'ecografía'],
            weight: 1
        },
        veterinaria: {
            keywords: ['veterinaria', 'veterinario', 'mascota', 'mascotas', 'perro', 'gato', 'animal', 'animales', 'canino', 'felino', 'pet', 'pets'],
            services: ['vacunación', 'desparasitación', 'cirugía', 'peluquería canina', 'guardería', 'internación'],
            weight: 1.2
        },
        fitness: {
            keywords: ['gimnasio', 'gym', 'fitness', 'crossfit', 'entrenamiento', 'musculación', 'ejercicio', 'deporte', 'entrenar', 'pesas', 'cardio'],
            services: ['musculación', 'spinning', 'yoga', 'pilates', 'funcional', 'personal trainer', 'clases grupales'],
            weight: 1
        },
        restaurante: {
            keywords: ['restaurante', 'restaurant', 'comida', 'gastronomía', 'gastronomia', 'cocina', 'chef', 'menú', 'menu', 'plato', 'sabor', 'bar', 'café', 'cafe', 'cafetería'],
            services: ['almuerzo', 'cena', 'delivery', 'catering', 'eventos', 'reservas'],
            weight: 1
        },
        legal: {
            keywords: ['abogado', 'abogada', 'abogados', 'estudio jurídico', 'estudio juridico', 'legal', 'derecho', 'ley', 'leyes', 'juicio', 'demanda', 'defensa'],
            services: ['asesoría legal', 'litigios', 'contratos', 'sucesiones', 'divorcios', 'laboral', 'penal', 'civil'],
            weight: 1
        },
        tecnologia: {
            keywords: ['software', 'desarrollo', 'programación', 'programacion', 'app', 'aplicación', 'web', 'tecnología', 'tecnologia', 'digital', 'sistemas', 'código', 'codigo'],
            services: ['desarrollo web', 'apps móviles', 'software a medida', 'consultoría IT', 'cloud', 'devops', 'inteligencia artificial'],
            weight: 1
        },
        inmobiliaria: {
            keywords: ['inmobiliaria', 'inmueble', 'inmuebles', 'propiedad', 'propiedades', 'casa', 'departamento', 'alquiler', 'venta', 'terreno', 'lote'],
            services: ['venta', 'alquiler', 'tasación', 'administración', 'asesoramiento'],
            weight: 1
        },
        educacion: {
            keywords: ['educación', 'educacion', 'escuela', 'colegio', 'instituto', 'academia', 'curso', 'cursos', 'capacitación', 'capacitacion', 'formación', 'formacion', 'aprender', 'enseñar'],
            services: ['cursos', 'talleres', 'capacitaciones', 'clases particulares', 'tutorías'],
            weight: 1
        },
        belleza: {
            keywords: ['belleza', 'estética', 'estetica', 'peluquería', 'peluqueria', 'spa', 'salón', 'salon', 'uñas', 'maquillaje', 'cabello', 'tratamiento facial'],
            services: ['corte', 'color', 'manicura', 'pedicura', 'depilación', 'masajes', 'tratamientos faciales'],
            weight: 1
        },
        automotriz: {
            keywords: ['auto', 'autos', 'automóvil', 'automovil', 'vehículo', 'vehiculo', 'coche', 'taller', 'mecánico', 'mecanico', 'repuesto', 'concesionaria'],
            services: ['service', 'reparación', 'mantenimiento', 'repuestos', 'venta de autos', 'lavadero'],
            weight: 1
        },
        construccion: {
            keywords: ['construcción', 'construccion', 'obra', 'obras', 'arquitecto', 'arquitectura', 'ingeniero', 'ingeniería', 'remodelación', 'remodelacion', 'albañil'],
            services: ['construcción', 'remodelación', 'diseño', 'planos', 'presupuestos', 'refacciones'],
            weight: 1
        },
        contabilidad: {
            keywords: ['contador', 'contadora', 'contabilidad', 'contable', 'impuestos', 'monotributo', 'facturación', 'facturacion', 'balance', 'auditoría', 'auditoria'],
            services: ['liquidación de sueldos', 'impuestos', 'balances', 'auditoría', 'asesoramiento contable'],
            weight: 1
        },
        seguros: {
            keywords: ['seguro', 'seguros', 'aseguradora', 'póliza', 'poliza', 'cobertura', 'siniestro', 'productor de seguros'],
            services: ['seguro de auto', 'seguro de vida', 'seguro de hogar', 'ART', 'seguro de salud'],
            weight: 1
        },
        marketing: {
            keywords: ['marketing', 'publicidad', 'agencia', 'redes sociales', 'social media', 'branding', 'diseño gráfico', 'diseño grafico', 'comunicación', 'comunicacion'],
            services: ['community manager', 'diseño gráfico', 'publicidad digital', 'branding', 'estrategia'],
            weight: 1
        }
    },

    /**
     * Detecta la industria basándose en texto
     */
    detect(text, services = []) {
        if (!text && services.length === 0) {
            return { industry: 'general', confidence: 0, personality: 'professional' };
        }

        const normalizedText = this.normalizeText(text);
        const normalizedServices = services.map(s => this.normalizeText(s.name || s));
        
        const scores = {};

        for (const [industry, pattern] of Object.entries(this.patterns)) {
            let score = 0;

            // Buscar keywords en texto
            for (const keyword of pattern.keywords) {
                const normalizedKeyword = this.normalizeText(keyword);
                if (normalizedText.includes(normalizedKeyword)) {
                    score += 10 * pattern.weight;
                }
            }

            // Buscar en servicios
            for (const service of pattern.services) {
                const normalizedService = this.normalizeText(service);
                if (normalizedText.includes(normalizedService)) {
                    score += 15 * pattern.weight;
                }
                // También buscar en lista de servicios proporcionada
                for (const providedService of normalizedServices) {
                    if (providedService.includes(normalizedService) || normalizedService.includes(providedService)) {
                        score += 20 * pattern.weight;
                    }
                }
            }

            scores[industry] = score;
        }

        // Encontrar la industria con mayor score
        const sortedIndustries = Object.entries(scores)
            .sort((a, b) => b[1] - a[1]);

        if (sortedIndustries[0][1] === 0) {
            return { industry: 'general', confidence: 0, personality: 'professional' };
        }

        const topIndustry = sortedIndustries[0][0];
        const topScore = sortedIndustries[0][1];
        const secondScore = sortedIndustries[1] ? sortedIndustries[1][1] : 0;

        // Calcular confianza
        const confidence = Math.min(100, Math.round((topScore / (topScore + secondScore + 10)) * 100));

        // Mapear a personalidad
        const personalityMap = {
            medico: 'health',
            veterinaria: 'health',
            fitness: 'bold',
            restaurante: 'friendly',
            legal: 'professional',
            tecnologia: 'tech',
            inmobiliaria: 'professional',
            educacion: 'friendly',
            belleza: 'friendly',
            automotriz: 'bold',
            construccion: 'professional',
            contabilidad: 'professional',
            seguros: 'professional',
            marketing: 'tech'
        };

        return {
            industry: topIndustry,
            confidence,
            personality: personalityMap[topIndustry] || 'professional',
            allScores: scores
        };
    },

    /**
     * Normaliza texto para comparación
     */
    normalizeText(text) {
        if (!text) return '';
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // Quitar acentos
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    },

    /**
     * Detecta desde un objeto de negocio completo
     */
    detectFromBusiness(businessData) {
        const textParts = [];

        // Recopilar todo el texto relevante
        if (businessData.nombre) textParts.push(businessData.nombre);
        if (businessData.descripcion) textParts.push(businessData.descripcion);
        if (businessData.rubro) textParts.push(businessData.rubro);
        if (businessData.categoria) textParts.push(businessData.categoria);
        
        // Servicios
        const services = [];
        if (businessData.servicios) {
            if (Array.isArray(businessData.servicios)) {
                services.push(...businessData.servicios);
            } else if (businessData.servicios.items) {
                services.push(...businessData.servicios.items);
            }
        }

        // Hero y sobre nosotros
        if (businessData.hero?.titulo) textParts.push(businessData.hero.titulo);
        if (businessData.sobre_nosotros?.descripcion) textParts.push(businessData.sobre_nosotros.descripcion);

        const fullText = textParts.join(' ');
        return this.detect(fullText, services);
    },

    /**
     * Obtiene configuración recomendada para la industria
     */
    getIndustryConfig(industry) {
        const configs = {
            medico: {
                colors: { primary: '#0891b2', secondary: '#06b6d4', accent: '#10b981' },
                emoji: '🏥',
                trustBadges: ['Profesionales matriculados', 'Equipamiento moderno', 'Turnos online'],
                urgencyLevel: 'medium'
            },
            veterinaria: {
                colors: { primary: '#059669', secondary: '#10b981', accent: '#f59e0b' },
                emoji: '🐾',
                trustBadges: ['Urgencias 24hs', 'Veterinarios certificados', 'Amor por los animales'],
                urgencyLevel: 'high'
            },
            fitness: {
                colors: { primary: '#dc2626', secondary: '#f97316', accent: '#fbbf24' },
                emoji: '💪',
                trustBadges: ['Entrenadores certificados', 'Equipamiento premium', 'Resultados garantizados'],
                urgencyLevel: 'high'
            },
            restaurante: {
                colors: { primary: '#ea580c', secondary: '#f97316', accent: '#84cc16' },
                emoji: '🍽️',
                trustBadges: ['Ingredientes frescos', 'Chef profesional', 'Ambiente acogedor'],
                urgencyLevel: 'medium'
            },
            legal: {
                colors: { primary: '#1e3a5f', secondary: '#2c5282', accent: '#c9a227' },
                emoji: '⚖️',
                trustBadges: ['Abogados matriculados', 'Confidencialidad total', 'Primera consulta gratis'],
                urgencyLevel: 'low'
            },
            tecnologia: {
                colors: { primary: '#6366f1', secondary: '#8b5cf6', accent: '#06b6d4' },
                emoji: '💻',
                trustBadges: ['Equipo senior', 'Metodología ágil', 'Soporte 24/7'],
                urgencyLevel: 'medium'
            },
            general: {
                colors: { primary: '#667eea', secondary: '#764ba2', accent: '#f093fb' },
                emoji: '🏢',
                trustBadges: ['Profesionales capacitados', 'Atención personalizada', 'Calidad garantizada'],
                urgencyLevel: 'medium'
            }
        };

        return configs[industry] || configs.general;
    }
};

// Exponer globalmente
if (typeof window !== 'undefined') {
    window.IndustryDetector = IndustryDetector;
}

// Export para Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IndustryDetector;
}
