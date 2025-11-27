<?php
/**
 * SAVE WEBSITE - Guarda información del sitio en MySQL
 * 
 * Endpoint: POST /_system/api/save-website.php
 * Headers: X-API-Key (requerido)
 * Body: { domain, business_name, template, staging_url, status, config }
 * 
 * Ruta servidor: /home/u253890393/domains/otavafitness.com/_system/api/save-website.php
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
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/secrets.php';

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
        'endpoint' => 'save-website',
        'message' => $message,
        'data' => $data
    ];
    
    file_put_contents(
        $logDir . '/api.log',
        json_encode($entry, JSON_PRETTY_PRINT) . "\n---\n",
        FILE_APPEND
    );
}

function respond($success, $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode(array_merge(['success' => $success], $data), JSON_UNESCAPED_UNICODE);
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

// Campos requeridos
$required = ['domain', 'business_name', 'template'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        respond(false, ['error' => "Campo '$field' es requerido"], 400);
    }
}

// ============================================
// GUARDAR EN DATABASE
// ============================================

try {
    $db = getDB();
    
    // Verificar si ya existe
    $stmt = $db->prepare("SELECT id FROM websites WHERE domain = ?");
    $stmt->execute([$input['domain']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Actualizar existente
        $stmt = $db->prepare("
            UPDATE websites SET 
                business_name = ?,
                template = ?,
                staging_url = ?,
                status = ?,
                config = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['business_name'],
            $input['template'],
            $input['staging_url'] ?? null,
            $input['status'] ?? 'staging',
            json_encode($input['config'] ?? []),
            $existing['id']
        ]);
        
        $websiteId = $existing['id'];
        $isNew = false;
        
        logApi('Website actualizado', ['id' => $websiteId, 'domain' => $input['domain']]);
        
    } else {
        // Insertar nuevo
        $stmt = $db->prepare("
            INSERT INTO websites (domain, business_name, template, staging_url, status, config, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $input['domain'],
            $input['business_name'],
            $input['template'],
            $input['staging_url'] ?? null,
            $input['status'] ?? 'staging',
            json_encode($input['config'] ?? [])
        ]);
        
        $websiteId = $db->lastInsertId();
        $isNew = true;
        
        logApi('Website creado', ['id' => $websiteId, 'domain' => $input['domain']]);
    }
    
    // Registrar en generation_logs si existe la función
    if (function_exists('insertGenerationLog')) {
        insertGenerationLog(
            $websiteId,
            $isNew ? 'create' : 'update',
            'completed',
            0, // duration
            0  // cost
        );
    }
    
    respond(true, [
        'website_id' => (int)$websiteId,
        'domain' => $input['domain'],
        'is_new' => $isNew,
        'message' => $isNew ? 'Website creado correctamente' : 'Website actualizado correctamente'
    ]);
    
} catch (PDOException $e) {
    logApi('Error de base de datos', ['error' => $e->getMessage()]);
    respond(false, ['error' => 'Error de base de datos: ' . $e->getMessage()], 500);
    
} catch (Exception $e) {
    logApi('Error general', ['error' => $e->getMessage()]);
    respond(false, ['error' => $e->getMessage()], 500);
}
