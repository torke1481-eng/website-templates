<?php
/**
 * LIST WEBSITES - Lista todos los sitios web
 * 
 * Endpoint: GET /_system/api/list-websites.php
 * Query params: ?status=pending_approval (opcional)
 * 
 * Ruta servidor: /home/u253890393/domains/otavafitness.com/_system/api/list-websites.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

try {
    $db = getDB();
    
    $status = $_GET['status'] ?? null;
    
    if ($status) {
        $stmt = $db->prepare("
            SELECT id, domain, business_name, template, status, staging_url, production_url, created_at, updated_at, published_at
            FROM websites 
            WHERE status = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$status]);
    } else {
        $stmt = $db->query("
            SELECT id, domain, business_name, template, status, staging_url, production_url, created_at, updated_at, published_at
            FROM websites 
            ORDER BY created_at DESC
        ");
    }
    
    $websites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Agregar URLs de aprobación
    $secret = 'tu-secreto-aprobacion-2024';
    foreach ($websites as &$site) {
        $token = substr(md5($site['id'] . $secret), 0, 16);
        $site['approve_url'] = "https://otavafitness.com/_system/api/approve.php?id={$site['id']}&action=approve&token=$token";
        $site['reject_url'] = "https://otavafitness.com/_system/api/approve.php?id={$site['id']}&action=reject&token=$token";
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($websites),
        'websites' => $websites
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
