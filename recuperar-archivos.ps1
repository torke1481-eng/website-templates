# ======================================================================
# SCRIPT DE RECUPERACIÓN - ARCHIVOS DEL BACKUP
# ======================================================================
# Recupera los archivos faltantes del backup automático
# Ejecutar: powershell -ExecutionPolicy Bypass .\recuperar-archivos.ps1

Write-Host ""
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "  RECUPERANDO ARCHIVOS DEL BACKUP" -ForegroundColor Cyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""

$baseDir = $PSScriptRoot

# ======================================================================
# 1. BUSCAR BACKUP
# ======================================================================
Write-Host "[1/5] Buscando archivo de backup..." -ForegroundColor Cyan

$backupFiles = Get-ChildItem -Path $baseDir -Filter "BACKUP_ANTES_LIMPIEZA_*.zip" | Sort-Object LastWriteTime -Descending

if ($backupFiles.Count -eq 0) {
    Write-Host "[ERROR] No se encontro archivo de backup" -ForegroundColor Red
    Write-Host "Buscar: BACKUP_ANTES_LIMPIEZA_*.zip" -ForegroundColor Yellow
    exit 1
}

$backupFile = $backupFiles[0]
Write-Host "[OK] Backup encontrado: $($backupFile.Name)" -ForegroundColor Green
Write-Host "    Tamano: $([math]::Round($backupFile.Length / 1KB, 2)) KB" -ForegroundColor Gray
Write-Host ""

# ======================================================================
# 2. EXTRAER BACKUP A CARPETA TEMPORAL
# ======================================================================
Write-Host "[2/5] Extrayendo backup..." -ForegroundColor Cyan

$tempDir = Join-Path $baseDir "TEMP_BACKUP_RECOVERY"

# Limpiar carpeta temporal si existe
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}

try {
    Expand-Archive -Path $backupFile.FullName -DestinationPath $tempDir -Force
    Write-Host "[OK] Backup extraido a: TEMP_BACKUP_RECOVERY" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] No se pudo extraer backup: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""

# ======================================================================
# 3. RECUPERAR SCRIPTS FALTANTES
# ======================================================================
Write-Host "[3/5] Recuperando scripts faltantes..." -ForegroundColor Cyan

$scriptsToRecover = @(
    @{
        Source = "generator\deploy-v4-mejorado.php"
        Dest = "_system\generator\deploy-v4-mejorado.php"
        Critical = $true
    },
    @{
        Source = "generator\verify-domain.php"
        Dest = "_system\generator\verify-domain.php"
        Critical = $false
    },
    @{
        Source = "generator\cleanup-old.php"
        Dest = "_system\generator\cleanup-old.php"
        Critical = $false
    }
)

$recoveredCount = 0
$criticalRecovered = 0

foreach ($script in $scriptsToRecover) {
    $sourcePath = Join-Path $tempDir $script.Source
    $destPath = Join-Path $baseDir $script.Dest
    $scriptName = Split-Path $script.Dest -Leaf
    
    if (Test-Path $sourcePath) {
        # Crear directorio destino si no existe
        $destDir = Split-Path $destPath -Parent
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        # Copiar archivo
        Copy-Item $sourcePath $destPath -Force
        
        if (Test-Path $destPath) {
            if ($script.Critical) {
                Write-Host "  [OK] CRITICO recuperado: $scriptName" -ForegroundColor Green
                $criticalRecovered++
            } else {
                Write-Host "  [OK] Recuperado: $scriptName" -ForegroundColor Green
            }
            $recoveredCount++
        } else {
            Write-Host "  [WARN] No se pudo copiar: $scriptName" -ForegroundColor Yellow
        }
    } else {
        Write-Host "  [WARN] No encontrado en backup: $scriptName" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "  Total recuperados: $recoveredCount de $($scriptsToRecover.Count)" -ForegroundColor Cyan

if ($criticalRecovered -eq 0) {
    Write-Host "  [WARN] No se recupero el script critico deploy-v4-mejorado.php" -ForegroundColor Yellow
}

Write-Host ""

# ======================================================================
# 4. COPIAR .env.example A CONFIG
# ======================================================================
Write-Host "[4/5] Configurando .env.example en config..." -ForegroundColor Cyan

$envExample = Join-Path $baseDir ".env.example"
$envConfigDest = Join-Path $baseDir "_system\config\.env.example"

if (Test-Path $envExample) {
    $configDir = Join-Path $baseDir "_system\config"
    if (-not (Test-Path $configDir)) {
        New-Item -ItemType Directory -Path $configDir -Force | Out-Null
    }
    
    Copy-Item $envExample $envConfigDest -Force
    
    if (Test-Path $envConfigDest) {
        Write-Host "[OK] .env.example copiado a _system/config/" -ForegroundColor Green
    }
} else {
    Write-Host "[WARN] .env.example no encontrado en raiz" -ForegroundColor Yellow
}

Write-Host ""

# ======================================================================
# 5. LIMPIAR ARCHIVOS DE OTAVAFITNESS EN PUBLIC_HTML
# ======================================================================
Write-Host "[5/5] Limpiando archivos de otavafitness en public_html..." -ForegroundColor Cyan

$otavafitnesFiles = @(
    "public_html\index.html",
    "public_html\styles.css",
    "public_html\script.js"
)

$cleanedCount = 0
foreach ($file in $otavafitnesFiles) {
    $filePath = Join-Path $baseDir $file
    if (Test-Path $filePath) {
        Remove-Item $filePath -Force
        if (-not (Test-Path $filePath)) {
            Write-Host "[OK] Eliminado: $(Split-Path $file -Leaf)" -ForegroundColor Green
            $cleanedCount++
        }
    }
}

if ($cleanedCount -gt 0) {
    Write-Host "  Total eliminados: $cleanedCount archivos de otavafitness" -ForegroundColor Cyan
} else {
    Write-Host "[INFO] No hay archivos de otavafitness para eliminar" -ForegroundColor Gray
}

Write-Host ""

# ======================================================================
# LIMPIAR CARPETA TEMPORAL
# ======================================================================
Write-Host "[Limpieza] Eliminando carpeta temporal..." -ForegroundColor Gray

if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force -ErrorAction SilentlyContinue
}

Write-Host ""

# ======================================================================
# RESULTADO FINAL
# ======================================================================
Write-Host "================================================================" -ForegroundColor Green
Write-Host "  RECUPERACION COMPLETADA" -ForegroundColor Green
Write-Host "================================================================" -ForegroundColor Green
Write-Host ""

# Verificar scripts finales
Write-Host "Scripts en _system/generator:" -ForegroundColor Yellow
$finalScripts = Get-ChildItem -Path (Join-Path $baseDir "_system\generator") -Filter *.php
Write-Host "  Total: $($finalScripts.Count) archivos PHP" -ForegroundColor Cyan

foreach ($script in $finalScripts) {
    Write-Host "  [OK] $($script.Name)" -ForegroundColor Green
}

Write-Host ""

# Verificar que deploy-v4-mejorado.php existe
$deployScript = Join-Path $baseDir "_system\generator\deploy-v4-mejorado.php"
if (Test-Path $deployScript) {
    Write-Host "[OK] CRITICO: deploy-v4-mejorado.php recuperado correctamente" -ForegroundColor Green -BackgroundColor Black
} else {
    Write-Host "[ERROR] CRITICO: deploy-v4-mejorado.php NO recuperado" -ForegroundColor Red -BackgroundColor Black
    Write-Host "        El sistema NO funcionara sin este archivo" -ForegroundColor Red
}

Write-Host ""
Write-Host "Proximos pasos:" -ForegroundColor Yellow
Write-Host "  1. Verificar sistema: .\verificar-estructura.ps1" -ForegroundColor White
Write-Host "  2. Configurar .env: cd _system\config, copy .env.example .env" -ForegroundColor White
Write-Host "  3. Listo para Hostinger" -ForegroundColor White
Write-Host ""

if ($recoveredCount -eq $scriptsToRecover.Count) {
    Write-Host "SISTEMA 100% FUNCIONAL" -ForegroundColor Green -BackgroundColor Black
} else {
    Write-Host "SISTEMA PARCIALMENTE RECUPERADO" -ForegroundColor Yellow -BackgroundColor Black
}

Write-Host ""
