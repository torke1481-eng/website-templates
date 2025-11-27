<?php
/**
 * SECRETS - Configuración centralizada de claves y secretos
 * 
 * ⚠️ IMPORTANTE: Este archivo contiene información sensible
 * - NO subir a repositorios públicos
 * - Cambiar todos los valores antes de producción
 * - Usar variables de entorno en producción real
 */

// ============================================
// API KEY PRINCIPAL
// ============================================
// Usada para autenticar requests a todos los endpoints
define('API_KEY', '5893674120Fr.');

// ============================================
// SECRETO DE APROBACIÓN
// ============================================
// Usado para generar tokens de aprobación/rechazo en emails
// Cambiar por un string aleatorio largo
define('APPROVAL_SECRET', 'torke-approval-secret-2024-change-me');

// ============================================
// RUTAS DEL SERVIDOR
// ============================================
define('SERVER_BASE', '/home/u253890393/domains/otavafitness.com');
define('DOMAINS_BASE', SERVER_BASE . '/domains');
define('STAGING_BASE', SERVER_BASE . '/staging');
define('SYSTEM_BASE', SERVER_BASE . '/_system');

// ============================================
// URLs PÚBLICAS
// ============================================
define('SITE_URL', 'https://otavafitness.com');
define('DOMAINS_URL', SITE_URL . '/domains');
define('STAGING_URL', SITE_URL . '/staging');
define('SYSTEM_URL', SITE_URL . '/_system');

// ============================================
// CONFIGURACIÓN DE DEPLOY
// ============================================
// 'staging' = guarda en /staging/ para revisar antes
// 'production' = guarda directo en /domains/
define('DEPLOY_MODE', 'production');

// ============================================
// EMAIL DE NOTIFICACIONES
// ============================================
define('ADMIN_EMAIL', 'admin@otavafitness.com');

// ============================================
// CONFIGURACIÓN DE CALIDAD
// ============================================
define('MIN_QUALITY_SCORE', 80);

// ============================================
// HELPER: Generar token de aprobación
// ============================================
function generateApprovalToken($websiteId) {
    return substr(md5($websiteId . APPROVAL_SECRET), 0, 16);
}

// ============================================
// HELPER: Validar token de aprobación
// ============================================
function validateApprovalToken($websiteId, $token) {
    return $token === generateApprovalToken($websiteId);
}

// ============================================
// HELPER: Generar URLs de aprobación
// ============================================
function getApprovalUrls($websiteId) {
    $token = generateApprovalToken($websiteId);
    $baseUrl = SYSTEM_URL . '/api/approve.php';
    
    return [
        'approve' => "{$baseUrl}?id={$websiteId}&action=approve&token={$token}",
        'reject' => "{$baseUrl}?id={$websiteId}&action=reject&token={$token}"
    ];
}
