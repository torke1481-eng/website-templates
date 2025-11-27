<?php
/**
 * DEPLOY SIMPLE - Recibe HTML generado por Claude y lo guarda en staging
 * 
 * Endpoint: POST /_system/generator/deploy-simple.php
 * Headers: X-API-Key (requerido)
 * Body: { html, slug, nombre, metadata }
 * 
 * Ruta servidor: /home/u253890393/domains/otavafitness.com/_system/generator/deploy-simple.php
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/deploy-simple.log');

// Headers
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Cargar configuración centralizada
require_once __DIR__ . '/../config/secrets.php';

// Rutas según modo de deploy (definido en secrets.php)
if (DEPLOY_MODE === 'production') {
    define('DEPLOY_BASE', DOMAINS_BASE);
    define('DEPLOY_URL', DOMAINS_URL);
} else {
    define('DEPLOY_BASE', STAGING_BASE);
    define('DEPLOY_URL', STAGING_URL);
}

// ============================================
// FUNCIONES HELPER
// ============================================

function logMessage($message, $data = []) {
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $entry = [
        'timestamp' => date('c'),
        'message' => $message,
        'data' => $data
    ];
    
    file_put_contents(
        $logDir . '/deploy-simple.log',
        json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n---\n",
        FILE_APPEND
    );
}

function sendResponse($success, $data = [], $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(array_merge(['success' => $success], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function sanitizeSlug($string) {
    $slug = strtolower(trim($string));
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = substr($slug, 0, 50);
    return $slug ?: 'sitio-' . uniqid();
}

function validateHtml($html) {
    $errors = [];
    
    if (strlen($html) < 1000) {
        $errors[] = 'HTML muy corto (menos de 1KB)';
    }
    
    if (strlen($html) > 500000) {
        $errors[] = 'HTML muy largo (más de 500KB)';
    }
    
    if (stripos($html, '<!DOCTYPE html>') === false) {
        $errors[] = 'Falta <!DOCTYPE html>';
    }
    
    if (stripos($html, '</html>') === false) {
        $errors[] = 'Falta </html>';
    }
    
    // Verificar que no tenga placeholders sin reemplazar
    if (preg_match('/\{\{[A-Z_]+\}\}/', $html)) {
        $errors[] = 'Contiene placeholders {{}} sin reemplazar';
    }
    
    if (preg_match('/\[WHATSAPP_NUMEROS\]/', $html)) {
        $errors[] = 'Contiene [WHATSAPP_NUMEROS] sin reemplazar';
    }
    
    return $errors;
}

// ============================================
// VALIDACIÓN DE REQUEST
// ============================================

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logMessage('Método no permitido', ['method' => $_SERVER['REQUEST_METHOD']]);
    sendResponse(false, ['error' => 'Método no permitido. Use POST.'], 405);
}

// Verificar API Key
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== API_KEY) {
    logMessage('API Key inválida', ['provided' => substr($apiKey, 0, 10) . '...']);
    sendResponse(false, ['error' => 'API Key inválida o faltante'], 401);
}

// Obtener y validar JSON
$input = file_get_contents('php://input');
if (empty($input)) {
    logMessage('Body vacío');
    sendResponse(false, ['error' => 'Request body vacío'], 400);
}

$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    logMessage('JSON inválido', ['error' => json_last_error_msg()]);
    sendResponse(false, ['error' => 'JSON inválido: ' . json_last_error_msg()], 400);
}

// Validar campos requeridos
if (empty($data['html'])) {
    sendResponse(false, ['error' => 'Campo "html" es requerido'], 400);
}

if (empty($data['slug'])) {
    sendResponse(false, ['error' => 'Campo "slug" es requerido'], 400);
}

// ============================================
// PROCESAR DATOS
// ============================================

$html = $data['html'];
$slug = sanitizeSlug($data['slug']);
$nombre = $data['nombre'] ?? $slug;
$metadata = $data['metadata'] ?? [];

logMessage('Procesando deploy', [
    'slug' => $slug,
    'nombre' => $nombre,
    'html_size' => strlen($html)
]);

// Validar HTML
$htmlErrors = validateHtml($html);
if (!empty($htmlErrors)) {
    logMessage('HTML inválido', ['errors' => $htmlErrors]);
    sendResponse(false, [
        'error' => 'HTML no válido',
        'details' => $htmlErrors
    ], 400);
}

// ============================================
// CREAR DIRECTORIO Y GUARDAR
// ============================================

$siteDir = DEPLOY_BASE . '/' . $slug;

// Verificar si ya existe
$isUpdate = file_exists($siteDir);

// Crear directorio si no existe
if (!file_exists($siteDir)) {
    if (!mkdir($siteDir, 0755, true)) {
        logMessage('Error creando directorio', ['path' => $siteDir]);
        sendResponse(false, ['error' => 'No se pudo crear el directorio'], 500);
    }
}

// Guardar HTML
$htmlFile = $siteDir . '/index.html';
$bytesWritten = file_put_contents($htmlFile, $html);

if ($bytesWritten === false) {
    logMessage('Error guardando HTML', ['path' => $htmlFile]);
    sendResponse(false, ['error' => 'No se pudo guardar el archivo HTML'], 500);
}

// Guardar metadata
$metadataFile = $siteDir . '/.metadata.json';
$metadataContent = [
    'slug' => $slug,
    'nombre' => $nombre,
    'created_at' => $isUpdate ? (json_decode(file_get_contents($metadataFile), true)['created_at'] ?? date('c')) : date('c'),
    'updated_at' => date('c'),
    'html_size_kb' => round(strlen($html) / 1024, 2),
    'metadata' => $metadata,
    'version' => $isUpdate ? ((json_decode(file_get_contents($metadataFile), true)['version'] ?? 0) + 1) : 1
];

file_put_contents($metadataFile, json_encode($metadataContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// ============================================
// RESPUESTA EXITOSA
// ============================================

$siteUrl = DEPLOY_URL . '/' . $slug . '/';

logMessage('Deploy exitoso', [
    'slug' => $slug,
    'url' => $siteUrl,
    'mode' => DEPLOY_MODE,
    'size_kb' => round(strlen($html) / 1024, 2),
    'is_update' => $isUpdate
]);

sendResponse(true, [
    'url' => $siteUrl,
    'slug' => $slug,
    'nombre' => $nombre,
    'size_kb' => round(strlen($html) / 1024, 2),
    'is_update' => $isUpdate,
    'version' => $metadataContent['version'],
    'mode' => DEPLOY_MODE,
    'message' => $isUpdate ? 'Sitio actualizado correctamente' : 'Sitio publicado correctamente'
]);
