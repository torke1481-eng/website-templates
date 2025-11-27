# ===========================================================
# VERIFICACION DE ESTRUCTURA LOCAL (VERSION SIMPLE)
# ===========================================================
# Ejecutar: powershell -ExecutionPolicy Bypass .\verificar-estructura-simple.ps1

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  VERIFICACION DE ESTRUCTURA LOCAL" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

$baseDir = $PSScriptRoot
$errors = 0
$warnings = 0
$checks = 0

# ===========================================================
# 1. CARPETAS PRINCIPALES
# ===========================================================
Write-Host "[1/9] Carpetas principales..." -ForegroundColor Cyan
$checks++

$requiredFolders = @('_system', 'public_html', 'domains', 'staging', 'docs')
$foldersOk = $true

foreach ($folder in $requiredFolders) {
    if (Test-Path (Join-Path $baseDir $folder)) {
        Write-Host "  OK: $folder/" -ForegroundColor Green
    } else {
        Write-Host "  ERROR: Falta $folder/" -ForegroundColor Red
        $errors++
        $foldersOk = $false
    }
}

if ($foldersOk) {
    Write-Host "  OK: Todas las carpetas principales existen" -ForegroundColor Green
}

Write-Host ""

# ===========================================================
# 2. SCRIPTS EN _SYSTEM/GENERATOR
# ===========================================================
Write-Host "[2/9] Scripts en _system/generator..." -ForegroundColor Cyan
$checks++

$generatorDir = Join-Path $baseDir "_system\generator"
$requiredScripts = @(
    'backup-all.php',
    'backup-client.php',
    'cleanup-old.php',
    'create-domain.php',
    'deploy-v4-mejorado.php',
    'health-check.php',
    'verify-domain.php',
    'verify-installation.php'
)

$scriptsOk = $true

if (Test-Path $generatorDir) {
    foreach ($script in $requiredScripts) {
        if (Test-Path (Join-Path $generatorDir $script)) {
            Write-Host "  OK: $script" -ForegroundColor Green
        } else {
            Write-Host "  ERROR: Falta $script" -ForegroundColor Red
            $errors++
            $scriptsOk = $false
        }
    }
} else {
    Write-Host "  ERROR: Carpeta _system/generator no existe" -ForegroundColor Red
    $errors++
    $scriptsOk = $false
}

if ($scriptsOk) {
    Write-Host "  OK: Todos los scripts presentes (8/8)" -ForegroundColor Green
}

Write-Host ""

# ===========================================================
# 3. TEMPLATES
# ===========================================================
Write-Host "[3/9] Templates..." -ForegroundColor Cyan
$checks++

$templatesDir = Join-Path $baseDir "_system\templates"
$requiredTemplates = @('landing-pro', 'landing-basica', 'componentes-globales')
$templatesOk = $true

if (Test-Path $templatesDir) {
    foreach ($template in $requiredTemplates) {
        $path = Join-Path $templatesDir $template
        if (Test-Path $path) {
            Write-Host "  OK: $template/" -ForegroundColor Green
        } else {
            Write-Host "  WARN: Falta $template/" -ForegroundColor Yellow
            $warnings++
            $templatesOk = $false
        }
    }
} else {
    Write-Host "  ERROR: Carpeta _system/templates no existe" -ForegroundColor Red
    $errors++
    $templatesOk = $false
}

if ($templatesOk) {
    Write-Host "  OK: Todos los templates presentes" -ForegroundColor Green
}

Write-Host ""

# ===========================================================
# 4. CONFIGURACION
# ===========================================================
Write-Host "[4/9] Configuracion..." -ForegroundColor Cyan
$checks++

$configDir = Join-Path $baseDir "_system\config"
$domainsJson = Join-Path $configDir "domains.json"
$envExample = Join-Path $configDir ".env.example"
$configOk = $true

if (-not (Test-Path $domainsJson)) {
    Write-Host "  ERROR: Falta domains.json" -ForegroundColor Red
    $errors++
    $configOk = $false
}

if (-not (Test-Path $envExample)) {
    Write-Host "  WARN: Falta .env.example en config" -ForegroundColor Yellow
    $warnings++
}

if ($configOk) {
    Write-Host "  OK: Configuracion correcta" -ForegroundColor Green
}

Write-Host ""

# ===========================================================
# 5. PROXY DEPLOY.PHP
# ===========================================================
Write-Host "[5/9] Proxy deploy.php..." -ForegroundColor Cyan
$checks++

$deployProxy = Join-Path $baseDir "public_html\generator\deploy.php"
$proxyOk = $true

if (Test-Path $deployProxy) {
    Write-Host "  OK: deploy.php existe" -ForegroundColor Green
} else {
    Write-Host "  ERROR: Falta deploy.php en public_html/generator/" -ForegroundColor Red
    $errors++
    $proxyOk = $false
}

Write-Host ""

# ===========================================================
# 6. ARCHIVOS RAIZ
# ===========================================================
Write-Host "[6/9] Archivos raiz..." -ForegroundColor Cyan
$checks++

$rootFiles = @('.gitignore', 'README.md', '.env.example')
$rootOk = $true

foreach ($file in $rootFiles) {
    if (Test-Path (Join-Path $baseDir $file)) {
        Write-Host "  OK: $file" -ForegroundColor Green
    } else {
        Write-Host "  WARN: Falta $file" -ForegroundColor Yellow
        $warnings++
        $rootOk = $false
    }
}

if ($rootOk) {
    Write-Host "  OK: Archivos raiz presentes" -ForegroundColor Green
}

Write-Host ""

# ===========================================================
# 7. GIT
# ===========================================================
Write-Host "[7/9] Repositorio Git..." -ForegroundColor Cyan
$checks++

if (Test-Path (Join-Path $baseDir ".git")) {
    Write-Host "  OK: Repositorio Git presente" -ForegroundColor Green
} else {
    Write-Host "  WARN: No hay repositorio Git" -ForegroundColor Yellow
    $warnings++
}

Write-Host ""

# ===========================================================
# 8. LIMPIEZA
# ===========================================================
Write-Host "[8/9] Verificando limpieza..." -ForegroundColor Cyan
$checks++

$oldFiles = @(
    'generator\deploy-v3.php',
    'generator\deploy-v2.php',
    'templates\landing-pro\index.html'
)

$cleanOk = $true
foreach ($file in $oldFiles) {
    if (Test-Path (Join-Path $baseDir $file)) {
        Write-Host "  WARN: Archivo viejo encontrado: $file" -ForegroundColor Yellow
        $warnings++
        $cleanOk = $false
    }
}

if ($cleanOk) {
    Write-Host "  OK: No hay archivos viejos" -ForegroundColor Green
}

Write-Host ""

# ===========================================================
# 9. TAMANO DEL SISTEMA
# ===========================================================
Write-Host "[9/9] Tamano del sistema..." -ForegroundColor Cyan
$checks++

$systemDir = Join-Path $baseDir "_system"
if (Test-Path $systemDir) {
    $size = 0
    Get-ChildItem -Path $systemDir -Recurse -File -ErrorAction SilentlyContinue | ForEach-Object {
        $size += $_.Length
    }
    
    $systemSizeMB = [math]::Round($size / 1MB, 2)
    Write-Host "  INFO: Tamano de _system: $systemSizeMB MB" -ForegroundColor Cyan
    
    if ($systemSizeMB -gt 100) {
        Write-Host "  WARN: _system/ es muy grande (>100 MB)" -ForegroundColor Yellow
        $warnings++
    }
}

Write-Host ""

# ===========================================================
# RESUMEN
# ===========================================================
Write-Host "============================================================" -ForegroundColor White
Write-Host "  RESUMEN" -ForegroundColor White
Write-Host "============================================================" -ForegroundColor White
Write-Host ""

Write-Host "Checks realizados:  $checks" -ForegroundColor White
Write-Host "OK: Correctos:      $($checks - $errors - $warnings)" -ForegroundColor Green
Write-Host "WARN: Advertencias: $warnings" -ForegroundColor Yellow
Write-Host "ERROR: Errores:     $errors" -ForegroundColor Red
Write-Host ""

$score = [math]::Round((($checks - $errors) / $checks) * 100)
Write-Host "SCORE:              $score/100" -ForegroundColor Cyan
Write-Host ""

# ===========================================================
# RESULTADO FINAL
# ===========================================================

if ($errors -eq 0 -and $warnings -eq 0) {
    Write-Host "============================================================" -ForegroundColor Green
    Write-Host "  OK: ESTRUCTURA PERFECTA - LISTA PARA SUBIR" -ForegroundColor Green
    Write-Host "============================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Puedes proceder con la migracion a Hostinger" -ForegroundColor Green
    Write-Host ""
    Write-Host "Proximos pasos:" -ForegroundColor Yellow
    Write-Host "  1. Configurar .env con valores reales" -ForegroundColor White
    Write-Host "  2. Abrir FileZilla" -ForegroundColor White
    Write-Host "  3. Subir _system/ -> /home/u123456789/_system/" -ForegroundColor White
    Write-Host "  4. Subir public_html/ -> /home/u123456789/public_html/" -ForegroundColor White
    Write-Host "  5. Seguir GUIA_MIGRACION_HOSTINGER.md" -ForegroundColor White
    Write-Host ""
    exit 0
    
} elseif ($errors -eq 0) {
    Write-Host "============================================================" -ForegroundColor Yellow
    Write-Host "  WARN: ESTRUCTURA OK CON ADVERTENCIAS" -ForegroundColor Yellow
    Write-Host "============================================================" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "La estructura es funcional pero revisa las advertencias." -ForegroundColor Yellow
    Write-Host ""
    exit 0
    
} else {
    Write-Host "============================================================" -ForegroundColor Red
    Write-Host "  ERROR: ESTRUCTURA CON ERRORES" -ForegroundColor Red
    Write-Host "============================================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Hay $errors error(es) que deben corregirse." -ForegroundColor Red
    Write-Host ""
    Write-Host "Soluciones:" -ForegroundColor Yellow
    Write-Host "  1. Ejecutar: recuperar-archivos.ps1" -ForegroundColor White
    Write-Host "  2. Verificar que los archivos se copiaron" -ForegroundColor White
    Write-Host "  3. Ejecutar este script de nuevo" -ForegroundColor White
    Write-Host ""
    exit 1
}
