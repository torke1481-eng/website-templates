<?php
/**
 * HEALTH CHECK - Verifica estado del sistema
 * 
 * Endpoint: GET /_system/api/health.php
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/db.php';

$checks = [
    'php' => [
        'status' => 'ok',
        'version' => PHP_VERSION
    ],
    'database' => [
        'status' => 'unknown'
    ],
    'directories' => [
        'staging' => false,
        'domains' => false,
        'logs' => false
    ],
    'timestamp' => date('c')
];

// Check database
try {
    $health = checkDatabaseHealth();
    $checks['database'] = [
        'status' => $health['healthy'] ? 'ok' : 'error',
        'details' => $health
    ];
} catch (Exception $e) {
    $checks['database'] = [
        'status' => 'error',
        'error' => $e->getMessage()
    ];
}

// Check directories
$dirs = [
    'staging' => '/home/u253890393/domains/otavafitness.com/staging',
    'domains' => '/home/u253890393/domains/otavafitness.com/domains',
    'logs' => __DIR__ . '/../logs'
];

foreach ($dirs as $name => $path) {
    $checks['directories'][$name] = [
        'exists' => file_exists($path),
        'writable' => is_writable($path)
    ];
}

// Overall status
$allOk = $checks['database']['status'] === 'ok';
$checks['overall'] = $allOk ? 'healthy' : 'degraded';

http_response_code($allOk ? 200 : 503);
echo json_encode($checks, JSON_PRETTY_PRINT);
