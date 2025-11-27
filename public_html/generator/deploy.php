<?php
/**
 * PROXY SEGURO PARA MAKE.COM
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Validar mÃ©todo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method Not Allowed']));
}

// Cargar .env
$envFile = dirname(dirname(__DIR__)) . '/_system/config/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Validar token
$secret = $_SERVER['HTTP_X_MAKE_SECRET'] ?? '';
$expectedSecret = getenv('MAKE_SECRET');

if (!$expectedSecret || !hash_equals($expectedSecret, $secret)) {
    http_response_code(403);
    die(json_encode(['error' => 'Forbidden']));
}

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateFile = sys_get_temp_dir() . '/rate-' . md5($ip) . '.txt';

if (file_exists($rateFile)) {
    $requests = file($rateFile, FILE_IGNORE_NEW_LINES);
    $now = time();
    
    $requests = array_filter($requests, function($ts) use ($now) {
        return ($now - (int)$ts) < 60;
    });
    
    if (count($requests) >= 10) {
        http_response_code(429);
        die(json_encode(['error' => 'Rate limit exceeded']));
    }
    
    $requests[] = $now;
} else {
    $requests = [time()];
}

file_put_contents($rateFile, implode("\n", $requests));

// Log
$logDir = dirname(dirname(__DIR__)) . '/_system/logs';
if (!file_exists($logDir)) {
    @mkdir($logDir, 0755, true);
}

file_put_contents(
    $logDir . '/make-access.log',
    json_encode([
        'timestamp' => date('c'),
        'ip' => $ip,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]) . "\n",
    FILE_APPEND
);

// Incluir script real
chdir(dirname(dirname(__DIR__)) . '/_system/generator');
require_once dirname(dirname(__DIR__)) . '/_system/generator/deploy-v4-mejorado.php';
?>
