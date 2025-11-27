<?php
/**
 * ENRICH BUSINESS - Enriquece datos del negocio con Template Engine
 * 
 * Endpoint: POST /_system/api/enrich-business.php
 * Headers: X-API-Key (requerido)
 * Body: { businessData: {...} }
 * 
 * Retorna: Datos enriquecidos con industria detectada, personalidad, colores, CTAs, etc.
 * 
 * Uso: El agente puede llamar ANTES de enviar a Claude para enriquecer los datos
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

// Cargar configuración y Template Engine
require_once __DIR__ . '/../config/secrets.php';
require_once __DIR__ . '/../generator/template-engine.php';

// ============================================
// FUNCIONES
// ============================================

function logApi($message, $data = []) {
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $entry = [
        'timestamp' => date('c'),
        'endpoint' => 'enrich-business',
        'message' => $message,
        'data' => $data
    ];
    
    file_put_contents(
        $logDir . '/api.log',
        json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n---\n",
        FILE_APPEND
    );
}

function respond($success, $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode(array_merge(['success' => $success], $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ============================================
// VALIDACIÓN
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, ['error' => 'Método no permitido'], 405);
}

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== API_KEY) {
    logApi('API Key inválida');
    respond(false, ['error' => 'No autorizado'], 401);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    respond(false, ['error' => 'JSON inválido'], 400);
}

// Obtener datos del negocio
$businessData = $input['businessData'] ?? $input;

if (empty($businessData)) {
    respond(false, ['error' => 'businessData es requerido'], 400);
}

// ============================================
// PROCESAR CON TEMPLATE ENGINE
// ============================================

try {
    $engine = new TemplateEngine();
    
    // Procesar datos del negocio
    $processed = $engine->processBusinessData($businessData);
    
    // Enriquecer datos
    $enriched = $engine->enrichBusinessData($businessData);
    
    logApi('Negocio enriquecido', [
        'nombre' => $businessData['nombre'] ?? 'N/A',
        'industry' => $processed['industry'],
        'personality' => $processed['personality']
    ]);
    
    respond(true, [
        'enrichedData' => $enriched,
        'detection' => [
            'industry' => $processed['industry'],
            'personality' => $processed['personality']
        ],
        'recommendations' => [
            'colors' => $processed['colors'],
            'ctas' => $processed['recommendedCTAs'],
            'trustBadges' => $processed['trustBadges']
        ],
        'generatedCSS' => $processed['generatedCSS'],
        'industryContent' => $processed['industryContent']
    ]);
    
} catch (Exception $e) {
    logApi('Error procesando negocio', ['error' => $e->getMessage()]);
    respond(false, ['error' => $e->getMessage()], 500);
}
