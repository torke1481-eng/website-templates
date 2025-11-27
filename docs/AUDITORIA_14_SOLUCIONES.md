# ✅ AUDITORÍA #14 - SOLUCIONES A PROBLEMAS CRÍTICOS

**Fecha:** 25 Nov 2025  
**Tipo:** Fixes para 21 Problemas Encontrados  
**Objetivo:** Sistema Perfecto y Production-Ready

---

## 🔧 FIXES APLICAR

### **FIX #1: Eliminar @ Operators de deploy-v4-mejorado.php**

```php
// ANTES (MAL):
@mkdir($logDir, 0755, true);

// DESPUÉS (BIEN):
if (!file_exists($logDir)) {
    if (!mkdir($logDir, 0755, true)) {
        $error = error_get_last();
        throw new Exception("No se pudo crear logs: {$error['message']}");
    }
}

// ANTES (MAL):
$header = @file_get_contents($path) ?: "<header>fallback</header>";

// DESPUÉS (BIEN):
if (!file_exists($path)) {
    logError("Header no encontrado", ['path' => $path]);
    $header = "<header><h1>$nombreNegocio</h1></header>";
} else {
    $header = file_get_contents($path);
    if ($header === false) {
        $error = error_get_last();
        throw new Exception("No se pudo leer header: {$error['message']}");
    }
}
```

---

### **FIX #2: Fix Lock Cleanup en create-domain.php**

```php
// ANTES (MAL):
register_shutdown_function(function() use ($lockFile) {
    @unlink($lockFile);
});

// DESPUÉS (BIEN):
register_shutdown_function(function() use ($lockFile) {
    if (file_exists($lockFile)) {
        if (!unlink($lockFile)) {
            $error = error_get_last();
            error_log("Lock cleanup failed: $lockFile - {$error['message']}");
        }
    }
});
```

---

### **FIX #3: Fix date() en Heredoc**

```php
// ANTES (MAL):
$htaccess = <<<HTACCESS
# Fecha: {date('Y-m-d H:i:s')}
HTACCESS;

// DESPUÉS (BIEN):
$currentDate = date('Y-m-d H:i:s');
$htaccess = <<<HTACCESS
# Fecha: $currentDate
HTACCESS;
```

---

### **FIX #4: Función Multiplataforma para Copiar Directorios**

Agregar al inicio de `create-domain.php`:

```php
/**
 * Copia recursiva multiplataforma (Windows + Linux)
 */
function recursiveCopy($src, $dest) {
    if (!file_exists($src)) {
        throw new Exception("Origen no existe: $src");
    }
    
    if (!is_dir($src)) {
        if (!copy($src, $dest)) {
            throw new Exception("No se pudo copiar archivo: $src → $dest");
        }
        return true;
    }
    
    if (!file_exists($dest)) {
        if (!mkdir($dest, 0755, true)) {
            throw new Exception("No se pudo crear directorio: $dest");
        }
    }
    
    $dir = opendir($src);
    if ($dir === false) {
        throw new Exception("No se pudo abrir directorio: $src");
    }
    
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $srcFile = $src . '/' . $file;
        $destFile = $dest . '/' . $file;
        
        if (is_dir($srcFile)) {
            recursiveCopy($srcFile, $destFile);
        } else {
            if (!copy($srcFile, $destFile)) {
                throw new Exception("No se pudo copiar: $srcFile");
            }
        }
    }
    
    closedir($dir);
    return true;
}

// Reemplazar línea 115:
// ANTES:
if (is_dir($file)) {
    exec("cp -r " . escapeshellarg($file) . " " . escapeshellarg($dest));
} else {
    copy($file, $dest);
}

// DESPUÉS:
recursiveCopy($file, $dest);
```

---

### **FIX #5: File Locking para domains.json**

```php
/**
 * Agrega dominio a config con file locking
 */
function addDomainToConfig($domain, $path, $status) {
    $configDir = dirname(__DIR__) . '/config';
    if (!file_exists($configDir)) {
        mkdir($configDir, 0755, true);
    }
    
    $configFile = $configDir . '/domains.json';
    $lockFile = $configFile . '.lock';
    
    // Crear lock file
    $fp = fopen($lockFile, 'w');
    if ($fp === false) {
        throw new Exception('No se pudo crear lock file');
    }
    
    // Adquirir lock exclusivo (bloquea otros procesos)
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        throw new Exception('No se pudo adquirir lock');
    }
    
    try {
        // Leer config actual
        $domains = [];
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            $domains = json_decode($content, true);
            
            if (!is_array($domains)) {
                logAction('Config corrupto, reiniciando', ['file' => $configFile]);
                $domains = [];
            }
        }
        
        // Verificar que no exista ya
        foreach ($domains as $existing) {
            if ($existing['domain'] === $domain) {
                throw new Exception("Dominio '$domain' ya está registrado");
            }
        }
        
        // Agregar nuevo
        $domains[] = [
            'domain' => $domain,
            'path' => $path,
            'status' => $status,
            'created' => date('c')
        ];
        
        // Guardar
        $json = json_encode($domains, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($configFile, $json) === false) {
            throw new Exception('No se pudo escribir config');
        }
        
        return true;
        
    } finally {
        // Siempre liberar lock
        flock($fp, LOCK_UN);
        fclose($fp);
        @unlink($lockFile);
    }
}

// Reemplazar líneas 250-274 en create-domain.php con:
addDomainToConfig($domain, $domainDir . '/public_html', 'pending_cpanel');
```

---

### **FIX #6-9: Crear Scripts Faltantes**

#### **public_html/generator/deploy.php (Proxy)**

```php
<?php
/**
 * PROXY SEGURO PARA MAKE.COM
 * Valida y redirige a sistema protegido
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // No exponer errores en producción

// 1. Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// 2. Validar token secreto
$secret = $_SERVER['HTTP_X_MAKE_SECRET'] ?? '';
$expectedSecret = getenv('MAKE_SECRET');

// Si no hay .env, usar fallback (cambiar en producción)
if (!$expectedSecret) {
    $expectedSecret = 'CAMBIAR_EN_PRODUCCION_' . md5(__FILE__);
    error_log('⚠️ WARNING: MAKE_SECRET no configurado, usando fallback');
}

if (hash_equals($expectedSecret, $secret) === false) {
    http_response_code(403);
    echo json_encode([
        'error' => 'Forbidden',
        'code' => 'INVALID_SECRET',
        'hint' => 'Configurar header X-Make-Secret'
    ]);
    exit;
}

// 3. Rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateFile = sys_get_temp_dir() . '/rate-' . md5($ip) . '.txt';

if (file_exists($rateFile)) {
    $requests = file($rateFile, FILE_IGNORE_NEW_LINES);
    $now = time();
    
    // Filtrar requests viejos (>60s)
    $requests = array_filter($requests, function($ts) use ($now) {
        return ($now - (int)$ts) < 60;
    });
    
    // Máximo 10 requests por minuto
    if (count($requests) >= 10) {
        http_response_code(429);
        echo json_encode([
            'error' => 'Rate limit exceeded',
            'retry_after' => 60,
            'current_requests' => count($requests)
        ]);
        exit;
    }
    
    $requests[] = $now;
} else {
    $requests = [time()];
}

file_put_contents($rateFile, implode("\n", $requests));

// 4. Log de acceso
$logDir = dirname(__DIR__) . '/_system/logs';
if (!file_exists($logDir)) {
    @mkdir($logDir, 0755, true);
}

$logEntry = [
    'timestamp' => date('c'),
    'ip' => $ip,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'referer' => $_SERVER['HTTP_REFERER'] ?? 'none'
];

file_put_contents(
    $logDir . '/make-access.log',
    json_encode($logEntry) . "\n",
    FILE_APPEND
);

// 5. Incluir script real
chdir(dirname(__DIR__) . '/_system/generator');
require_once dirname(__DIR__) . '/_system/generator/deploy-v4-mejorado.php';
?>
```

#### **_system/generator/verify-domain.php**

```php
<?php
/**
 * VERIFY-DOMAIN.PHP
 * Verifica configuración completa de un dominio
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo se ejecuta desde CLI');
}

if ($argc < 2) {
    echo "Uso: php verify-domain.php dominio.com\n";
    exit(1);
}

$domain = $argv[1];
$baseDir = dirname(dirname(__DIR__));

echo "\n";
echo "🔍 VERIFICANDO: $domain\n";
echo str_repeat('═', 50) . "\n\n";

$checks = [];
$allOk = true;

// 1. DNS Check
echo "[1/6] DNS... ";
$ip = gethostbyname($domain);
if ($ip !== $domain && filter_var($ip, FILTER_VALIDATE_IP)) {
    echo "✅ OK ($ip)\n";
    $checks['dns'] = true;
} else {
    echo "❌ FAIL\n";
    $checks['dns'] = false;
    $allOk = false;
}

// 2. HTTP Check
echo "[2/6] HTTP... ";
$ch = curl_init("https://$domain");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_NOBODY => true
]);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ OK ($httpCode)\n";
    $checks['http'] = true;
} else {
    echo "❌ FAIL ($httpCode)\n";
    $checks['http'] = false;
    $allOk = false;
}

// 3. SSL Check
echo "[3/6] SSL... ";
$ctx = stream_context_create([
    "ssl" => [
        "capture_peer_cert" => true,
        "verify_peer" => false
    ]
]);
$client = @stream_socket_client(
    "ssl://$domain:443",
    $errno,
    $errstr,
    10,
    STREAM_CLIENT_CONNECT,
    $ctx
);

if ($client) {
    $params = stream_context_get_params($client);
    $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
    $validUntil = $cert['validTo_time_t'];
    $daysLeft = floor(($validUntil - time()) / 86400);
    
    if ($daysLeft > 0) {
        echo "✅ OK ($daysLeft días)\n";
        $checks['ssl'] = true;
    } else {
        echo "❌ EXPIRADO\n";
        $checks['ssl'] = false;
        $allOk = false;
    }
    fclose($client);
} else {
    echo "❌ FAIL\n";
    $checks['ssl'] = false;
    $allOk = false;
}

// 4. Files Check
echo "[4/6] Archivos... ";
$domainPath = $baseDir . '/domains/' . $domain;

if (file_exists($domainPath . '/public_html/index.html')) {
    echo "✅ OK\n";
    $checks['files'] = true;
} else {
    echo "❌ FAIL\n";
    $checks['files'] = false;
    $allOk = false;
}

// 5. Metadata Check
echo "[5/6] Metadata... ";
$metadataPath = $domainPath . '/.metadata.json';

if (file_exists($metadataPath)) {
    $metadata = json_decode(file_get_contents($metadataPath), true);
    
    if ($allOk) {
        // Actualizar estado
        $metadata['status'] = 'active';
        $metadata['dns_status'] = 'configured';
        $metadata['ssl_enabled'] = $checks['ssl'];
        $metadata['verified_at'] = date('c');
        $metadata['last_check'] = [
            'timestamp' => date('c'),
            'checks' => $checks
        ];
        
        file_put_contents(
            $metadataPath,
            json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        echo "✅ OK (actualizado)\n";
    } else {
        echo "⚠️  Existe pero hay problemas\n";
    }
    $checks['metadata'] = true;
} else {
    echo "❌ FAIL\n";
    $checks['metadata'] = false;
    $allOk = false;
}

// 6. Config Check
echo "[6/6] Config... ";
$configFile = $baseDir . '/_system/config/domains.json';

if (file_exists($configFile)) {
    $domains = json_decode(file_get_contents($configFile), true);
    $found = false;
    
    foreach ($domains as &$d) {
        if ($d['domain'] === $domain) {
            $found = true;
            if ($allOk) {
                $d['status'] = 'active';
                $d['last_verified'] = date('c');
            }
            break;
        }
    }
    
    if ($found) {
        file_put_contents(
            $configFile,
            json_encode($domains, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        echo "✅ OK\n";
    } else {
        echo "⚠️  No encontrado en config\n";
    }
} else {
    echo "❌ Config no existe\n";
}

// Resumen
echo "\n";
echo str_repeat('═', 50) . "\n";

if ($allOk) {
    echo "✅ DOMINIO COMPLETAMENTE CONFIGURADO\n";
    echo "   URL: https://$domain\n";
    exit(0);
} else {
    echo "❌ DOMINIO CON PROBLEMAS\n\n";
    echo "Revisar:\n";
    if (!$checks['dns']) echo "  - DNS records\n";
    if (!$checks['http']) echo "  - Apache/cPanel config\n";
    if (!$checks['ssl']) echo "  - SSL certificate\n";
    if (!$checks['files']) echo "  - Archivos del sitio\n";
    exit(1);
}
?>
```

#### **_system/generator/cleanup-old.php**

```php
<?php
/**
 * CLEANUP-OLD.PHP
 * Limpia staging sites > 7 días
 */

$baseDir = dirname(dirname(__DIR__));
$stagingDir = $baseDir . '/staging';

if (!file_exists($stagingDir)) {
    echo "ℹ️  No hay directorio staging\n";
    exit(0);
}

echo "\n🧹 LIMPIEZA DE STAGING\n";
echo str_repeat('═', 50) . "\n\n";

$dirs = glob($stagingDir . '/*', GLOB_ONLYDIR);
$now = time();
$deleted = 0;
$freedSpace = 0;
$maxAge = 7 * 86400; // 7 días

foreach ($dirs as $dir) {
    $age = $now - filemtime($dir);
    $days = floor($age / 86400);
    
    if ($age > $maxAge) {
        $size = getDirSize($dir);
        $slug = basename($dir);
        
        echo "🗑️  $slug ($days días, " . formatBytes($size) . ")... ";
        
        if (deleteDirectory($dir)) {
            echo "✅\n";
            $deleted++;
            $freedSpace += $size;
        } else {
            echo "❌\n";
        }
    }
}

echo "\n";
echo str_repeat('═', 50) . "\n";
echo "✅ Limpieza completada\n";
echo "   Eliminados: $deleted sitios\n";
echo "   Liberados: " . formatBytes($freedSpace) . "\n";

function getDirSize($dir) {
    $size = 0;
    try {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            $size += $file->getSize();
        }
    } catch (Exception $e) {
        error_log("Error calculating size for $dir: " . $e->getMessage());
    }
    return $size;
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    try {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        return rmdir($dir);
    } catch (Exception $e) {
        error_log("Error deleting $dir: " . $e->getMessage());
        return false;
    }
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>
```

---

### **FIX #10: Usar .env para Secrets**

Crear `.env.example`:
```bash
# Make.com Integration
MAKE_SECRET=your_secret_token_here

# Admin Notifications
ADMIN_EMAIL=admin@tudominio.com
SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id

# Client Webhooks
CLIENT_WEBHOOK_URL=https://your-crm.com/webhook
WEBHOOK_SECRET=your_webhook_secret

# Database (si usas)
DB_HOST=localhost
DB_USER=u123456_user
DB_PASS=secure_password
DB_NAME=u123456_database
```

Crear `.env` (NO subir a Git):
```bash
MAKE_SECRET=a8f7d9c2e1b4f6h3j5k9m2n4p7q8r1s3t5u7v9w1x3y5
ADMIN_EMAIL=tu@email.com
# ... valores reales
```

Agregar a `.gitignore`:
```gitignore
.env
_system/config/domains.json
_system/logs/
_system/queue/
/domains/
/staging/
*.tar.gz
*.log
```

---

### **FIX #11: Rate Limiting** 

Ya incluido en proxy deploy.php (FIX #6)

---

### **FIX #12: Fix .htaccess vs .txt Conflict**

```php
// En create-domain.php línea 349:
// ANTES:
$instructionsFile = $domainDir . '/public_html/CPANEL_INSTRUCTIONS.txt';

// DESPUÉS (fuera de public_html):
$instructionsFile = $domainDir . '/CPANEL_INSTRUCTIONS.txt';
```

---

### **FIX #13-17: Mejoras de Integración**

Ver archivo separado `AUDITORIA_14_INTEGRACIONES.md`

---

### **FIX #18-21: Estandarización**

#### **Cron Jobs Estándar:**
```cron
# Backups diarios 3 AM
0 3 * * * /usr/bin/php /home/u123456789/_system/generator/backup-all.php >> /home/u123456789/_system/logs/backups.log 2>&1

# Health check cada hora
0 * * * * /usr/bin/php /home/u123456789/_system/generator/health-check.php >> /home/u123456789/_system/logs/health.log 2>&1

# Cleanup staging viejo (diario 4 AM)
0 4 * * * /usr/bin/php /home/u123456789/_system/generator/cleanup-old.php >> /home/u123456789/_system/logs/cleanup.log 2>&1

# Queue processor cada 5 min (fallback Make.com)
*/5 * * * * /usr/bin/php /home/u123456789/_system/generator/queue-processor.php >> /home/u123456789/_system/logs/queue.log 2>&1
```

#### **Constantes Globales:**

Crear `_system/config/constants.php`:
```php
<?php
/**
 * CONSTANTES GLOBALES DEL SISTEMA
 */

// Hosting
define('HOSTING_USER', 'u123456789');
define('BASE_DIR', '/home/' . HOSTING_USER);
define('SYSTEM_DIR', BASE_DIR . '/_system');
define('DOMAINS_DIR', BASE_DIR . '/domains');
define('STAGING_DIR', BASE_DIR . '/staging');
define('PUBLIC_DIR', BASE_DIR . '/public_html');

// URLs
define('BASE_URL', 'https://otavafitness.com');
define('STAGING_URL', BASE_URL . '/staging');

// Límites
define('MAX_DOMAINS', 500);
define('MAX_SITE_SIZE_MB', 20);
define('STAGING_MAX_AGE_DAYS', 7);
define('BACKUP_KEEP_COUNT', 7);

// Rate Limiting
define('RATE_LIMIT_REQUESTS', 10);
define('RATE_LIMIT_WINDOW', 60); // segundos

// Timeouts
define('CURL_TIMEOUT', 10);
define('HEALTH_CHECK_TIMEOUT', 30);

// Paths de templates
define('TEMPLATES_DIR', SYSTEM_DIR . '/templates');
define('COMPONENTES_DIR', TEMPLATES_DIR . '/componentes-globales');

// Logs
define('LOGS_DIR', SYSTEM_DIR . '/logs');
define('ERRORS_DIR', LOGS_DIR . '/errors');
define('HEALTH_DIR', LOGS_DIR . '/health');

// Incluir en cada script:
require_once __DIR__ . '/../config/constants.php';
?>
```

---

## ✅ CHECKLIST DE APLICACIÓN

- [ ] FIX #1: Eliminar @ operators
- [ ] FIX #2: Fix lock cleanup
- [ ] FIX #3: Fix date() heredoc
- [ ] FIX #4: Función multiplataforma copy
- [ ] FIX #5: File locking domains.json
- [ ] FIX #6: Crear proxy deploy.php
- [ ] FIX #7: Crear verify-domain.php
- [ ] FIX #8: Crear cleanup-old.php
- [ ] FIX #9: (export-client.php - opcional)
- [ ] FIX #10: Setup .env
- [ ] FIX #11: (ya en #6)
- [ ] FIX #12: Fix .txt conflict
- [ ] FIX #13-17: Integraciones
- [ ] FIX #18: Estandarizar crons
- [ ] FIX #19: Crear constants.php
- [ ] FIX #20: Crear .gitignore
- [ ] FIX #21: Crear test-setup.php

---

**Tiempo estimado:** 7 horas  
**Prioridad:** Hacer en orden (P0 → P1 → P2)  
**Estado:** ⏳ PENDIENTE APLICACIÓN
