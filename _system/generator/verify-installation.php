<?php
/**
 * VERIFY-INSTALLATION.PHP
 * 
 * Verifica que la instalación en Hostinger esté correcta
 * 
 * Uso: php verify-installation.php
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  🔍 VERIFICACIÓN DE INSTALACIÓN - HOSTINGER                   ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$errors = 0;
$warnings = 0;
$checks = 0;

// Determinar base dir
$baseDir = dirname(dirname(__DIR__));
echo "📂 Base Directory: $baseDir\n\n";

// ========================================
// 1. ESTRUCTURA DE CARPETAS
// ========================================
echo "[1/15] Estructura de carpetas... ";
$checks++;

$requiredDirs = [
    $baseDir . '/_system',
    $baseDir . '/_system/generator',
    $baseDir . '/_system/templates',
    $baseDir . '/_system/logs',
    $baseDir . '/_system/config',
    $baseDir . '/_system/queue',
    $baseDir . '/domains',
    $baseDir . '/staging',
    $baseDir . '/public_html'
];

$allDirsOk = true;
foreach ($requiredDirs as $dir) {
    if (!file_exists($dir)) {
        if ($allDirsOk) echo "\n";
        echo "   ❌ Falta: $dir\n";
        $errors++;
        $allDirsOk = false;
    }
}

if ($allDirsOk) {
    echo "✅\n";
} else {
    echo "\n";
}

// ========================================
// 2. PERMISOS CRÍTICOS
// ========================================
echo "[2/15] Permisos de seguridad... ";
$checks++;

$permsOk = true;

// _system debe ser 700
$systemPerms = substr(sprintf('%o', fileperms($baseDir . '/_system')), -3);
if ($systemPerms !== '700') {
    if ($permsOk) echo "\n";
    echo "   ⚠️  _system/ debería ser 700, es $systemPerms\n";
    $warnings++;
    $permsOk = false;
}

// .env debe ser 600 si existe
$envFile = $baseDir . '/_system/config/.env';
if (file_exists($envFile)) {
    $envPerms = substr(sprintf('%o', fileperms($envFile)), -3);
    if ($envPerms !== '600') {
        if ($permsOk) echo "\n";
        echo "   ⚠️  .env debería ser 600, es $envPerms\n";
        $warnings++;
        $permsOk = false;
    }
}

if ($permsOk) {
    echo "✅\n";
} else {
    echo "\n";
}

// ========================================
// 3. ARCHIVO .ENV
// ========================================
echo "[3/15] Archivo .env... ";
$checks++;

if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    
    $envOk = true;
    $requiredVars = ['MAKE_SECRET', 'ADMIN_EMAIL', 'BASE_URL'];
    
    foreach ($requiredVars as $var) {
        if (strpos($envContent, $var) === false) {
            if ($envOk) echo "\n";
            echo "   ⚠️  Variable faltante: $var\n";
            $warnings++;
            $envOk = false;
        }
    }
    
    if ($envOk) {
        echo "✅\n";
    } else {
        echo "\n";
    }
} else {
    echo "❌ No existe\n";
    $errors++;
}

// ========================================
// 4. SCRIPTS CRÍTICOS
// ========================================
echo "[4/15] Scripts críticos... ";
$checks++;

$requiredScripts = [
    $baseDir . '/_system/generator/create-domain.php',
    $baseDir . '/_system/generator/backup-client.php',
    $baseDir . '/_system/generator/backup-all.php',
    $baseDir . '/_system/generator/health-check.php',
    $baseDir . '/_system/generator/verify-domain.php',
    $baseDir . '/_system/generator/cleanup-old.php',
    $baseDir . '/public_html/generator/deploy.php'
];

$allScriptsOk = true;
foreach ($requiredScripts as $script) {
    if (!file_exists($script)) {
        if ($allScriptsOk) echo "\n";
        echo "   ❌ Falta: " . basename($script) . "\n";
        $errors++;
        $allScriptsOk = false;
    }
}

if ($allScriptsOk) {
    echo "✅\n";
} else {
    echo "\n";
}

// ========================================
// 5. TEMPLATES
// ========================================
echo "[5/15] Templates... ";
$checks++;

$requiredTemplates = [
    $baseDir . '/_system/templates/landing-pro',
    $baseDir . '/_system/templates/landing-basica',
    $baseDir . '/_system/templates/componentes-globales'
];

$allTemplatesOk = true;
foreach ($requiredTemplates as $template) {
    if (!file_exists($template)) {
        if ($allTemplatesOk) echo "\n";
        echo "   ❌ Falta: " . basename($template) . "\n";
        $errors++;
        $allTemplatesOk = false;
    }
}

if ($allTemplatesOk) {
    echo "✅\n";
} else {
    echo "\n";
}

// ========================================
// 6. DOMAINS.JSON
// ========================================
echo "[6/15] domains.json... ";
$checks++;

$domainsFile = $baseDir . '/_system/config/domains.json';
if (file_exists($domainsFile)) {
    $content = file_get_contents($domainsFile);
    $domains = json_decode($content, true);
    
    if (is_array($domains)) {
        echo "✅ (" . count($domains) . " dominios)\n";
    } else {
        echo "⚠️  JSON inválido\n";
        $warnings++;
    }
} else {
    echo "❌ No existe\n";
    $errors++;
}

// ========================================
// 7. PHP VERSION
// ========================================
echo "[7/15] Versión PHP... ";
$checks++;

$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "✅ ($phpVersion)\n";
} else {
    echo "⚠️  $phpVersion (recomendado >= 7.4)\n";
    $warnings++;
}

// ========================================
// 8. PHP EXTENSIONS
// ========================================
echo "[8/15] Extensiones PHP... ";
$checks++;

$requiredExts = ['json', 'curl', 'mbstring', 'openssl'];
$allExtsOk = true;

foreach ($requiredExts as $ext) {
    if (!extension_loaded($ext)) {
        if ($allExtsOk) echo "\n";
        echo "   ❌ Falta extensión: $ext\n";
        $errors++;
        $allExtsOk = false;
    }
}

if ($allExtsOk) {
    echo "✅\n";
} else {
    echo "\n";
}

// ========================================
// 9. FUNCIONES PHP
// ========================================
echo "[9/15] Funciones PHP... ";
$checks++;

$requiredFuncs = ['exec', 'flock', 'curl_init', 'openssl_x509_parse'];
$allFuncsOk = true;

foreach ($requiredFuncs as $func) {
    if (!function_exists($func)) {
        if ($allFuncsOk) echo "\n";
        echo "   ⚠️  Función no disponible: $func\n";
        $warnings++;
        $allFuncsOk = false;
    }
}

if ($allFuncsOk) {
    echo "✅\n";
} else {
    echo "\n";
}

// ========================================
// 10. PERMISOS DE ESCRITURA
// ========================================
echo "[10/15] Permisos de escritura... ";
$checks++;

$writableDirs = [
    $baseDir . '/_system/logs',
    $baseDir . '/_system/queue',
    $baseDir . '/domains',
    $baseDir . '/staging'
];

$allWritable = true;
foreach ($writableDirs as $dir) {
    if (file_exists($dir) && !is_writable($dir)) {
        if ($allWritable) echo "\n";
        echo "   ❌ No escribible: " . basename($dir) . "\n";
        $errors++;
        $allWritable = false;
    }
}

if ($allWritable) {
    echo "✅\n";
} else {
    echo "\n";
}

// ========================================
// 11. TEST DE ESCRITURA
// ========================================
echo "[11/15] Test de escritura... ";
$checks++;

$testFile = $baseDir . '/_system/logs/test-' . time() . '.txt';
if (@file_put_contents($testFile, 'test') !== false) {
    @unlink($testFile);
    echo "✅\n";
} else {
    echo "❌ No se puede escribir en logs\n";
    $errors++;
}

// ========================================
// 12. FILE LOCKING
// ========================================
echo "[12/15] File locking... ";
$checks++;

$lockFile = sys_get_temp_dir() . '/test-' . time() . '.lock';
$fp = @fopen($lockFile, 'c');

if ($fp && @flock($fp, LOCK_EX)) {
    flock($fp, LOCK_UN);
    fclose($fp);
    @unlink($lockFile);
    echo "✅\n";
} else {
    echo "❌ flock() no funciona\n";
    $errors++;
    if ($fp) fclose($fp);
}

// ========================================
// 13. CONECTIVIDAD
// ========================================
echo "[13/15] Conectividad externa... ";
$checks++;

if (function_exists('curl_init')) {
    $ch = curl_init('https://www.google.com');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    $result = @curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅\n";
    } else {
        echo "⚠️  Sin internet o bloqueado (code: $httpCode)\n";
        $warnings++;
    }
} else {
    echo "⚠️  curl no disponible\n";
    $warnings++;
}

// ========================================
// 14. ESPACIO EN DISCO
// ========================================
echo "[14/15] Espacio en disco... ";
$checks++;

$freeSpace = @disk_free_space($baseDir);
if ($freeSpace !== false) {
    $freeGB = round($freeSpace / 1024 / 1024 / 1024, 2);
    
    if ($freeGB > 10) {
        echo "✅ ({$freeGB} GB libres)\n";
    } elseif ($freeGB > 5) {
        echo "⚠️  {$freeGB} GB libres (bajo)\n";
        $warnings++;
    } else {
        echo "❌ {$freeGB} GB libres (crítico)\n";
        $errors++;
    }
} else {
    echo "⚠️  No se pudo verificar\n";
    $warnings++;
}

// ========================================
// 15. PROXY DEPLOY.PHP
// ========================================
echo "[15/15] Proxy deploy.php... ";
$checks++;

$proxyFile = $baseDir . '/public_html/generator/deploy.php';
if (file_exists($proxyFile)) {
    $proxyContent = file_get_contents($proxyFile);
    
    $proxyOk = true;
    
    // Verificar que tenga validación de token
    if (strpos($proxyContent, 'X-Make-Secret') === false) {
        if ($proxyOk) echo "\n";
        echo "   ⚠️  No valida X-Make-Secret\n";
        $warnings++;
        $proxyOk = false;
    }
    
    // Verificar que incluya el script real
    if (strpos($proxyContent, 'deploy-v4-mejorado.php') === false) {
        if ($proxyOk) echo "\n";
        echo "   ⚠️  No incluye deploy-v4-mejorado.php\n";
        $warnings++;
        $proxyOk = false;
    }
    
    if ($proxyOk) {
        echo "✅\n";
    } else {
        echo "\n";
    }
} else {
    echo "❌ No existe\n";
    $errors++;
}

// ========================================
// RESUMEN
// ========================================
echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  📊 RESUMEN DE VERIFICACIÓN                                   ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "Checks realizados:  $checks\n";
echo "✅ Sin problemas:   " . ($checks - $errors - $warnings) . "\n";
echo "⚠️  Advertencias:    $warnings\n";
echo "❌ Errores:          $errors\n";
echo "\n";

// Estado final
if ($errors === 0 && $warnings === 0) {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ INSTALACIÓN PERFECTA - SISTEMA LISTO                      ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "🚀 El sistema está 100% operativo y listo para producción.\n";
    echo "\n";
    echo "Próximos pasos:\n";
    echo "1. Configurar cron jobs (si no están)\n";
    echo "2. Configurar Make.com\n";
    echo "3. Probar generación de sitio\n";
    echo "\n";
    exit(0);
    
} elseif ($errors === 0 && $warnings > 0) {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ⚠️  INSTALACIÓN OK CON ADVERTENCIAS                          ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "El sistema funciona pero hay advertencias que deberías revisar.\n";
    echo "\n";
    exit(0);
    
} else {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ❌ INSTALACIÓN CON ERRORES                                   ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "⚠️  Hay $errors error(es) que deben corregirse antes de usar el sistema.\n";
    echo "\n";
    echo "Revisar:\n";
    echo "- Estructura de carpetas completa\n";
    echo "- Permisos correctos\n";
    echo "- Todos los scripts presentes\n";
    echo "\n";
    exit(1);
}
?>
