<?php
/**
 * CREATE-DOMAIN.PHP
 * 
 * Crea estructura completa para un nuevo dominio de cliente
 * Prepara todo para agregar en cPanel como Addon Domain
 * 
 * Uso: php create-domain.php dominio.com slug-staging
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función de logging
function logAction($message, $context = []) {
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $entry = [
        'timestamp' => date('c'),
        'message' => $message,
        'context' => $context
    ];
    
    file_put_contents(
        $logDir . '/domain-creation.log',
        json_encode($entry) . "\n",
        FILE_APPEND
    );
}

// Sanitizar dominio
function sanitizeDomain($domain) {
    $domain = strtolower(trim($domain));
    $domain = preg_replace('/[^a-z0-9.-]/', '', $domain);
    
    if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain)) {
        throw new Exception('Dominio inválido. Formato: ejemplo.com');
    }
    
    return $domain;
}

// Lock de slug para evitar duplicados
function acquireLock($slug, $timeout = 30) {
    $lockFile = sys_get_temp_dir() . "/slug-{$slug}.lock";
    $start = time();
    
    while (file_exists($lockFile)) {
        if (time() - $start > $timeout) {
            throw new Exception("El slug '$slug' está siendo procesado. Intenta más tarde.");
        }
        usleep(500000); // 0.5s
    }
    
    touch($lockFile);
    
    register_shutdown_function(function() use ($lockFile) {
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
    });
}

// Copia recursiva multiplataforma (Windows + Linux)
function copyRecursive($source, $dest) {
    if (!file_exists($source)) {
        throw new Exception("No existe: $source");
    }
    
    // Si es archivo, copiar directamente
    if (is_file($source)) {
        $dir = dirname($dest);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        return copy($source, $dest);
    }
    
    // Es directorio, copiar recursivamente
    if (!file_exists($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $dir = opendir($source);
    if ($dir === false) {
        throw new Exception("No se pudo abrir: $source");
    }
    
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $srcPath = $source . '/' . $file;
        $destPath = $dest . '/' . $file;
        
        if (is_dir($srcPath)) {
            copyRecursive($srcPath, $destPath);
        } else {
            copy($srcPath, $destPath);
        }
    }
    
    closedir($dir);
    return true;
}

// Agrega dominio a config con file locking (previene race conditions)
function addDomainToConfigSafe($domain, $path, $status) {
    $configDir = dirname(__DIR__) . '/config';
    if (!file_exists($configDir)) {
        mkdir($configDir, 0755, true);
    }
    
    $configFile = $configDir . '/domains.json';
    $lockFile = $configFile . '.lock';
    
    // Adquirir lock exclusivo
    $fp = fopen($lockFile, 'c');
    if ($fp === false) {
        throw new Exception('No se pudo crear lock file');
    }
    
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        throw new Exception('No se pudo adquirir lock de config');
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
        
        // Verificar duplicado
        foreach ($domains as $existing) {
            if ($existing['domain'] === $domain) {
                throw new Exception("Dominio '$domain' ya existe en config");
            }
        }
        
        // Agregar nuevo
        $domains[] = [
            'domain' => $domain,
            'path' => $path,
            'status' => $status,
            'created' => date('c')
        ];
        
        // Guardar atómicamente
        $json = json_encode($domains, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($configFile, $json) === false) {
            throw new Exception('No se pudo escribir config');
        }
        
        return true;
        
    } finally {
        // Siempre liberar lock
        flock($fp, LOCK_UN);
        fclose($fp);
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
    }
}

// Main function
function createDomainStructure($domain, $stagingSlug = null) {
    // Validar dominio
    $domain = sanitizeDomain($domain);
    
    logAction('Iniciando creación de dominio', ['domain' => $domain]);
    
    // Adquirir lock
    acquireLock($domain);
    
    // Paths
    $baseDir = dirname(dirname(__DIR__)); // /home/u123456
    $domainDir = $baseDir . '/domains/' . $domain;
    $stagingDir = $baseDir . '/staging/' . ($stagingSlug ?: 'temp');
    
    // Verificar que no exista ya
    if (file_exists($domainDir)) {
        throw new Exception("El dominio '$domain' ya existe");
    }
    
    // Crear estructura de carpetas
    echo "📁 Creando estructura de carpetas...\n";
    
    $folders = [
        $domainDir,
        $domainDir . '/public_html',
        $domainDir . '/public_html/css',
        $domainDir . '/public_html/js',
        $domainDir . '/public_html/images',
        $domainDir . '/logs',
        $domainDir . '/backups'
    ];
    
    foreach ($folders as $folder) {
        if (!mkdir($folder, 0755, true)) {
            throw new Exception("No se pudo crear: $folder");
        }
        echo "  ✅ $folder\n";
    }
    
    // Copiar sitio desde staging si existe
    if ($stagingSlug && file_exists($stagingDir)) {
        echo "📦 Copiando sitio desde staging...\n";
        
        $files = glob($stagingDir . '/*');
        foreach ($files as $file) {
            $basename = basename($file);
            $dest = $domainDir . '/public_html/' . $basename;
            
            copyRecursive($file, $dest);
            
            echo "  ✅ Copiado: $basename\n";
        }
    } else {
        // Crear HTML básico
        echo "📄 Creando página placeholder...\n";
        
        $placeholder = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$domain - Configurando...</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }
        .container {
            padding: 2rem;
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Sitio en Configuración</h1>
        <p>Tu sitio <strong>$domain</strong> está siendo configurado.</p>
        <p>Estará listo en breve.</p>
    </div>
</body>
</html>
HTML;
        
        file_put_contents($domainDir . '/public_html/index.html', $placeholder);
        echo "  ✅ index.html placeholder creado\n";
    }
    
    // Crear .htaccess seguro
    echo "🔒 Creando .htaccess de seguridad...\n";
    
    $generatedDate = date('Y-m-d H:i:s');
    $htaccess = <<<HTACCESS
# Generado automáticamente - $domain
# Fecha: $generatedDate

# Bloquear directory listing
Options -Indexes

# Rewrite engine
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/\$1 [L,R=301]
    
    # Force www o sin www (elegir uno)
    # RewriteCond %{HTTP_HOST} !^www\. [NC]
    # RewriteRule ^(.*)$ https://www.%{HTTP_HOST}/\$1 [L,R=301]
</IfModule>

# Bloquear acceso a archivos sensibles
<FilesMatch "\.(json|log|bak|sql|md|txt)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security headers
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>

# Compresión GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css
    AddOutputFilterByType DEFLATE application/javascript application/json
</IfModule>
HTACCESS;
    
    file_put_contents($domainDir . '/public_html/.htaccess', $htaccess);
    echo "  ✅ .htaccess creado\n";
    
    // Crear metadata
    echo "📋 Creando metadata...\n";
    
    $metadata = [
        'domain' => $domain,
        'created_at' => date('c'),
        'status' => 'pending_cpanel',
        'dns_status' => 'pending',
        'ssl_enabled' => false,
        'staging_slug' => $stagingSlug,
        'structure_version' => '2.0'
    ];
    
    file_put_contents(
        $domainDir . '/.metadata.json',
        json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
    echo "  ✅ .metadata.json creado\n";
    
    // Registrar en sistema (con file locking)
    echo "📝 Registrando en sistema...\n";
    
    addDomainToConfigSafe(
        $domain,
        $domainDir . '/public_html',
        'pending_cpanel'
    );
    
    echo "  ✅ Registrado en domains.json\n";
    
    // Generar instrucciones para cPanel
    echo "📖 Generando instrucciones...\n";
    
    $userName = basename(dirname($baseDir)); // u123456
    
    $instructions = <<<INSTRUCTIONS
╔══════════════════════════════════════════════════════════════════════╗
║  📋 INSTRUCCIONES CPANEL PARA: $domain
╚══════════════════════════════════════════════════════════════════════╝

✅ ESTRUCTURA CREADA CORRECTAMENTE

📂 Ubicación:
   $domainDir/public_html/

🔧 PASOS EN CPANEL:

1. Acceder a cPanel → Dominios → Addon Domains

2. Completar el formulario:
   ┌────────────────────────────────────────────────────┐
   │ New Domain Name:                                   │
   │ ► $domain
   │                                                    │
   │ Subdomain: (dejar vacío o auto)                   │
   │ ► (automático)                                     │
   │                                                    │
   │ Document Root:                                     │
   │ ► $domainDir/public_html
   │                                                    │
   └────────────────────────────────────────────────────┘

3. Click en "Add Domain"

4. ⏳ Esperar 2-5 minutos

5. Verificar en cPanel → Dominios
   ✅ $domain debe aparecer en la lista

6. Configurar SSL:
   - cPanel → Security → SSL/TLS Status
   - Buscar: $domain
   - Click "Run AutoSSL"
   - ⏳ Esperar 2-10 minutos

7. Verificar sitio:
   - Abrir: https://$domain
   - Debe mostrar el sitio (o placeholder)

8. Configurar DNS (si cliente tiene dominio externo):
   ┌────────────────────────────────────────────────────┐
   │ En el proveedor DNS del cliente (GoDaddy, etc):    │
   │                                                    │
   │ Tipo   Nombre   Valor                    TTL      │
   │ A      @        [IP DE HOSTINGER]        3600     │
   │ A      www      [IP DE HOSTINGER]        3600     │
   │                                                    │
   │ IP Hostinger: (verificar en cPanel)               │
   └────────────────────────────────────────────────────┘

9. Actualizar estado:
   ```bash
   php /_system/generator/verify-domain.php $domain
   ```

═══════════════════════════════════════════════════════════════════════

📞 SOPORTE:
   Si hay problemas, revisar logs en:
   /_system/logs/domain-creation.log

INSTRUCTIONS;
    
    $instructionsFile = $domainDir . '/CPANEL_INSTRUCTIONS.txt';
    file_put_contents($instructionsFile, $instructions);
    echo "  ✅ Instrucciones guardadas en: $instructionsFile\n";
    
    // Log de éxito
    logAction('Dominio creado exitosamente', [
        'domain' => $domain,
        'path' => $domainDir,
        'staging_slug' => $stagingSlug
    ]);
    
    // Resultado final
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ DOMINIO CREADO EXITOSAMENTE                                  ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "📦 Dominio: $domain\n";
    echo "📂 Path: $domainDir/public_html/\n";
    echo "📄 Instrucciones: $instructionsFile\n";
    echo "\n";
    echo "🔧 PRÓXIMOS PASOS:\n";
    echo "   1. Agregar dominio en cPanel (ver instrucciones)\n";
    echo "   2. Configurar DNS\n";
    echo "   3. Verificar con: php verify-domain.php $domain\n";
    echo "\n";
    
    return [
        'success' => true,
        'domain' => $domain,
        'path' => $domainDir,
        'instructions_file' => $instructionsFile
    ];
}

// CLI Execution
if (php_sapi_name() === 'cli') {
    if ($argc < 2) {
        echo "Uso: php create-domain.php dominio.com [slug-staging]\n";
        echo "\n";
        echo "Ejemplos:\n";
        echo "  php create-domain.php clientenegocio.com\n";
        echo "  php create-domain.php clientenegocio.com cliente-preview-abc123\n";
        exit(1);
    }
    
    $domain = $argv[1];
    $stagingSlug = $argv[2] ?? null;
    
    try {
        createDomainStructure($domain, $stagingSlug);
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
        logAction('Error al crear dominio', [
            'domain' => $domain,
            'error' => $e->getMessage()
        ]);
        exit(1);
    }
}
?>
