/**
 * COLOR PALETTE GENERATOR 2025
 * Genera automáticamente 9 tonos de cada color de marca
 * Uso: Importar y llamar generateAndApplyPalette('#667eea')
 */

'use strict';

const ColorPalette = {
    /**
     * Convierte HEX a HSL
     */
    hexToHSL(hex) {
        hex = hex.replace('#', '');
        const r = parseInt(hex.substring(0, 2), 16) / 255;
        const g = parseInt(hex.substring(2, 4), 16) / 255;
        const b = parseInt(hex.substring(4, 6), 16) / 255;

        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;

        if (max === min) {
            h = s = 0;
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
                case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                case g: h = ((b - r) / d + 2) / 6; break;
                case b: h = ((r - g) / d + 4) / 6; break;
            }
        }

        return {
            h: Math.round(h * 360),
            s: Math.round(s * 100),
            l: Math.round(l * 100)
        };
    },

    /**
     * Convierte HSL a HEX
     */
    hslToHex(h, s, l) {
        s /= 100;
        l /= 100;
        const a = s * Math.min(l, 1 - l);
        const f = n => {
            const k = (n + h / 30) % 12;
            const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
            return Math.round(255 * color).toString(16).padStart(2, '0');
        };
        return `#${f(0)}${f(8)}${f(4)}`;
    },

    /**
     * Genera paleta completa de 9 tonos
     */
    generatePalette(hex) {
        const hsl = this.hexToHSL(hex);
        
        // Lightness values para cada tono (50-900)
        const lightnessMap = {
            50: 97,
            100: 94,
            200: 86,
            300: 77,
            400: 66,
            500: 55,  // Color base aproximado
            600: 45,
            700: 35,
            800: 25,
            900: 15
        };

        // Ajustar saturación según lightness
        const saturationAdjust = {
            50: -10,
            100: -5,
            200: 0,
            300: 0,
            400: 0,
            500: 0,
            600: 5,
            700: 5,
            800: 10,
            900: 10
        };

        const palette = {};
        
        for (const [shade, lightness] of Object.entries(lightnessMap)) {
            const adjustedSat = Math.min(100, Math.max(0, hsl.s + saturationAdjust[shade]));
            palette[shade] = `hsl(${hsl.h}, ${adjustedSat}%, ${lightness}%)`;
            palette[`${shade}-hex`] = this.hslToHex(hsl.h, adjustedSat, lightness);
        }

        return palette;
    },

    /**
     * Genera colores complementarios
     */
    generateComplementary(hex) {
        const hsl = this.hexToHSL(hex);
        
        return {
            complementary: `hsl(${(hsl.h + 180) % 360}, ${hsl.s}%, ${hsl.l}%)`,
            analogous1: `hsl(${(hsl.h + 30) % 360}, ${hsl.s}%, ${hsl.l}%)`,
            analogous2: `hsl(${(hsl.h - 30 + 360) % 360}, ${hsl.s}%, ${hsl.l}%)`,
            triadic1: `hsl(${(hsl.h + 120) % 360}, ${hsl.s}%, ${hsl.l}%)`,
            triadic2: `hsl(${(hsl.h + 240) % 360}, ${hsl.s}%, ${hsl.l}%)`
        };
    },

    /**
     * Genera colores para glassmorphism
     */
    generateGlassColors(hex) {
        const hsl = this.hexToHSL(hex);
        
        return {
            glassLight: `hsla(${hsl.h}, ${hsl.s}%, 98%, 0.7)`,
            glassDark: `hsla(${hsl.h}, ${hsl.s}%, 10%, 0.8)`,
            glowPrimary: `hsla(${hsl.h}, ${hsl.s}%, ${hsl.l}%, 0.4)`,
            glowSecondary: `hsla(${hsl.h}, ${hsl.s}%, ${hsl.l}%, 0.2)`,
            shadowColored: `0 10px 40px hsla(${hsl.h}, ${hsl.s}%, ${hsl.l}%, 0.3)`
        };
    },

    /**
     * Aplica la paleta como CSS variables
     */
    applyToDocument(primaryHex, secondaryHex = null) {
        const root = document.documentElement;
        
        // Paleta primaria
        const primaryPalette = this.generatePalette(primaryHex);
        for (const [shade, color] of Object.entries(primaryPalette)) {
            if (!shade.includes('hex')) {
                root.style.setProperty(`--primary-${shade}`, color);
            }
        }

        // Paleta secundaria (si existe)
        if (secondaryHex) {
            const secondaryPalette = this.generatePalette(secondaryHex);
            for (const [shade, color] of Object.entries(secondaryPalette)) {
                if (!shade.includes('hex')) {
                    root.style.setProperty(`--secondary-${shade}`, color);
                }
            }
        }

        // Colores glass
        const glassColors = this.generateGlassColors(primaryHex);
        root.style.setProperty('--glass-bg', glassColors.glassLight);
        root.style.setProperty('--glass-bg-dark', glassColors.glassDark);
        root.style.setProperty('--glow-primary', glassColors.glowPrimary);
        root.style.setProperty('--glow-secondary', glassColors.glowSecondary);
        root.style.setProperty('--shadow-colored', glassColors.shadowColored);

        console.log('🎨 Paleta de colores aplicada:', { primary: primaryHex, secondary: secondaryHex });
    },

    /**
     * Genera CSS string para usar en generación estática
     */
    generateCSSString(primaryHex, secondaryHex = null) {
        const primaryPalette = this.generatePalette(primaryHex);
        const glassColors = this.generateGlassColors(primaryHex);
        
        let css = ':root {\n';
        css += `    /* Primary Palette - Generated from ${primaryHex} */\n`;
        
        for (const [shade, color] of Object.entries(primaryPalette)) {
            if (!shade.includes('hex')) {
                css += `    --primary-${shade}: ${color};\n`;
            }
        }

        if (secondaryHex) {
            const secondaryPalette = this.generatePalette(secondaryHex);
            css += `\n    /* Secondary Palette - Generated from ${secondaryHex} */\n`;
            for (const [shade, color] of Object.entries(secondaryPalette)) {
                if (!shade.includes('hex')) {
                    css += `    --secondary-${shade}: ${color};\n`;
                }
            }
        }

        css += `\n    /* Glass & Glow Effects */\n`;
        css += `    --glass-bg: ${glassColors.glassLight};\n`;
        css += `    --glass-bg-dark: ${glassColors.glassDark};\n`;
        css += `    --glow-primary: ${glassColors.glowPrimary};\n`;
        css += `    --glow-secondary: ${glassColors.glowSecondary};\n`;
        css += `    --shadow-colored: ${glassColors.shadowColored};\n`;
        css += '}\n';

        return css;
    }
};

// Exponer globalmente
if (typeof window !== 'undefined') {
    window.ColorPalette = ColorPalette;
}

// Export para Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ColorPalette;
}
