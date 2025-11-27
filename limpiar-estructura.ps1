# ======================================================================
# SCRIPT DE LIMPIEZA - ELIMINAR VIEJO, ACTIVAR NUEVO
# ======================================================================
# Elimina estructura vieja y activa la nueva
# IMPORTANTE: Hace backup automático antes de eliminar
# Ejecutar: powershell -ExecutionPolicy Bypass .\limpiar-estructura.ps1

Write-Host ""
Write-Host "================================================================" -ForegroundColor Red
Write-Host "  LIMPIEZA DE ESTRUCTURA - ELIMINAR VIEJO" -ForegroundColor Red
Write-Host "================================================================" -ForegroundColor Red
Write-Host ""
Write-Host "ADVERTENCIA: Este script eliminara archivos de forma PERMANENTE" -ForegroundColor Yellow
Write-Host ""

$baseDir = $PSScriptRoot
Write-Host "Directorio: $baseDir" -ForegroundColor Cyan
Write-Host ""

# ======================================================================
# VERIFICACIÓN PREVIA
# ======================================================================
Write-Host "[Verificacion] Comprobando que existe estructura nueva..." -ForegroundColor Cyan

$requiredNew = @(
    "_system_nuevo",
    "_system_nuevo\generator",
    "_system_nuevo\templates",
    "_system_nuevo\config",
    "public_html_nuevo",
    "public_html_nuevo\generator",
    "docs"
)

$allExist = $true
foreach ($dir in $requiredNew) {
    if (-not (Test-Path (Join-Path $baseDir $dir))) {
        Write-Host "[ERROR] No existe: $dir" -ForegroundColor Red
        $allExist = $false
    }
}

if (-not $allExist) {
    Write-Host ""
    Write-Host "ERROR: La estructura nueva no esta completa." -ForegroundColor Red
    Write-Host "Primero ejecuta: .\reorganizar-estructura.ps1" -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

Write-Host "[OK] Estructura nueva existe" -ForegroundColor Green
Write-Host ""

# Verificar que scripts existen
$scriptCount = (Get-ChildItem (Join-Path $baseDir "_system_nuevo\generator") -Filter *.php).Count
Write-Host "[INFO] Scripts en _system_nuevo/generator: $scriptCount" -ForegroundColor Cyan

if ($scriptCount -lt 6) {
    Write-Host "[WARN] Pocos scripts ($scriptCount), esperado minimo 6" -ForegroundColor Yellow
    $continue = Read-Host "Continuar de todos modos? (S/N)"
    if ($continue -ne "S" -and $continue -ne "s") {
        exit 1
    }
}

Write-Host ""

# ======================================================================
# CONFIRMACIÓN USUARIO
# ======================================================================
Write-Host "QUE SE VA A ELIMINAR:" -ForegroundColor Yellow
Write-Host "  - _system/ (viejo)" -ForegroundColor Gray
Write-Host "  - generator/ (viejo)" -ForegroundColor Gray
Write-Host "  - templates/ (viejo)" -ForegroundColor Gray
Write-Host "  - admin/ (app fitness)" -ForegroundColor Gray
Write-Host "  - api/ (app fitness)" -ForegroundColor Gray
Write-Host "  - uploads/" -ForegroundColor Gray
Write-Host "  - 80+ archivos PHP/JS/CSS de app fitness" -ForegroundColor Gray
Write-Host "  - Documentos .md duplicados en raiz" -ForegroundColor Gray
Write-Host ""

Write-Host "QUE SE VA A MANTENER:" -ForegroundColor Green
Write-Host "  - _system_nuevo/ (se renombrara a _system/)" -ForegroundColor Gray
Write-Host "  - public_html_nuevo/ (se renombrara a public_html/)" -ForegroundColor Gray
Write-Host "  - docs/" -ForegroundColor Gray
Write-Host "  - domains/" -ForegroundColor Gray
Write-Host "  - staging/" -ForegroundColor Gray
Write-Host "  - .git/, .gitignore, README.md" -ForegroundColor Gray
Write-Host "  - Scripts de utilidad (.ps1)" -ForegroundColor Gray
Write-Host ""

$confirm = Read-Host "ESTAS SEGURO? Esto NO se puede deshacer (S/N)"
if ($confirm -ne "S" -and $confirm -ne "s") {
    Write-Host "Cancelado" -ForegroundColor Yellow
    exit 0
}

Write-Host ""

# ======================================================================
# BACKUP AUTOMÁTICO
# ======================================================================
Write-Host "[1/5] Creando backup de seguridad..." -ForegroundColor Cyan

$timestamp = Get-Date -Format "yyyyMMdd-HHmm"
$backupFile = Join-Path $baseDir "BACKUP_ANTES_LIMPIEZA_$timestamp.zip"

try {
    # Crear lista de archivos a respaldar (importante)
    $backupDirs = @("_system", "generator", "templates", "admin", "api")
    
    Write-Host "  [INFO] Creando backup en: BACKUP_ANTES_LIMPIEZA_$timestamp.zip" -ForegroundColor Gray
    Write-Host "  [INFO] Esto puede tardar 1-2 minutos..." -ForegroundColor Gray
    
    # Comprimir carpetas viejas
    $compress = @{
        Path = $backupDirs | ForEach-Object { Join-Path $baseDir $_ } | Where-Object { Test-Path $_ }
        DestinationPath = $backupFile
        CompressionLevel = "Fastest"
    }
    
    Compress-Archive @compress -ErrorAction Stop
    
    $backupSizeMB = [math]::Round((Get-Item $backupFile).Length / 1MB, 2)
    Write-Host "  [OK] Backup creado ($backupSizeMB MB)" -ForegroundColor Green
    
} catch {
    Write-Host "  [ERROR] No se pudo crear backup: $_" -ForegroundColor Red
    Write-Host "  [INFO] Cancelando por seguridad..." -ForegroundColor Yellow
    exit 1
}

Write-Host ""

# ======================================================================
# ELIMINAR CARPETAS VIEJAS
# ======================================================================
Write-Host "[2/5] Eliminando carpetas viejas..." -ForegroundColor Cyan

$oldDirs = @(
    "_system",
    "generator",
    "templates",
    "admin",
    "api",
    "uploads"
)

foreach ($dir in $oldDirs) {
    $path = Join-Path $baseDir $dir
    if (Test-Path $path) {
        Remove-Item $path -Recurse -Force -ErrorAction SilentlyContinue
        if (Test-Path $path) {
            Write-Host "  [WARN] No se pudo eliminar: $dir" -ForegroundColor Yellow
        } else {
            Write-Host "  [OK] Eliminado: $dir" -ForegroundColor Green
        }
    }
}

Write-Host ""

# ======================================================================
# ELIMINAR ARCHIVOS VIEJOS
# ======================================================================
Write-Host "[3/5] Eliminando archivos viejos..." -ForegroundColor Cyan

# Lista de archivos a eliminar (TODOS los archivos de otavafitness y viejos)
$oldFiles = @(
    # Archivos principales de otavafitness (ya en Hostinger)
    "index.html", "styles.css", "script.js",
    
    # Admin y autenticación
    "admin-panel.php", "admin-script-DB.js", "admin-script.js",
    "auth-modal-fixed.js", "auth-modal.css", "auth-protection.js",
    
    # Carrito y productos
    "carrito.html", "cart-modern-script.js", "cart-modern-styles.css",
    "cart-script.js", "cart-styles.css", "cart-table-script.js", "cart-table-styles.css",
    "producto.html", "productos-simple.js",
    "product-grid-styles.css", "product-styles-CATEGORIAS.css", "product-styles.css",
    
    # Desafíos y gamificación
    "challenges-script.js", "challenges-styles.css",
    "desafios.html",
    "gamificacion.html", "gamification-script.js", "gamification-styles.css",
    "recompensas.html",
    "rewards-script.js", "rewards-styles.css",
    
    # Chatbot y componentes
    "chatbot-script.js", "chatbot-styles.css",
    "global-functions.js",
    
    # Fitness y perfiles
    "fitness-chart.js", "fitness-modern-styles.css", "fitness-styles.css",
    "perfil-fitness.html", "perfil.html",
    "profile-datos.js", "profile-direcciones.js", "profile-envios.js",
    "profile-modals.css", "profile-resenas.js", "profile-script.js",
    "profile-sections.css", "profile-styles.css",
    
    # Guías
    "guias-script.js", "guias.html",
    
    # Headers
    "header-auth.js", "header-modern.css", "header-modern.js",
    
    # Configuración y PHP
    "config-email.php", "config.php",
    "deploy-simple.php", "deploy-site.php", "deploy-v2.php",
    "process-template-curl.php", "process-template.php",
    "payment-integration.js",
    "reset-password.php",
    "verificar-boton.php", "verificar-email.php",
    "webhook-mercadopago.php",
    
    # Tests
    "test-paths.php", "test-permissions.php", "test-simple.php", "test-template.php"
)

$deletedCount = 0
foreach ($file in $oldFiles) {
    $path = Join-Path $baseDir $file
    if (Test-Path $path) {
        Remove-Item $path -Force -ErrorAction SilentlyContinue
        $deletedCount++
    }
}

Write-Host "  [OK] Eliminados $deletedCount archivos viejos" -ForegroundColor Green
Write-Host ""

# ======================================================================
# ELIMINAR DOCUMENTACIÓN DUPLICADA
# ======================================================================
Write-Host "[4/5] Eliminando documentacion duplicada en raiz..." -ForegroundColor Cyan

$docPatterns = @("AUDITORIA_*.md", "ESTRUCTURA_*.md", "GUIA_*.md", "RESUMEN_*.md", 
                 "FLUJO_*.md", "REVISION_*.md", "SESION_*.md", "QUE_FALTA_*.md",
                 "CHECKLIST_*.md", "INSTRUCCIONES_*.md", "ANALISIS_*.md")

$deletedDocs = 0
foreach ($pattern in $docPatterns) {
    $files = Get-ChildItem -Path $baseDir -Filter $pattern -File
    foreach ($file in $files) {
        Remove-Item $file.FullName -Force -ErrorAction SilentlyContinue
        $deletedDocs++
    }
}

Write-Host "  [OK] Eliminados $deletedDocs documentos duplicados" -ForegroundColor Green
Write-Host ""

# ======================================================================
# RENOMBRAR ESTRUCTURA NUEVA
# ======================================================================
Write-Host "[5/5] Activando estructura nueva..." -ForegroundColor Cyan

# Renombrar _system_nuevo -> _system
if (Test-Path (Join-Path $baseDir "_system_nuevo")) {
    Rename-Item (Join-Path $baseDir "_system_nuevo") "_system"
    Write-Host "  [OK] _system_nuevo -> _system" -ForegroundColor Green
}

# Renombrar public_html_nuevo -> public_html
if (Test-Path (Join-Path $baseDir "public_html_nuevo")) {
    Rename-Item (Join-Path $baseDir "public_html_nuevo") "public_html"
    Write-Host "  [OK] public_html_nuevo -> public_html" -ForegroundColor Green
}

Write-Host ""

# ======================================================================
# VERIFICACIÓN FINAL
# ======================================================================
Write-Host "================================================================" -ForegroundColor Green
Write-Host "  LIMPIEZA COMPLETADA" -ForegroundColor Green
Write-Host "================================================================" -ForegroundColor Green
Write-Host ""

Write-Host "Estructura final:" -ForegroundColor Yellow
Write-Host ""

$finalDirs = @("_system", "public_html", "domains", "staging", "docs")
foreach ($dir in $finalDirs) {
    $path = Join-Path $baseDir $dir
    if (Test-Path $path) {
        $itemCount = (Get-ChildItem $path -Recurse -File).Count
        Write-Host "  [OK] $dir/ ($itemCount archivos)" -ForegroundColor Green
    } else {
        Write-Host "  [ERROR] Falta: $dir/" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Archivos importantes:" -ForegroundColor Yellow
$importantFiles = @(".gitignore", "README.md", ".env.example")
foreach ($file in $importantFiles) {
    if (Test-Path (Join-Path $baseDir $file)) {
        Write-Host "  [OK] $file" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "BACKUP guardado en:" -ForegroundColor Cyan
Write-Host "  $backupFile" -ForegroundColor Gray
Write-Host ""

Write-Host "Proximos pasos:" -ForegroundColor Yellow
Write-Host "  1. Verificar estructura: .\verificar-estructura.ps1" -ForegroundColor White
Write-Host "  2. Configurar .env: cd _system\config, copy .env.example .env" -ForegroundColor White
Write-Host "  3. Listo para subir a Hostinger" -ForegroundColor White
Write-Host ""

Write-Host "ESTRUCTURA LISTA PARA PRODUCCION" -ForegroundColor Green -BackgroundColor Black
Write-Host ""
