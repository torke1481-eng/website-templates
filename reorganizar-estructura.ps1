# ======================================================================
# SCRIPT DE REORGANIZACIÓN - ESTRUCTURA HOSTINGER LOCAL
# ======================================================================
# Reorganiza tu carpeta local para que sea EXACTA a Hostinger
# Ejecutar con: powershell -ExecutionPolicy Bypass .\reorganizar-estructura.ps1

Write-Host ""
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "  REORGANIZANDO ESTRUCTURA LOCAL -> HOSTINGER" -ForegroundColor Cyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""

$baseDir = $PSScriptRoot
Write-Host "Directorio base: $baseDir" -ForegroundColor Yellow
Write-Host ""

# Confirmar
$confirm = Read-Host "Reorganizar estructura? (S/N)"
if ($confirm -ne "S" -and $confirm -ne "s") {
    Write-Host "Cancelado" -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "Iniciando reorganizacion..." -ForegroundColor Green
Write-Host ""

# ======================================================================
# FASE 1: CREAR NUEVA ESTRUCTURA
# ======================================================================
Write-Host "[1/6] Creando nueva estructura..." -ForegroundColor Cyan

$newDirs = @(
    "_system_nuevo",
    "_system_nuevo\generator",
    "_system_nuevo\templates",
    "_system_nuevo\config",
    "_system_nuevo\logs",
    "_system_nuevo\logs\errors",
    "_system_nuevo\logs\health",
    "_system_nuevo\queue",
    "public_html_nuevo",
    "public_html_nuevo\generator",
    "domains",
    "staging",
    "docs"
)

foreach ($dir in $newDirs) {
    $fullPath = Join-Path $baseDir $dir
    if (-not (Test-Path $fullPath)) {
        New-Item -ItemType Directory -Path $fullPath -Force | Out-Null
        Write-Host "  [OK] Creado: $dir" -ForegroundColor Green
    }
}

Write-Host ""

# ======================================================================
# FASE 2: MOVER SCRIPTS A _system/generator
# ======================================================================
Write-Host "[2/6] Moviendo scripts a _system/generator..." -ForegroundColor Cyan

# Scripts que van a _system/generator
$generatorScripts = @(
    "create-domain.php",
    "backup-client.php",
    "backup-all.php",
    "health-check.php",
    "verify-domain.php",
    "cleanup-old.php",
    "deploy-v4-mejorado.php",
    "verify-installation.php"
)

foreach ($script in $generatorScripts) {
    $source = Join-Path $baseDir "_system\generator\$script"
    if (Test-Path $source) {
        $dest = Join-Path $baseDir "_system_nuevo\generator\$script"
        Copy-Item $source $dest -Force
        Write-Host "  [OK] Copiado: $script" -ForegroundColor Green
    }
}

Write-Host ""

# ======================================================================
# FASE 3: MOVER TEMPLATES A _system/templates
# ======================================================================
Write-Host "[3/6] Moviendo templates a _system/templates..." -ForegroundColor Cyan

$templatesSource = Join-Path $baseDir "templates"
$templatesDest = Join-Path $baseDir "_system_nuevo\templates"

if (Test-Path $templatesSource) {
    Copy-Item "$templatesSource\*" $templatesDest -Recurse -Force
    Write-Host "  [OK] Templates copiados" -ForegroundColor Green
} else {
    Write-Host "  [WARN] Carpeta templates no encontrada" -ForegroundColor Yellow
}

Write-Host ""

# ======================================================================
# FASE 4: CONFIGURAR _system/config
# ======================================================================
Write-Host "[4/6] Configurando _system/config..." -ForegroundColor Cyan

# Copiar .env.example
$envExample = Join-Path $baseDir ".env.example"
if (Test-Path $envExample) {
    Copy-Item $envExample (Join-Path $baseDir "_system_nuevo\config\.env.example") -Force
    Write-Host "  [OK] .env.example copiado" -ForegroundColor Green
}

# Crear domains.json vacío
$domainsJson = Join-Path $baseDir "_system_nuevo\config\domains.json"
"[]" | Out-File -FilePath $domainsJson -Encoding UTF8
Write-Host "  [OK] domains.json creado" -ForegroundColor Green

Write-Host ""

# ======================================================================
# FASE 5: ORGANIZAR public_html
# ======================================================================
Write-Host "[5/6] Organizando public_html..." -ForegroundColor Cyan

# Archivos del sitio principal que van a public_html
$publicFiles = @(
    "index.html",
    "styles.css",
    "script.js"
)

foreach ($file in $publicFiles) {
    $source = Join-Path $baseDir $file
    if (Test-Path $source) {
        $dest = Join-Path $baseDir "public_html_nuevo\$file"
        Copy-Item $source $dest -Force
        Write-Host "  [OK] Copiado: $file" -ForegroundColor Green
    }
}

# Crear proxy deploy.php
$proxyDest = Join-Path $baseDir "public_html_nuevo\generator"
$proxyContent = @'
<?php
/**
 * PROXY SEGURO PARA MAKE.COM
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Validar método
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
'@

$proxyFile = Join-Path $proxyDest "deploy.php"
$proxyContent | Out-File -FilePath $proxyFile -Encoding UTF8
Write-Host "  [OK] deploy.php (proxy) creado" -ForegroundColor Green

Write-Host ""

# ======================================================================
# FASE 6: ORGANIZAR DOCUMENTACIÓN
# ======================================================================
Write-Host "[6/6] Organizando documentación..." -ForegroundColor Cyan

# Mover todas las auditorías y docs a /docs
$docsPattern = @("AUDITORIA_*.md", "GUIA_*.md", "RESUMEN_*.md", "ESTRUCTURA_*.md", "QUE_FALTA_*.md", "CHECKLIST_*.md")

$docsCount = 0
foreach ($pattern in $docsPattern) {
    $files = Get-ChildItem -Path $baseDir -Filter $pattern -File
    foreach ($file in $files) {
        $dest = Join-Path $baseDir "docs\$($file.Name)"
        Copy-Item $file.FullName $dest -Force
        $docsCount++
    }
}

# Copiar README.md a docs también (mantener uno en raíz)
$readme = Join-Path $baseDir "README.md"
if (Test-Path $readme) {
    Copy-Item $readme (Join-Path $baseDir "docs\README.md") -Force
    $docsCount++
}

Write-Host "  [OK] $docsCount documentos organizados en /docs" -ForegroundColor Green

Write-Host ""

# ======================================================================
# RESULTADO FINAL
# ======================================================================
Write-Host "================================================================" -ForegroundColor Green
Write-Host "  REORGANIZACION COMPLETADA" -ForegroundColor Green
Write-Host "================================================================" -ForegroundColor Green
Write-Host ""

Write-Host "NUEVA ESTRUCTURA CREADA:" -ForegroundColor Yellow
Write-Host ""
Write-Host "  _system_nuevo/" -ForegroundColor Cyan
Write-Host "    - generator/          (scripts PHP)" -ForegroundColor Gray
Write-Host "    - templates/          (templates base)" -ForegroundColor Gray
Write-Host "    - config/             (.env, domains.json)" -ForegroundColor Gray
Write-Host "    - logs/               (logs del sistema)" -ForegroundColor Gray
Write-Host "    - queue/              (cola async)" -ForegroundColor Gray
Write-Host ""
Write-Host "  public_html_nuevo/" -ForegroundColor Cyan
Write-Host "    - index.html" -ForegroundColor Gray
Write-Host "    - styles.css" -ForegroundColor Gray
Write-Host "    - script.js" -ForegroundColor Gray
Write-Host "    - generator/" -ForegroundColor Gray
Write-Host "        - deploy.php      (proxy seguro)" -ForegroundColor Gray
Write-Host ""
Write-Host "  domains/                  (sitios clientes)" -ForegroundColor Cyan
Write-Host "  staging/                  (previews)" -ForegroundColor Cyan
Write-Host "  docs/                     (documentacion)" -ForegroundColor Cyan
Write-Host ""

Write-Host "IMPORTANTE:" -ForegroundColor Yellow
Write-Host "  1. Revisa la nueva estructura" -ForegroundColor White
Write-Host "  2. Si todo esta OK, elimina las carpetas viejas" -ForegroundColor White
Write-Host "  3. Renombra _system_nuevo a _system" -ForegroundColor White
Write-Host "  4. Renombra public_html_nuevo a public_html" -ForegroundColor White
Write-Host ""

Write-Host "Proximos pasos:" -ForegroundColor Yellow
Write-Host "  1. cd _system_nuevo\config" -ForegroundColor White
Write-Host "  2. copy .env.example .env" -ForegroundColor White
Write-Host "  3. Editar .env con tus valores reales" -ForegroundColor White
Write-Host ""

Write-Host "Listo para subir a Hostinger con FileZilla" -ForegroundColor Green
Write-Host ""
