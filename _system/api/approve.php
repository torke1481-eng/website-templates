<?php
/**
 * APPROVE - Aprobar o rechazar sitio desde email
 * 
 * Endpoint: GET /_system/api/approve.php?id=X&action=approve|reject&token=XXX
 * 
 * Ruta servidor: /home/u253890393/domains/otavafitness.com/_system/api/approve.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/api.log');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/secrets.php';

// Configuración cargada desde secrets.php

// ============================================
// FUNCIONES
// ============================================

function logApproval($message, $data = []) {
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents(
        $logDir . '/approvals.log',
        date('c') . " | $message | " . json_encode($data) . "\n",
        FILE_APPEND
    );
}

function showPage($title, $message, $type = 'success') {
    $color = $type === 'success' ? '#00BFA5' : ($type === 'error' ? '#E53935' : '#FF9800');
    $icon = $type === 'success' ? '✅' : ($type === 'error' ? '❌' : '⚠️');
    
    echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>$title - Torke Digital</title>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 20px;
        }
        .card {
            background: white;
            padding: 48px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .icon { font-size: 64px; margin-bottom: 24px; }
        h1 { 
            color: $color; 
            font-size: 28px; 
            margin-bottom: 16px;
            font-weight: 700;
        }
        p { 
            color: #666; 
            font-size: 16px; 
            line-height: 1.7;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: $color;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform .2s, box-shadow .2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,.2);
        }
        .details {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
            font-size: 14px;
        }
        .details strong { color: #333; }
    </style>
</head>
<body>
    <div class='card'>
        <div class='icon'>$icon</div>
        <h1>$title</h1>
        <p>$message</p>
        <a href='https://otavafitness.com/_system/' class='btn'>Ir al Dashboard</a>
    </div>
</body>
</html>";
    exit;
}

function copyDirectory($src, $dst) {
    $dir = opendir($src);
    if (!file_exists($dst)) {
        mkdir($dst, 0755, true);
    }
    
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        
        if (is_dir($srcPath)) {
            copyDirectory($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
    
    closedir($dir);
}

// ============================================
// VALIDACIÓN
// ============================================

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;
$token = $_GET['token'] ?? '';

// Validar parámetros básicos
if (!$id || !is_numeric($id)) {
    showPage('Error', 'ID de sitio inválido o faltante.', 'error');
}

if (!in_array($action, ['approve', 'reject'])) {
    showPage('Error', 'Acción inválida. Use "approve" o "reject".', 'error');
}

// Validar token (simple hash del ID + secreto)
$expectedToken = generateApprovalToken($id);
if ($token !== $expectedToken) {
    logApproval('Token inválido', ['id' => $id, 'token' => $token]);
    showPage('Error', 'Token de seguridad inválido. Este enlace puede haber expirado.', 'error');
}

// ============================================
// PROCESAR ACCIÓN
// ============================================

try {
    $db = getDB();
    
    // Obtener información del sitio
    $stmt = $db->prepare("SELECT * FROM websites WHERE id = ?");
    $stmt->execute([$id]);
    $website = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$website) {
        showPage('Error', 'Sitio no encontrado en la base de datos.', 'error');
    }
    
    $domain = $website['domain'];
    $businessName = $website['business_name'];
    
    if ($action === 'approve') {
        // ========== APROBAR ==========
        
        // Verificar que existe en staging
        $stagingDir = STAGING_BASE . '/' . $domain;
        if (!file_exists($stagingDir . '/index.html')) {
            showPage('Error', 'No se encontraron archivos en staging para este sitio.', 'error');
        }
        
        // Copiar de staging a domains
        $productionDir = DOMAINS_BASE . '/' . $domain;
        copyDirectory($stagingDir, $productionDir);
        
        // Actualizar base de datos
        $stmt = $db->prepare("
            UPDATE websites SET 
                status = 'live',
                production_url = ?,
                published_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            'https://otavafitness.com/domains/' . $domain . '/',
            $id
        ]);
        
        // Registrar aprobación
        if (function_exists('insertApproval')) {
            insertApproval($id, 'admin', 'approved', 'Aprobado desde email');
        }
        
        logApproval('Sitio aprobado', [
            'id' => $id,
            'domain' => $domain,
            'business' => $businessName
        ]);
        
        $productionUrl = 'https://otavafitness.com/domains/' . $domain . '/';
        
        showPage(
            'Sitio Aprobado',
            "El sitio <strong>$businessName</strong> ha sido publicado exitosamente.<br><br>
            <div class='details'>
                <strong>URL de producción:</strong><br>
                <a href='$productionUrl' target='_blank'>$productionUrl</a>
            </div>",
            'success'
        );
        
    } else {
        // ========== RECHAZAR ==========
        
        // Actualizar base de datos
        $stmt = $db->prepare("
            UPDATE websites SET 
                status = 'rejected',
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        
        // Registrar rechazo
        if (function_exists('insertApproval')) {
            insertApproval($id, 'admin', 'rejected', 'Rechazado desde email');
        }
        
        logApproval('Sitio rechazado', [
            'id' => $id,
            'domain' => $domain,
            'business' => $businessName
        ]);
        
        showPage(
            'Sitio Rechazado',
            "El sitio <strong>$businessName</strong> ha sido marcado para revisión.<br><br>
            Los archivos permanecen en staging para futuras modificaciones.",
            'warning'
        );
    }
    
} catch (PDOException $e) {
    logApproval('Error de base de datos', ['error' => $e->getMessage()]);
    showPage('Error', 'Error de base de datos: ' . $e->getMessage(), 'error');
    
} catch (Exception $e) {
    logApproval('Error general', ['error' => $e->getMessage()]);
    showPage('Error', $e->getMessage(), 'error');
}
