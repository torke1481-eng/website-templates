/**
 * QUALITY GATE 2025
 * Sistema de validación de calidad para HTML generado
 * Verifica SEO, accesibilidad, performance y contenido
 */

'use strict';

const QualityGate = {
    
    /**
     * Configuración de checks
     */
    config: {
        minScore: 80,
        weights: {
            seo: 25,
            accessibility: 25,
            performance: 20,
            content: 20,
            security: 10
        }
    },

    /**
     * Ejecuta todos los checks
     */
    validate(html, options = {}) {
        const results = {
            seo: this.checkSEO(html),
            accessibility: this.checkAccessibility(html),
            performance: this.checkPerformance(html),
            content: this.checkContent(html, options),
            security: this.checkSecurity(html)
        };

        const score = this.calculateScore(results);
        const passed = score >= this.config.minScore;

        return {
            passed,
            score,
            results,
            summary: this.generateSummary(results, score),
            recommendations: this.getRecommendations(results)
        };
    },

    /**
     * Checks de SEO
     */
    checkSEO(html) {
        const checks = {
            hasDoctype: {
                passed: html.trim().toLowerCase().startsWith('<!doctype html>'),
                message: 'DOCTYPE HTML presente',
                weight: 10
            },
            hasTitle: {
                passed: /<title>[^<]+<\/title>/i.test(html),
                message: 'Título de página presente',
                weight: 15
            },
            titleLength: {
                passed: (() => {
                    const match = html.match(/<title>([^<]+)<\/title>/i);
                    return match && match[1].length >= 30 && match[1].length <= 60;
                })(),
                message: 'Título entre 30-60 caracteres',
                weight: 10
            },
            hasMetaDescription: {
                passed: /<meta\s+name=["']description["']\s+content=["'][^"']+["']/i.test(html),
                message: 'Meta description presente',
                weight: 15
            },
            metaDescriptionLength: {
                passed: (() => {
                    const match = html.match(/<meta\s+name=["']description["']\s+content=["']([^"']+)["']/i);
                    return match && match[1].length >= 120 && match[1].length <= 160;
                })(),
                message: 'Meta description entre 120-160 caracteres',
                weight: 10
            },
            hasH1: {
                passed: /<h1[^>]*>[^<]+<\/h1>/i.test(html),
                message: 'Encabezado H1 presente',
                weight: 15
            },
            singleH1: {
                passed: (html.match(/<h1/gi) || []).length === 1,
                message: 'Solo un H1 en la página',
                weight: 10
            },
            hasCanonical: {
                passed: /<link\s+rel=["']canonical["']/i.test(html),
                message: 'URL canónica definida',
                weight: 5
            },
            hasOpenGraph: {
                passed: /<meta\s+property=["']og:/i.test(html),
                message: 'Open Graph tags presentes',
                weight: 5
            },
            hasSchema: {
                passed: /<script\s+type=["']application\/ld\+json["']/i.test(html),
                message: 'Schema.org JSON-LD presente',
                weight: 5
            }
        };

        return this.evaluateChecks(checks);
    },

    /**
     * Checks de Accesibilidad
     */
    checkAccessibility(html) {
        const checks = {
            hasLang: {
                passed: /<html[^>]+lang=["'][a-z]{2}/i.test(html),
                message: 'Atributo lang en HTML',
                weight: 15
            },
            hasViewport: {
                passed: /<meta\s+name=["']viewport["']/i.test(html),
                message: 'Meta viewport presente',
                weight: 10
            },
            imagesHaveAlt: {
                passed: (() => {
                    const imgs = html.match(/<img[^>]+>/gi) || [];
                    const withAlt = imgs.filter(img => /alt=["'][^"']*["']/i.test(img));
                    return imgs.length === 0 || withAlt.length === imgs.length;
                })(),
                message: 'Todas las imágenes tienen alt',
                weight: 20
            },
            noEmptyAlt: {
                passed: !/<img[^>]+alt=["']\s*["']/i.test(html),
                message: 'No hay alt vacíos',
                weight: 10
            },
            hasSkipLink: {
                passed: /skip-link|skip-to-content|skip-nav/i.test(html),
                message: 'Skip link para navegación',
                weight: 10
            },
            buttonsHaveText: {
                passed: (() => {
                    const buttons = html.match(/<button[^>]*>[\s\S]*?<\/button>/gi) || [];
                    return buttons.every(btn => {
                        const hasText = /<button[^>]*>[^<]*\S[^<]*<\/button>/i.test(btn);
                        const hasAriaLabel = /aria-label=["'][^"']+["']/i.test(btn);
                        return hasText || hasAriaLabel;
                    });
                })(),
                message: 'Botones tienen texto o aria-label',
                weight: 15
            },
            hasMainLandmark: {
                passed: /<main/i.test(html) || /role=["']main["']/i.test(html),
                message: 'Landmark main presente',
                weight: 10
            },
            hasHeaderFooter: {
                passed: /<header/i.test(html) && /<footer/i.test(html),
                message: 'Header y footer presentes',
                weight: 10
            }
        };

        return this.evaluateChecks(checks);
    },

    /**
     * Checks de Performance
     */
    checkPerformance(html) {
        const checks = {
            noCSSInBody: {
                passed: !/<body[\s\S]*<style/i.test(html.split('</head>')[1] || ''),
                message: 'CSS no está en el body',
                weight: 15
            },
            hasPreconnect: {
                passed: /<link\s+rel=["']preconnect["']/i.test(html),
                message: 'Preconnect para recursos externos',
                weight: 10
            },
            lazyImages: {
                passed: (() => {
                    const imgs = html.match(/<img[^>]+>/gi) || [];
                    const belowFold = imgs.slice(1); // Ignorar primera imagen
                    if (belowFold.length === 0) return true;
                    return belowFold.some(img => /loading=["']lazy["']/i.test(img));
                })(),
                message: 'Imágenes con lazy loading',
                weight: 15
            },
            noBlockingScripts: {
                passed: (() => {
                    const headScripts = (html.split('</head>')[0] || '').match(/<script[^>]+src=/gi) || [];
                    return headScripts.every(s => /defer|async/i.test(s));
                })(),
                message: 'Scripts no bloquean renderizado',
                weight: 20
            },
            hasCriticalCSS: {
                passed: /<style[^>]*>[\s\S]{100,}<\/style>/i.test(html.split('</head>')[0] || ''),
                message: 'CSS crítico inline',
                weight: 15
            },
            reasonableSize: {
                passed: html.length < 500000, // < 500KB
                message: 'Tamaño de HTML razonable (<500KB)',
                weight: 15
            },
            noInlineStyles: {
                passed: (html.match(/style=["'][^"']{50,}["']/gi) || []).length < 10,
                message: 'Pocos estilos inline largos',
                weight: 10
            }
        };

        return this.evaluateChecks(checks);
    },

    /**
     * Checks de Contenido
     */
    checkContent(html, options = {}) {
        const businessName = options.businessName || '';
        const whatsapp = options.whatsapp || '';

        const checks = {
            noPlaceholders: {
                passed: !/\{\{[^}]+\}\}/.test(html) && !/\[placeholder\]/i.test(html),
                message: 'Sin placeholders sin reemplazar',
                weight: 25
            },
            hasBusinessName: {
                passed: !businessName || html.toLowerCase().includes(businessName.toLowerCase()),
                message: 'Nombre del negocio presente',
                weight: 15
            },
            hasContactInfo: {
                passed: /wa\.me\/\d+|tel:|mailto:|whatsapp/i.test(html),
                message: 'Información de contacto presente',
                weight: 20
            },
            whatsappCorrect: {
                passed: !whatsapp || html.includes(`wa.me/${whatsapp}`),
                message: 'WhatsApp correctamente formateado',
                weight: 15
            },
            hasCTA: {
                passed: /btn|button|cta/i.test(html) && /<button|<a[^>]+class=["'][^"']*btn/i.test(html),
                message: 'Call-to-action presente',
                weight: 15
            },
            hasFooter: {
                passed: /<footer[\s\S]*<\/footer>/i.test(html),
                message: 'Footer con contenido',
                weight: 10
            }
        };

        return this.evaluateChecks(checks);
    },

    /**
     * Checks de Seguridad
     */
    checkSecurity(html) {
        const checks = {
            noInlineJS: {
                passed: !/onclick=["'](?!.*openWhatsApp|.*scrollTo|.*toggle)/i.test(html),
                message: 'Sin JavaScript inline peligroso',
                weight: 20
            },
            externalLinksSecure: {
                passed: (() => {
                    const externalLinks = html.match(/href=["']https?:\/\/(?!wa\.me)[^"']+["']/gi) || [];
                    return externalLinks.every(link => 
                        /rel=["'][^"']*noopener[^"']*["']/i.test(html.substring(
                            html.indexOf(link) - 100, 
                            html.indexOf(link) + link.length + 100
                        ))
                    );
                })(),
                message: 'Links externos con noopener',
                weight: 25
            },
            noSensitiveData: {
                passed: !/api[_-]?key|password|secret|token/i.test(html),
                message: 'Sin datos sensibles expuestos',
                weight: 30
            },
            httpsLinks: {
                passed: !/<a[^>]+href=["']http:\/\//i.test(html),
                message: 'Links usan HTTPS',
                weight: 15
            },
            noEval: {
                passed: !/eval\(|Function\(|setTimeout\(['"]/i.test(html),
                message: 'Sin uso de eval o similar',
                weight: 10
            }
        };

        return this.evaluateChecks(checks);
    },

    /**
     * Evalúa un conjunto de checks
     */
    evaluateChecks(checks) {
        let totalWeight = 0;
        let earnedWeight = 0;
        const details = [];

        for (const [key, check] of Object.entries(checks)) {
            totalWeight += check.weight;
            if (check.passed) {
                earnedWeight += check.weight;
            }
            details.push({
                key,
                passed: check.passed,
                message: check.message,
                weight: check.weight
            });
        }

        return {
            score: Math.round((earnedWeight / totalWeight) * 100),
            passed: details.filter(d => d.passed).length,
            failed: details.filter(d => !d.passed).length,
            total: details.length,
            details
        };
    },

    /**
     * Calcula el score total ponderado
     */
    calculateScore(results) {
        let totalScore = 0;
        
        for (const [category, weight] of Object.entries(this.config.weights)) {
            if (results[category]) {
                totalScore += (results[category].score * weight) / 100;
            }
        }
        
        return Math.round(totalScore);
    },

    /**
     * Genera resumen legible
     */
    generateSummary(results, score) {
        const status = score >= 90 ? '🟢 Excelente' : 
                       score >= 80 ? '🟡 Bueno' : 
                       score >= 60 ? '🟠 Necesita mejoras' : '🔴 Crítico';

        let summary = `\n${status} - Score: ${score}/100\n\n`;
        
        for (const [category, result] of Object.entries(results)) {
            const emoji = result.score >= 80 ? '✅' : result.score >= 60 ? '⚠️' : '❌';
            summary += `${emoji} ${category.toUpperCase()}: ${result.score}% (${result.passed}/${result.total})\n`;
        }

        return summary;
    },

    /**
     * Obtiene recomendaciones de mejora
     */
    getRecommendations(results) {
        const recommendations = [];

        for (const [category, result] of Object.entries(results)) {
            for (const detail of result.details) {
                if (!detail.passed) {
                    recommendations.push({
                        category,
                        issue: detail.message,
                        priority: detail.weight >= 15 ? 'alta' : detail.weight >= 10 ? 'media' : 'baja'
                    });
                }
            }
        }

        // Ordenar por prioridad
        const priorityOrder = { alta: 0, media: 1, baja: 2 };
        recommendations.sort((a, b) => priorityOrder[a.priority] - priorityOrder[b.priority]);

        return recommendations;
    },

    /**
     * Valida y retorna resultado simple (para Make.com)
     */
    quickValidate(html, options = {}) {
        const result = this.validate(html, options);
        return {
            valid: result.passed,
            score: result.score,
            issues: result.recommendations.filter(r => r.priority === 'alta').map(r => r.issue)
        };
    }
};

// Exponer globalmente
if (typeof window !== 'undefined') {
    window.QualityGate = QualityGate;
}

// Export para Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QualityGate;
}
