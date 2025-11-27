<?php
/**
 * DELETE WEBSITE - Elimina sitio web completo
 * 
 * Endpoint: POST /api/delete.php
 * Headers: X-API-Key (requerido)
 * Body: { slug: "nombre-sitio" }
 * 
 * Elimina:
 * - Carpeta en /domains/slug/
 * - Carpeta en /staging/slug/ (si existe)
 * - Registro en database
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once dirname(__DIR__, 2) . '/_system/config/db.php';

// ============================================
// CONFIGURACIÓN
// ============================================
define('API_KEY', '5893674120Fr.');
define('DOMAINS_BASE', '/home/u253890393/domains/otavafitness.com/domains');
define('STAGING_BASE', '/home/u253890393/domains/otavafitness.com/staging');

// ============================================
// FUNCIONES
// ============================================

function respond($success, $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode(array_merge(['success' => $success], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . '/' . $item)) return false;
    }
    
    return rmdir($dir);
}

// ============================================
// VALIDACIÓN
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, ['error' => 'Método no permitido. Use POST.'], 405);
}

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== API_KEY) {
    respond(false, ['error' => 'No autorizado'], 401);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['slug'])) {
    respond(false, ['error' => 'Campo "slug" es requerido'], 400);
}

$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($input['slug']));
if (empty($slug)) {
    respond(false, ['error' => 'Slug inválido'], 400);
}

// ============================================
// ELIMINAR
// ============================================

$deleted = [
    'domains_folder' => false,
    'staging_folder' => false,
    'database' => false
];

// 1. Eliminar carpeta en domains
$domainsDir = DOMAINS_BASE . '/' . $slug;
if (file_exists($domainsDir)) {
    $deleted['domains_folder'] = deleteDirectory($domainsDir);
} else {
    $deleted['domains_folder'] = 'no existía';
}

// 2. Eliminar carpeta en staging
$stagingDir = STAGING_BASE . '/' . $slug;
if (file_exists($stagingDir)) {
    $deleted['staging_folder'] = deleteDirectory($stagingDir);
} else {
    $deleted['staging_folder'] = 'no existía';
}

// 3. Eliminar de database
try {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM websites WHERE domain = ?");
    $stmt->execute([$slug]);
    $deleted['database'] = $stmt->rowCount() > 0 ? true : 'no existía';
} catch (Exception $e) {
    $deleted['database'] = 'error: ' . $e->getMessage();
}

respond(true, [
    'slug' => $slug,
    'deleted' => $deleted,
    'message' => 'Sitio eliminado correctamente'
]);
