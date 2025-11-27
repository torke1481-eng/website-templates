<?php
/**
 * GET PROMPT - Retorna el prompt para Claude
 * 
 * Endpoint: GET /_system/api/get-prompt.php
 * Headers: X-API-Key (requerido)
 * 
 * El agente llama a este endpoint para obtener el prompt actualizado
 * antes de llamar a Claude
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/secrets.php';

// Verificar API Key
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Leer el prompt
$promptFile = __DIR__ . '/../config/PROMPT_CLAUDE_TEMPLATE.txt';

if (!file_exists($promptFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'Prompt no encontrado']);
    exit;
}

$prompt = file_get_contents($promptFile);

echo json_encode([
    'success' => true,
    'prompt' => $prompt,
    'version' => '1.0',
    'updated_at' => date('c', filemtime($promptFile))
], JSON_UNESCAPED_UNICODE);
