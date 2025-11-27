<?php
/**
 * VERIFY-DOMAIN.PHP
 * 
 * Verifica la configuración y salud de un dominio específico
 * 
 * Uso: php verify-domain.php dominio.com
 */

// Configuración
$baseDir = dirname(dirname(__DIR__));

// Validar argumentos
if ($argc < 2) {
    echo "Uso: php verify-domain.php dominio.com\n";
    exit(1);
}

$domain = $argv[1];

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  🔍 VERIFICANDO DOMINIO: $domain\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$checks = 0;
$passed = 0;
$warnings = 0;
$errors = 0;

// ======================================================================
// 1. VERIFICAR ESTRUCTURA DE CARPETAS
// ======================================================================
echo "[1/7] Estructura de carpetas...\n";
$checks++;

$domainDir = $baseDir . '/domains/' . $domain;

if (!file_exists($domainDir)) {
    echo "  ❌ ERROR: El dominio no existe en /domains/\n";
    $errors++;
} else {
    echo "  ✅ Carpeta del dominio existe\n";
    
    // Verificar subcarpetas
    $requiredDirs = ['public_html', 'logs', 'backups'];
    $allDirsOk = true;
    
    foreach ($requiredDirs as $dir) {
        if (!file_exists($domainDir . '/' . $dir)) {
            echo "  ⚠️  Falta carpeta: $dir\n";
            $warnings++;
            $allDirsOk = false;
        }
    }
    
    if ($allDirsOk) {
        $passed++;
    }
}

echo "\n";

// ======================================================================
// 2. VERIFICAR ARCHIVOS ESENCIALES
// ======================================================================
echo "[2/7] Archivos esenciales...\n";
$checks++;

if (file_exists($domainDir)) {
    $publicHtml = $domainDir . '/public_html';
    $requiredFiles = ['index.html', '.htaccess'];
    $filesOk = true;
    
    foreach ($requiredFiles as $file) {
        if (!file_exists($publicHtml . '/' . $file)) {
            echo "  ⚠️  Falta archivo: $file\n";
            $warnings++;
            $filesOk = false;
        }
    }
    
    if ($filesOk) {
        echo "  ✅ Archivos esenciales presentes\n";
        $passed++;
    }
} else {
    echo "  ❌ No se puede verificar (dominio no existe)\n";
    $errors++;
}

echo "\n";

// ======================================================================
// 3. VERIFICAR METADATA
// ======================================================================
echo "[3/7] Metadata...\n";
$checks++;

$metadataFile = $domainDir . '/.metadata.json';

if (file_exists($metadataFile)) {
    $metadata = json_decode(file_get_contents($metadataFile), true);
    
    if ($metadata && isset($metadata['domain'])) {
        echo "  ✅ Metadata válido\n";
        echo "      Dominio: " . ($metadata['domain'] ?? 'N/A') . "\n";
        echo "      Creado: " . ($metadata['created'] ?? 'N/A') . "\n";
        echo "      Staging: " . ($metadata['staging_slug'] ?? 'N/A') . "\n";
        $passed++;
    } else {
        echo "  ⚠️  Metadata inválido o corrupto\n";
        $warnings++;
    }
} else {
    echo "  ⚠️  Archivo .metadata.json no existe\n";
    $warnings++;
}

echo "\n";

// ======================================================================
// 4. VERIFICAR DNS (si curl disponible)
// ======================================================================
echo "[4/7] Resolución DNS...\n";
$checks++;

if (function_exists('gethostbyname')) {
    $ip = gethostbyname($domain);
    
    if ($ip && $ip !== $domain) {
        echo "  ✅ DNS resuelve a: $ip\n";
        $passed++;
    } else {
        echo "  ⚠️  DNS no resuelve (dominio no configurado aún)\n";
        $warnings++;
    }
} else {
    echo "  ℹ️  Función gethostbyname no disponible\n";
}

echo "\n";

// ======================================================================
// 5. VERIFICAR HTTP (si curl disponible)
// ======================================================================
echo "[5/7] Respuesta HTTP...\n";
$checks++;

if (function_exists('curl_init')) {
    $ch = curl_init("http://$domain");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $result = @curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "  ✅ HTTP responde: $httpCode\n";
        $passed++;
    } else {
        echo "  ⚠️  HTTP no responde correctamente: $httpCode\n";
        $warnings++;
    }
} else {
    echo "  ℹ️  cURL no disponible, saltando verificación HTTP\n";
}

echo "\n";

// ======================================================================
// 6. VERIFICAR SSL (si openssl disponible)
// ======================================================================
echo "[6/7] Certificado SSL...\n";
$checks++;

if (function_exists('openssl_x509_parse')) {
    $context = stream_context_create([
        'ssl' => [
            'capture_peer_cert' => true,
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $client = @stream_socket_client(
        "ssl://$domain:443",
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    );
    
    if ($client) {
        $params = stream_context_get_params($client);
        $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
        
        if ($cert) {
            $validTo = date('Y-m-d', $cert['validTo_time_t']);
            $daysLeft = floor(($cert['validTo_time_t'] - time()) / 86400);
            
            if ($daysLeft > 0) {
                echo "  ✅ SSL válido hasta: $validTo ($daysLeft días)\n";
                $passed++;
            } else {
                echo "  ⚠️  SSL EXPIRADO desde: $validTo\n";
                $warnings++;
            }
        }
        
        fclose($client);
    } else {
        echo "  ⚠️  SSL no disponible o dominio sin HTTPS\n";
        $warnings++;
    }
} else {
    echo "  ℹ️  OpenSSL no disponible, saltando verificación SSL\n";
}

echo "\n";

// ======================================================================
// 7. VERIFICAR ESPACIO EN DISCO
// ======================================================================
echo "[7/7] Uso de disco...\n";
$checks++;

if (file_exists($domainDir)) {
    $size = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($domainDir)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }
    
    $sizeMB = round($size / 1024 / 1024, 2);
    
    if ($sizeMB < 50) {
        echo "  ✅ Tamaño: {$sizeMB} MB (normal)\n";
        $passed++;
    } elseif ($sizeMB < 100) {
        echo "  ⚠️  Tamaño: {$sizeMB} MB (alto)\n";
        $warnings++;
    } else {
        echo "  ⚠️  Tamaño: {$sizeMB} MB (muy alto)\n";
        $warnings++;
    }
} else {
    echo "  ❌ No se puede verificar (dominio no existe)\n";
    $errors++;
}

echo "\n";

// ======================================================================
// RESUMEN
// ======================================================================
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  📊 RESUMEN DE VERIFICACIÓN\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$score = ($checks > 0) ? round(($passed / $checks) * 100) : 0;

echo "Checks realizados:  $checks\n";
echo "✅ Pasados:         $passed\n";
echo "⚠️  Advertencias:    $warnings\n";
echo "❌ Errores:          $errors\n";
echo "\n";
echo "Score:              $score/100\n";
echo "\n";

// Estado final
if ($errors > 0) {
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║  ❌ DOMINIO CON ERRORES CRÍTICOS\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    exit(1);
} elseif ($warnings > 0) {
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║  ⚠️  DOMINIO FUNCIONAL CON ADVERTENCIAS\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ DOMINIO PERFECTO\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    exit(0);
}
?>
