# ======================================================================
# VERIFICACIÓN DE ESTRUCTURA LOCAL
# ======================================================================
# Verifica que la estructura local esté correcta antes de subir
# Ejecutar: powershell -ExecutionPolicy Bypass .\verificar-estructura.ps1

Write-Host ""
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "  VERIFICACION DE ESTRUCTURA LOCAL" -ForegroundColor Cyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""

$baseDir = $PSScriptRoot
$errors = 0
$warnings = 0
$checks = 0

# ======================================================================
# 1. CARPETAS PRINCIPALES
# ======================================================================
Write-Host "[1/8] Verificando carpetas principales..." -ForegroundColor Cyan
$checks++

$requiredFolders = @(
    "_system",
    "_system\generator",
    "_system\templates",
    "_system\config",
    "_system\logs",
    "_system\queue",
    "public_html",
    "public_html\generator",
    "domains",
    "staging"
)

$foldersOk = $true
foreach ($folder in $requiredFolders) {
    $path = Join-Path $baseDir $folder
    if (-not (Test-Path $path)) {
        if ($foldersOk) { Write-Host "" }
        Write-Host "  ❌ Falta: $folder" -ForegroundColor Red
        $errors++
        $foldersOk = $false
    }
}

if ($foldersOk) {
    Write-Host "  ✅ Todas las carpetas principales existen" -ForegroundColor Green
}

Write-Host ""

# ======================================================================
# 2. SCRIPTS EN _system/generator
# ======================================================================
Write-Host "[2/8] Verificando scripts en _system/generator..." -ForegroundColor Cyan
$checks++

$requiredScripts = @(
    "create-domain.php",
    "backup-client.php",
    "backup-all.php",
    "health-check.php",
    "verify-domain.php",
    "cleanup-old.php",
    "verify-installation.php"
)

$scriptsOk = $true
foreach ($script in $requiredScripts) {
    $path = Join-Path $baseDir "_system\generator\$script"
    if (-not (Test-Path $path)) {
        if ($scriptsOk) { Write-Host "" }
        Write-Host "  ❌ Falta: $script" -ForegroundColor Red
        $errors++
        $scriptsOk = $false
    }
}

if ($scriptsOk) {
    Write-Host "  ✅ Todos los scripts presentes" -ForegroundColor Green
}

Write-Host ""

# ======================================================================
# 3. TEMPLATES
# ======================================================================
Write-Host "[3/8] Verificando templates..." -ForegroundColor Cyan
$checks++

$requiredTemplates = @(
    "_system\templates\landing-pro",
    "_system\templates\landing-basica",
    "_system\templates\componentes-globales"
)

$templatesOk = $true
foreach ($template in $requiredTemplates) {
    $path = Join-Path $baseDir $template
    if (-not (Test-Path $path)) {
        if ($templatesOk) { Write-Host "" }
        Write-Host "  ❌ Falta: $template" -ForegroundColor Red
        $errors++
        $templatesOk = $false
    } else {
        # Verificar que tenga archivos
        $files = Get-ChildItem -Path $path -Recurse -File
        if ($files.Count -eq 0) {
            if ($templatesOk) { Write-Host "" }
            Write-Host "  ⚠️  $template está vacío" -ForegroundColor Yellow
            $warnings++
            $templatesOk = $false
        }
    }
}

if ($templatesOk) {
    Write-Host "  ✅ Todos los templates presentes" -ForegroundColor Green
}

Write-Host ""

# ======================================================================
# 4. ARCHIVOS DE CONFIGURACIÓN
# ======================================================================
Write-Host "[4/8] Verificando configuración..." -ForegroundColor Cyan
$checks++

$configOk = $true

# .env.example debe existir
$envExample = Join-Path $baseDir "_system\config\.env.example"
if (-not (Test-Path $envExample)) {
    Write-Host "  ❌ Falta: .env.example" -ForegroundColor Red
    $errors++
    $configOk = $false
}

# .env (si existe, verificar que tenga MAKE_SECRET)
$env = Join-Path $baseDir "_system\config\.env"
if (Test-Path $env) {
    $content = Get-Content $env -Raw
    if ($content -notmatch "MAKE_SECRET=") {
        Write-Host "  ⚠️  .env no tiene MAKE_SECRET configurado" -ForegroundColor Yellow
        $warnings++
        $configOk = $false
    }
} else {
    Write-Host "  ⚠️  .env no existe (copiar de .env.example)" -ForegroundColor Yellow
    $warnings++
    $configOk = $false
}

# domains.json debe existir
$domainsJson = Join-Path $baseDir "_system\config\domains.json"
if (-not (Test-Path $domainsJson)) {
    Write-Host "  ❌ Falta: domains.json" -ForegroundColor Red
    $errors++
    $configOk = $false
} else {
    # Verificar que sea JSON válido
    try {
        $domains = Get-Content $domainsJson | ConvertFrom-Json
        if (-not ($domains -is [array])) {
            Write-Host "  ⚠️  domains.json no es un array" -ForegroundColor Yellow
            $warnings++
            $configOk = $false
        }
    } catch {
        Write-Host "  ❌ domains.json tiene JSON inválido" -ForegroundColor Red
        $errors++
        $configOk = $false
    }
}

if ($configOk) {
    Write-Host "  ✅ Configuración correcta" -ForegroundColor Green
}

Write-Host ""

# ======================================================================
# 5. PROXY DEPLOY.PHP
# ======================================================================
Write-Host "[5/8] Verificando proxy deploy.php..." -ForegroundColor Cyan
$checks++

$deployProxy = Join-Path $baseDir "public_html\generator\deploy.php"
if (-not (Test-Path $deployProxy)) {
    Write-Host "  ❌ Falta: public_html\generator\deploy.php" -ForegroundColor Red
    $errors++
} else {
    $content = Get-Content $deployProxy -Raw
    
    $proxyOk = $true
    
    # Verificar que valide token
    if ($content -notmatch "X-Make-Secret") {
        Write-Host "  ⚠️  deploy.php no valida X-Make-Secret" -ForegroundColor Yellow
        $warnings++
        $proxyOk = $false
    }
    
    # Verificar que incluya script real
    if ($content -notmatch "deploy-v4-mejorado\.php") {
        Write-Host "  ⚠️  deploy.php no incluye deploy-v4-mejorado.php" -ForegroundColor Yellow
        $warnings++
        $proxyOk = $false
    }
    
    if ($proxyOk) {
        Write-Host "  ✅ Proxy deploy.php correcto" -ForegroundColor Green
    }
}

Write-Host ""

# ======================================================================
# 6. ARCHIVOS RAÍZ
# ======================================================================
Write-Host "[6/8] Verificando archivos raíz..." -ForegroundColor Cyan
$checks++

$rootFiles = @(
    "README.md",
    ".gitignore",
    ".env.example"
)

$rootOk = $true
foreach ($file in $rootFiles) {
    $path = Join-Path $baseDir $file
    if (-not (Test-Path $path)) {
        if ($rootOk) { Write-Host "" }
        Write-Host "  ⚠️  Falta: $file" -ForegroundColor Yellow
        $warnings++
        $rootOk = $false
    }
}

if ($rootOk) {
    Write-Host "  ✅ Archivos raíz presentes" -ForegroundColor Green
}

Write-Host ""

# ======================================================================
# 7. GITIGNORE
# ======================================================================
Write-Host "[7/8] Verificando .gitignore..." -ForegroundColor Cyan
$checks++

$gitignore = Join-Path $baseDir ".gitignore"
if (Test-Path $gitignore) {
    $content = Get-Content $gitignore -Raw
    
    $gitignoreOk = $true
    $requiredPatterns = @(".env", "/domains/", "/staging/", "*.tar.gz")
    
    foreach ($pattern in $requiredPatterns) {
        if ($content -notmatch [regex]::Escape($pattern)) {
            if ($gitignoreOk) { Write-Host "" }
            Write-Host "  ⚠️  .gitignore no bloquea: $pattern" -ForegroundColor Yellow
            $warnings++
            $gitignoreOk = $false
        }
    }
    
    if ($gitignoreOk) {
        Write-Host "  ✅ .gitignore correcto" -ForegroundColor Green
    }
} else {
    Write-Host "  ⚠️  .gitignore no existe" -ForegroundColor Yellow
    $warnings++
}

Write-Host ""

# ======================================================================
# 8. TAMAÑO TOTAL
# ======================================================================
Write-Host "[8/8] Calculando tamaño..." -ForegroundColor Cyan
$checks++

$systemSize = 0
if (Test-Path (Join-Path $baseDir "_system")) {
    $systemSize = (Get-ChildItem (Join-Path $baseDir "_system") -Recurse -File | Measure-Object -Property Length -Sum).Sum
}

$systemSizeMB = [math]::Round($systemSize / 1MB, 2)

if ($systemSizeMB -gt 0) {
    Write-Host "  ℹ️  Tamaño _system/: $systemSizeMB MB" -ForegroundColor Cyan
    
    if ($systemSizeMB > 100) {
        Write-Host "  ⚠️  _system/ es muy grande (>100 MB)" -ForegroundColor Yellow
        $warnings++
    }
}

Write-Host ""

# ======================================================================
# RESUMEN
# ======================================================================
Write-Host "================================================================" -ForegroundColor White
Write-Host "  RESUMEN" -ForegroundColor White
Write-Host "================================================================" -ForegroundColor White
Write-Host ""

Write-Host "Checks realizados:  $checks" -ForegroundColor White
Write-Host "OK: Correctos:      $($checks - $errors - $warnings)" -ForegroundColor Green
Write-Host "WARN: Advertencias: $warnings" -ForegroundColor Yellow
Write-Host "ERROR: Errores:     $errors" -ForegroundColor Red
Write-Host ""

if ($errors -eq 0 -and $warnings -eq 0) {
    Write-Host "================================================================" -ForegroundColor Green
    Write-Host "  OK: ESTRUCTURA PERFECTA - LISTA PARA SUBIR" -ForegroundColor Green
    Write-Host "================================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "OK: Puedes proceder con la migracion a Hostinger" -ForegroundColor Green
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
    Write-Host "================================================================" -ForegroundColor Yellow
    Write-Host "  WARN: ESTRUCTURA OK CON ADVERTENCIAS" -ForegroundColor Yellow
    Write-Host "================================================================" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "La estructura es funcional pero revisa las advertencias." -ForegroundColor Yellow
    Write-Host ""
    exit 0
    
} else {
    Write-Host "================================================================" -ForegroundColor Red
    Write-Host "  ERROR: ESTRUCTURA CON ERRORES" -ForegroundColor Red
    Write-Host "================================================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "WARN: Hay $errors error(es) que deben corregirse." -ForegroundColor Red
    Write-Host ""
    Write-Host "Soluciones:" -ForegroundColor Yellow
    Write-Host "  1. Ejecutar: .\reorganizar-estructura.ps1" -ForegroundColor White
    Write-Host "  2. Verificar que los archivos se copiaron" -ForegroundColor White
    Write-Host "  3. Ejecutar este script de nuevo" -ForegroundColor White
    Write-Host ""
    exit 1
}
