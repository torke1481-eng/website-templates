<?php
/**
 * VALIDATE HTML - Valida calidad del HTML generado
 * 
 * Endpoint: POST /_system/api/validate-html.php
 * Headers: X-API-Key (requerido)
 * Body: { html: "...", businessName: "...", whatsapp: "..." }
 * 
 * Retorna: Score de calidad y recomendaciones
 * 
 * Uso: El agente puede llamar DESPUÉS de que Claude genere HTML para validar
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/api.log');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Cargar configuración
require_once __DIR__ . '/../config/secrets.php';

// Score mínimo (también definido en secrets.php como MIN_QUALITY_SCORE)
define('MIN_SCORE', defined('MIN_QUALITY_SCORE') ? MIN_QUALITY_SCORE : 80);

// ============================================
// FUNCIONES DE VALIDACIÓN
// ============================================

function checkSEO($html) {
    $checks = [];
    $score = 0;
    $maxScore = 100;
    
    // DOCTYPE
    if (stripos(trim($html), '<!doctype html>') === 0) {
        $checks['doctype'] = ['passed' => true, 'message' => 'DOCTYPE presente'];
        $score += 10;
    } else {
        $checks['doctype'] = ['passed' => false, 'message' => 'Falta DOCTYPE'];
    }
    
    // Title
    if (preg_match('/<title>([^<]+)<\/title>/i', $html, $matches)) {
        $titleLen = strlen($matches[1]);
        if ($titleLen >= 30 && $titleLen <= 60) {
            $checks['title'] = ['passed' => true, 'message' => "Título OK ($titleLen chars)"];
            $score += 15;
        } else {
            $checks['title'] = ['passed' => false, 'message' => "Título fuera de rango ($titleLen chars, ideal 30-60)"];
            $score += 5;
        }
    } else {
        $checks['title'] = ['passed' => false, 'message' => 'Falta título'];
    }
    
    // Meta description
    if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
        $descLen = strlen($matches[1]);
        if ($descLen >= 120 && $descLen <= 160) {
            $checks['meta_description'] = ['passed' => true, 'message' => "Meta description OK ($descLen chars)"];
            $score += 15;
        } else {
            $checks['meta_description'] = ['passed' => false, 'message' => "Meta description fuera de rango ($descLen chars)"];
            $score += 5;
        }
    } else {
        $checks['meta_description'] = ['passed' => false, 'message' => 'Falta meta description'];
    }
    
    // H1
    preg_match_all('/<h1/i', $html, $h1Matches);
    $h1Count = count($h1Matches[0]);
    if ($h1Count === 1) {
        $checks['h1'] = ['passed' => true, 'message' => 'Un H1 presente'];
        $score += 15;
    } elseif ($h1Count > 1) {
        $checks['h1'] = ['passed' => false, 'message' => "Múltiples H1 ($h1Count)"];
        $score += 5;
    } else {
        $checks['h1'] = ['passed' => false, 'message' => 'Falta H1'];
    }
    
    // Open Graph
    if (preg_match('/<meta\s+property=["\']og:/i', $html)) {
        $checks['open_graph'] = ['passed' => true, 'message' => 'Open Graph presente'];
        $score += 10;
    } else {
        $checks['open_graph'] = ['passed' => false, 'message' => 'Falta Open Graph'];
    }
    
    // Canonical
    if (preg_match('/<link\s+rel=["\']canonical["\']/i', $html)) {
        $checks['canonical'] = ['passed' => true, 'message' => 'Canonical presente'];
        $score += 10;
    } else {
        $checks['canonical'] = ['passed' => false, 'message' => 'Falta canonical'];
    }
    
    // Schema.org
    if (preg_match('/<script\s+type=["\']application\/ld\+json["\']/i', $html)) {
        $checks['schema'] = ['passed' => true, 'message' => 'Schema.org presente'];
        $score += 10;
    } else {
        $checks['schema'] = ['passed' => false, 'message' => 'Falta Schema.org'];
    }
    
    // Lang attribute
    if (preg_match('/<html[^>]+lang=["\'][a-z]{2}/i', $html)) {
        $checks['lang'] = ['passed' => true, 'message' => 'Atributo lang presente'];
        $score += 10;
    } else {
        $checks['lang'] = ['passed' => false, 'message' => 'Falta atributo lang'];
    }
    
    // Viewport
    if (preg_match('/<meta\s+name=["\']viewport["\']/i', $html)) {
        $checks['viewport'] = ['passed' => true, 'message' => 'Viewport presente'];
        $score += 5;
    } else {
        $checks['viewport'] = ['passed' => false, 'message' => 'Falta viewport'];
    }
    
    return [
        'score' => $score,
        'checks' => $checks
    ];
}

function checkAccessibility($html) {
    $checks = [];
    $score = 0;
    
    // Alt en imágenes
    preg_match_all('/<img[^>]+>/i', $html, $imgs);
    $totalImgs = count($imgs[0]);
    $imgsWithAlt = 0;
    foreach ($imgs[0] as $img) {
        if (preg_match('/alt=["\'][^"\']+["\']/i', $img)) {
            $imgsWithAlt++;
        }
    }
    
    if ($totalImgs === 0 || $imgsWithAlt === $totalImgs) {
        $checks['img_alt'] = ['passed' => true, 'message' => "Todas las imágenes tienen alt ($imgsWithAlt/$totalImgs)"];
        $score += 30;
    } else {
        $checks['img_alt'] = ['passed' => false, 'message' => "Imágenes sin alt ($imgsWithAlt/$totalImgs)"];
        $score += round(($imgsWithAlt / $totalImgs) * 30);
    }
    
    // Skip link
    if (preg_match('/skip-link|skip-to-content|skip-nav/i', $html)) {
        $checks['skip_link'] = ['passed' => true, 'message' => 'Skip link presente'];
        $score += 20;
    } else {
        $checks['skip_link'] = ['passed' => false, 'message' => 'Falta skip link'];
    }
    
    // ARIA labels
    if (preg_match('/aria-label/i', $html)) {
        $checks['aria'] = ['passed' => true, 'message' => 'ARIA labels presentes'];
        $score += 20;
    } else {
        $checks['aria'] = ['passed' => false, 'message' => 'Faltan ARIA labels'];
    }
    
    // Header/Footer/Main
    $hasHeader = stripos($html, '<header') !== false;
    $hasFooter = stripos($html, '<footer') !== false;
    $hasMain = stripos($html, '<main') !== false || stripos($html, 'role="main"') !== false;
    
    if ($hasHeader && $hasFooter && $hasMain) {
        $checks['landmarks'] = ['passed' => true, 'message' => 'Landmarks semánticos presentes'];
        $score += 30;
    } else {
        $missing = [];
        if (!$hasHeader) $missing[] = 'header';
        if (!$hasFooter) $missing[] = 'footer';
        if (!$hasMain) $missing[] = 'main';
        $checks['landmarks'] = ['passed' => false, 'message' => 'Faltan: ' . implode(', ', $missing)];
        $score += 10;
    }
    
    return [
        'score' => $score,
        'checks' => $checks
    ];
}

function checkContent($html, $businessName = '', $whatsapp = '') {
    $checks = [];
    $score = 0;
    
    // Sin placeholders
    if (!preg_match('/\{\{[A-Z_]+\}\}/', $html) && !preg_match('/\[placeholder\]/i', $html)) {
        $checks['no_placeholders'] = ['passed' => true, 'message' => 'Sin placeholders'];
        $score += 30;
    } else {
        $checks['no_placeholders'] = ['passed' => false, 'message' => 'Contiene placeholders sin reemplazar'];
    }
    
    // Nombre del negocio presente
    if (empty($businessName) || stripos($html, $businessName) !== false) {
        $checks['business_name'] = ['passed' => true, 'message' => 'Nombre del negocio presente'];
        $score += 20;
    } else {
        $checks['business_name'] = ['passed' => false, 'message' => 'Nombre del negocio no encontrado'];
    }
    
    // WhatsApp correcto
    if (empty($whatsapp) || strpos($html, "wa.me/$whatsapp") !== false) {
        $checks['whatsapp'] = ['passed' => true, 'message' => 'WhatsApp correcto'];
        $score += 20;
    } else {
        $checks['whatsapp'] = ['passed' => false, 'message' => 'WhatsApp incorrecto o faltante'];
    }
    
    // Tiene CTAs
    if (preg_match('/<button|<a[^>]+class=["\'][^"\']*btn/i', $html)) {
        $checks['cta'] = ['passed' => true, 'message' => 'CTAs presentes'];
        $score += 15;
    } else {
        $checks['cta'] = ['passed' => false, 'message' => 'Faltan CTAs'];
    }
    
    // Tamaño razonable
    $sizeKB = strlen($html) / 1024;
    if ($sizeKB >= 10 && $sizeKB <= 500) {
        $checks['size'] = ['passed' => true, 'message' => sprintf('Tamaño OK (%.1f KB)', $sizeKB)];
        $score += 15;
    } else {
        $checks['size'] = ['passed' => false, 'message' => sprintf('Tamaño fuera de rango (%.1f KB)', $sizeKB)];
    }
    
    return [
        'score' => $score,
        'checks' => $checks
    ];
}

function respond($success, $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode(array_merge(['success' => $success], $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ============================================
// VALIDACIÓN DE REQUEST
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, ['error' => 'Método no permitido'], 405);
}

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== API_KEY) {
    respond(false, ['error' => 'No autorizado'], 401);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['html'])) {
    respond(false, ['error' => 'Campo html es requerido'], 400);
}

// ============================================
// EJECUTAR VALIDACIÓN
// ============================================

$html = $input['html'];
$businessName = $input['businessName'] ?? '';
$whatsapp = $input['whatsapp'] ?? '';

$seoResult = checkSEO($html);
$accessibilityResult = checkAccessibility($html);
$contentResult = checkContent($html, $businessName, $whatsapp);

// Calcular score total (ponderado)
$totalScore = round(
    ($seoResult['score'] * 0.35) +
    ($accessibilityResult['score'] * 0.30) +
    ($contentResult['score'] * 0.35)
);

$passed = $totalScore >= MIN_SCORE;

// Obtener issues críticos
$issues = [];
foreach (array_merge($seoResult['checks'], $accessibilityResult['checks'], $contentResult['checks']) as $key => $check) {
    if (!$check['passed']) {
        $issues[] = $check['message'];
    }
}

respond(true, [
    'valid' => $passed,
    'score' => $totalScore,
    'minScore' => MIN_SCORE,
    'categories' => [
        'seo' => [
            'score' => $seoResult['score'],
            'weight' => '35%',
            'checks' => $seoResult['checks']
        ],
        'accessibility' => [
            'score' => $accessibilityResult['score'],
            'weight' => '30%',
            'checks' => $accessibilityResult['checks']
        ],
        'content' => [
            'score' => $contentResult['score'],
            'weight' => '35%',
            'checks' => $contentResult['checks']
        ]
    ],
    'issues' => $issues,
    'recommendation' => $passed 
        ? 'HTML válido, listo para deploy' 
        : 'HTML necesita correcciones antes de deploy'
]);
