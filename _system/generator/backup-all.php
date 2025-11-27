<?php
/**
 * BACKUP-ALL.PHP
 * 
 * Backup automático de TODOS los clientes
 * Se ejecuta con cron diario
 * 
 * Uso: php backup-all.php
 * Cron: 0 3 * * * (3 AM diario)
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
set_time_limit(0); // Sin límite

// Lock global para evitar ejecuciones simultáneas
$lockFile = sys_get_temp_dir() . '/backup-all.lock';

if (file_exists($lockFile)) {
    $lockAge = time() - filemtime($lockFile);
    
    if ($lockAge < 7200) { // < 2 horas
        echo "⚠️  Backup ya en progreso (lock file exists)\n";
        exit(1);
    }
    
    // Lock viejo, probablemente proceso muerto
    echo "🔓 Removiendo lock antiguo...\n";
    unlink($lockFile);
}

touch($lockFile);

register_shutdown_function(function() use ($lockFile) {
    @unlink($lockFile);
});

// Require backup-client.php
require_once __DIR__ . '/backup-client.php';

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  💾 BACKUP MASIVO - " . date('Y-m-d H:i:s') . "                      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

try {
    // Leer dominios
    $configFile = dirname(__DIR__) . '/config/domains.json';
    
    if (!file_exists($configFile)) {
        throw new Exception("Config file no encontrado");
    }
    
    $domains = json_decode(file_get_contents($configFile), true);
    
    if (empty($domains)) {
        echo "ℹ️  No hay dominios para respaldar\n\n";
        exit(0);
    }
    
    $total = count($domains);
    echo "📊 Total dominios: $total\n";
    
    // Verificar espacio ANTES de empezar
    $freeSpace = disk_free_space(dirname(__DIR__));
    $freeSpaceGB = round($freeSpace / 1024 / 1024 / 1024, 2);
    $requiredSpace = 100 * 1024 * 1024; // 100 MB mínimo
    
    echo "💽 Espacio libre: {$freeSpaceGB} GB\n";
    
    if ($freeSpace < $requiredSpace) {
        throw new Exception("Espacio insuficiente para backups");
    }
    
    echo "\n";
    
    $successful = 0;
    $failed = 0;
    $totalSize = 0;
    $startTime = microtime(true);
    
    foreach ($domains as $index => $domainInfo) {
        $domain = $domainInfo['domain'];
        $num = $index + 1;
        
        echo "[$num/$total] Procesando: $domain\n";
        echo str_repeat('─', 60) . "\n";
        
        try {
            // Verificar espacio antes de cada backup
            $currentFreeSpace = disk_free_space(dirname(__DIR__));
            
            if ($currentFreeSpace < $requiredSpace) {
                echo "⚠️  Espacio crítico, deteniendo backups\n";
                $failed++;
                break;
            }
            
            $result = backupClient($domain);
            
            $totalSize += $result['size_mb'];
            $successful++;
            
        } catch (Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            $failed++;
        }
        
        echo "\n";
    }
    
    $duration = round(microtime(true) - $startTime, 2);
    $totalSizeGB = round($totalSize / 1024, 2);
    
    // Resumen final
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║  📊 RESUMEN FINAL                                            ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "✅ Exitosos:     $successful\n";
    echo "❌ Fallidos:     $failed\n";
    echo "📦 Tamaño total: {$totalSizeGB} GB\n";
    echo "⏱️  Duración:     {$duration}s\n";
    echo "\n";
    
    // Log final
    logBackup('Backup masivo completado', [
        'total' => $total,
        'successful' => $successful,
        'failed' => $failed,
        'size_gb' => $totalSizeGB,
        'duration' => $duration
    ]);
    
    if ($failed > 0) {
        exit(1);
    }
    
    exit(0);
    
} catch (Exception $e) {
    echo "\n❌ ERROR FATAL: " . $e->getMessage() . "\n\n";
    exit(1);
}
?>
