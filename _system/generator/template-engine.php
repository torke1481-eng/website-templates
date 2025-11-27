<?php
/**
 * TEMPLATE ENGINE 2025
 * Motor de templates que integra:
 * - Color Palette Generator
 * - Personality Engine
 * - Industry Detector
 * - Smart Sections
 * - Quality Gate
 * 
 * Uso: Procesa JSON del negocio y genera HTML optimizado
 */

class TemplateEngine {
    
    private $industriesData;
    private $templatePath;
    
    public function __construct() {
        $this->templatePath = __DIR__ . '/../templates/';
        $this->loadIndustriesData();
    }
    
    /**
     * Carga datos de industrias
     */
    private function loadIndustriesData() {
        $jsonPath = $this->templatePath . 'content-blocks/industries.json';
        if (file_exists($jsonPath)) {
            $this->industriesData = json_decode(file_get_contents($jsonPath), true);
        } else {
            $this->industriesData = ['industries' => [], 'fallback' => []];
        }
    }
    
    /**
     * Detecta la industria del negocio
     */
    public function detectIndustry($businessData) {
        $text = strtolower($this->normalizeText(
            ($businessData['nombre'] ?? '') . ' ' .
            ($businessData['descripcion'] ?? '') . ' ' .
            ($businessData['rubro'] ?? '') . ' ' .
            ($businessData['categoria'] ?? '')
        ));
        
        $patterns = [
            'medico' => ['médico', 'medico', 'doctor', 'clínica', 'clinica', 'hospital', 'salud'],
            'veterinaria' => ['veterinaria', 'veterinario', 'mascota', 'perro', 'gato', 'animal'],
            'fitness' => ['gimnasio', 'gym', 'fitness', 'crossfit', 'entrenamiento'],
            'restaurante' => ['restaurante', 'comida', 'gastronomía', 'cocina', 'café', 'bar'],
            'legal' => ['abogado', 'abogados', 'estudio jurídico', 'legal', 'derecho'],
            'tecnologia' => ['software', 'desarrollo', 'programación', 'app', 'web', 'tecnología'],
        ];
        
        $scores = [];
        foreach ($patterns as $industry => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (strpos($text, $this->normalizeText($keyword)) !== false) {
                    $score += 10;
                }
            }
            $scores[$industry] = $score;
        }
        
        arsort($scores);
        $topIndustry = key($scores);
        
        if ($scores[$topIndustry] === 0) {
            return 'general';
        }
        
        return $topIndustry;
    }
    
    /**
     * Normaliza texto (quita acentos, minúsculas)
     */
    private function normalizeText($text) {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[áàäâ]/u', 'a', $text);
        $text = preg_replace('/[éèëê]/u', 'e', $text);
        $text = preg_replace('/[íìïî]/u', 'i', $text);
        $text = preg_replace('/[óòöô]/u', 'o', $text);
        $text = preg_replace('/[úùüû]/u', 'u', $text);
        $text = preg_replace('/ñ/u', 'n', $text);
        return $text;
    }
    
    /**
     * Obtiene contenido predefinido para la industria
     */
    public function getIndustryContent($industry) {
        if (isset($this->industriesData['industries'][$industry])) {
            return $this->industriesData['industries'][$industry];
        }
        return $this->industriesData['fallback'] ?? [];
    }
    
    /**
     * Genera paleta de colores CSS
     */
    public function generateColorPalette($primaryHex, $secondaryHex = null) {
        $primary = $this->hexToHSL($primaryHex);
        
        $lightnessMap = [
            50 => 97, 100 => 94, 200 => 86, 300 => 77, 400 => 66,
            500 => 55, 600 => 45, 700 => 35, 800 => 25, 900 => 15
        ];
        
        $css = ":root {\n";
        $css .= "    /* Primary Palette - Generated from {$primaryHex} */\n";
        
        foreach ($lightnessMap as $shade => $lightness) {
            $css .= "    --primary-{$shade}: hsl({$primary['h']}, {$primary['s']}%, {$lightness}%);\n";
        }
        
        if ($secondaryHex) {
            $secondary = $this->hexToHSL($secondaryHex);
            $css .= "\n    /* Secondary Palette - Generated from {$secondaryHex} */\n";
            foreach ($lightnessMap as $shade => $lightness) {
                $css .= "    --secondary-{$shade}: hsl({$secondary['h']}, {$secondary['s']}%, {$lightness}%);\n";
            }
        }
        
        // Glass effects
        $css .= "\n    /* Glass & Glow Effects */\n";
        $css .= "    --glass-bg: hsla({$primary['h']}, {$primary['s']}%, 98%, 0.7);\n";
        $css .= "    --glass-bg-dark: hsla({$primary['h']}, {$primary['s']}%, 10%, 0.8);\n";
        $css .= "    --glow-primary: hsla({$primary['h']}, {$primary['s']}%, {$primary['l']}%, 0.4);\n";
        $css .= "    --shadow-colored: 0 10px 40px hsla({$primary['h']}, {$primary['s']}%, {$primary['l']}%, 0.3);\n";
        $css .= "}\n";
        
        return $css;
    }
    
    /**
     * Convierte HEX a HSL
     */
    private function hexToHSL($hex) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        
        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            
            switch ($max) {
                case $r: $h = (($g - $b) / $d + ($g < $b ? 6 : 0)) / 6; break;
                case $g: $h = (($b - $r) / $d + 2) / 6; break;
                case $b: $h = (($r - $g) / $d + 4) / 6; break;
            }
        }
        
        return [
            'h' => round($h * 360),
            's' => round($s * 100),
            'l' => round($l * 100)
        ];
    }
    
    /**
     * Obtiene personalidad según industria
     */
    public function getPersonality($industry) {
        $map = [
            'medico' => 'health',
            'veterinaria' => 'health',
            'fitness' => 'bold',
            'restaurante' => 'friendly',
            'legal' => 'professional',
            'tecnologia' => 'tech',
            'general' => 'professional'
        ];
        
        return $map[$industry] ?? 'professional';
    }
    
    /**
     * Genera CSS de personalidad
     */
    public function generatePersonalityCSS($personality) {
        $styles = [
            'professional' => [
                'fontFamily' => "'Inter', 'Helvetica Neue', sans-serif",
                'headingWeight' => 600,
                'borderRadius' => '8px',
                'animationSpeed' => '0.4s'
            ],
            'friendly' => [
                'fontFamily' => "'Nunito', 'Poppins', sans-serif",
                'headingWeight' => 700,
                'borderRadius' => '16px',
                'animationSpeed' => '0.5s'
            ],
            'bold' => [
                'fontFamily' => "'Montserrat', 'Oswald', sans-serif",
                'headingWeight' => 800,
                'borderRadius' => '4px',
                'animationSpeed' => '0.3s'
            ],
            'tech' => [
                'fontFamily' => "'Space Grotesk', 'JetBrains Mono', monospace",
                'headingWeight' => 700,
                'borderRadius' => '12px',
                'animationSpeed' => '0.35s'
            ],
            'health' => [
                'fontFamily' => "'Source Sans Pro', 'Open Sans', sans-serif",
                'headingWeight' => 600,
                'borderRadius' => '12px',
                'animationSpeed' => '0.5s'
            ]
        ];
        
        $s = $styles[$personality] ?? $styles['professional'];
        
        return "
/* Personality: {$personality} */
:root {
    --personality-font-family: {$s['fontFamily']};
    --personality-heading-weight: {$s['headingWeight']};
    --personality-border-radius: {$s['borderRadius']};
    --personality-animation-speed: {$s['animationSpeed']};
}

body {
    font-family: var(--personality-font-family);
}

h1, h2, h3, h4, h5, h6 {
    font-weight: var(--personality-heading-weight);
}

.btn, button, .card {
    border-radius: var(--personality-border-radius);
    transition: all var(--personality-animation-speed) ease;
}
";
    }
    
    /**
     * Valida calidad del HTML
     */
    public function validateQuality($html, $options = []) {
        $checks = [
            'hasDoctype' => stripos(trim($html), '<!doctype html>') === 0,
            'hasTitle' => preg_match('/<title>[^<]+<\/title>/i', $html),
            'hasMetaDescription' => preg_match('/<meta\s+name=["\']description["\']/i', $html),
            'hasH1' => preg_match('/<h1[^>]*>[^<]+<\/h1>/i', $html),
            'hasViewport' => preg_match('/<meta\s+name=["\']viewport["\']/i', $html),
            'noPlaceholders' => !preg_match('/\{\{[^}]+\}\}/', $html),
            'hasHeader' => stripos($html, '<header') !== false,
            'hasFooter' => stripos($html, '<footer') !== false,
            'reasonableSize' => strlen($html) > 5000 && strlen($html) < 500000
        ];
        
        $passed = array_filter($checks);
        $score = round((count($passed) / count($checks)) * 100);
        
        return [
            'valid' => $score >= 80,
            'score' => $score,
            'checks' => $checks,
            'passed' => count($passed),
            'total' => count($checks)
        ];
    }
    
    /**
     * Procesa un negocio completo y genera configuración
     */
    public function processBusinessData($businessData) {
        // Detectar industria
        $industry = $this->detectIndustry($businessData);
        
        // Obtener contenido de industria
        $industryContent = $this->getIndustryContent($industry);
        
        // Obtener personalidad
        $personality = $this->getPersonality($industry);
        
        // Colores (usar los del negocio o los recomendados)
        $colors = $businessData['colores'] ?? $industryContent['colors'] ?? [
            'primary' => '#667eea',
            'secondary' => '#764ba2'
        ];
        
        // Generar CSS
        $colorCSS = $this->generateColorPalette(
            $colors['primary'] ?? '#667eea',
            $colors['secondary'] ?? null
        );
        $personalityCSS = $this->generatePersonalityCSS($personality);
        
        return [
            'industry' => $industry,
            'personality' => $personality,
            'industryContent' => $industryContent,
            'colors' => $colors,
            'generatedCSS' => $colorCSS . "\n" . $personalityCSS,
            'recommendedCTAs' => $industryContent['ctas'] ?? [
                'primary' => ['Contactar ahora'],
                'secondary' => ['Ver más']
            ],
            'trustBadges' => $industryContent['trust_badges'] ?? [],
            'faq' => $industryContent['faq'] ?? []
        ];
    }
    
    /**
     * Enriquece datos del negocio con contenido de industria
     */
    public function enrichBusinessData($businessData) {
        $processed = $this->processBusinessData($businessData);
        $industryContent = $processed['industryContent'];
        
        // Enriquecer hero si está vacío
        if (empty($businessData['hero']['titulo_principal']) && !empty($industryContent['hero']['titles'])) {
            $businessData['hero']['titulo_principal'] = $industryContent['hero']['titles'][0];
        }
        
        // Enriquecer subtítulo
        if (empty($businessData['hero']['subtitulo']) && !empty($industryContent['hero']['subtitles'])) {
            $businessData['hero']['subtitulo'] = $industryContent['hero']['subtitles'][0];
        }
        
        // Enriquecer CTAs
        if (empty($businessData['hero']['cta_principal']['texto']) && !empty($industryContent['ctas']['primary'])) {
            $businessData['hero']['cta_principal']['texto'] = $industryContent['ctas']['primary'][0];
        }
        
        // Agregar trust badges si no existen
        if (empty($businessData['trust_badges']) && !empty($industryContent['trust_badges'])) {
            $businessData['trust_badges'] = $industryContent['trust_badges'];
        }
        
        // Agregar FAQ si no existe
        if (empty($businessData['faq']) && !empty($industryContent['faq'])) {
            $businessData['faq'] = $industryContent['faq'];
        }
        
        // Agregar metadata de procesamiento
        $businessData['_processed'] = [
            'industry' => $processed['industry'],
            'personality' => $processed['personality'],
            'generatedCSS' => $processed['generatedCSS'],
            'timestamp' => date('c')
        ];
        
        return $businessData;
    }
}

// Uso standalone
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $engine = new TemplateEngine();
    $testData = json_decode($argv[1], true);
    if ($testData) {
        $result = $engine->processBusinessData($testData);
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
