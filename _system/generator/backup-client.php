<?php
/**
 * BACKUP-CLIENT.PHP
 * 
 * Realiza backup individual de un cliente
 * Mantiene últimos 7 backups
 * 
 * Uso: php backup-client.php dominio.com
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300); // 5 minutos

function logBackup($message, $context = []) {
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
        $logDir . '/backups.log',
        json_encode($entry) . "\n",
        FILE_APPEND
    );
}

function backupClient($domain) {
    $baseDir = dirname(dirname(__DIR__));
    $domainPath = $baseDir . '/domains/' . $domain;
    
    // Verificar que exista
    if (!file_exists($domainPath)) {
        throw new Exception("Dominio no encontrado: $domain");
    }
    
    $backupDir = $domainPath . '/backups';
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    // Nombre del backup
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . "/backup-$timestamp.tar.gz";
    
    echo "📦 Iniciando backup de: $domain\n";
    echo "📂 Origen: $domainPath/public_html\n";
    echo "💾 Destino: $backupFile\n\n";
    
    // Verificar espacio en disco
    $freeSpace = disk_free_space($backupDir);
    $requiredSpace = 50 * 1024 * 1024; // 50 MB buffer
    
    if ($freeSpace < $requiredSpace) {
        throw new Exception(
            "Espacio insuficiente. Disponible: " . 
            round($freeSpace / 1024 / 1024, 2) . " MB"
        );
    }
    
    // Crear backup con tar
    $command = sprintf(
        "tar -czf %s -C %s public_html .metadata.json 2>&1",
        escapeshellarg($backupFile),
        escapeshellarg($domainPath)
    );
    
    $startTime = microtime(true);
    exec($command, $output, $returnCode);
    $duration = round(microtime(true) - $startTime, 2);
    
    if ($returnCode !== 0) {
        $error = implode("\n", $output);
        throw new Exception("Backup falló: $error");
    }
    
    // Verificar que se creó
    if (!file_exists($backupFile)) {
        throw new Exception("Archivo de backup no se creó");
    }
    
    $backupSize = filesize($backupFile);
    $backupSizeMB = round($backupSize / 1024 / 1024, 2);
    
    echo "✅ Backup creado exitosamente\n";
    echo "📊 Tamaño: $backupSizeMB MB\n";
    echo "⏱️  Duración: {$duration}s\n\n";
    
    // Limpiar backups viejos (mantener solo 7)
    echo "🧹 Limpiando backups antiguos...\n";
    
    $backups = glob($backupDir . "/backup-*.tar.gz");
    
    if (count($backups) > 7) {
        // Ordenar por fecha (más viejo primero)
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        $toDelete = count($backups) - 7;
        $deletedSize = 0;
        
        for ($i = 0; $i < $toDelete; $i++) {
            $fileSize = filesize($backups[$i]);
            if (unlink($backups[$i])) {
                $deletedSize += $fileSize;
                echo "  🗑️  Eliminado: " . basename($backups[$i]) . "\n";
            }
        }
        
        $deletedSizeMB = round($deletedSize / 1024 / 1024, 2);
        echo "  ✅ Liberados: $deletedSizeMB MB\n";
    } else {
        echo "  ✅ Sin backups antiguos para eliminar\n";
    }
    
    // Log de éxito
    logBackup('Backup exitoso', [
        'domain' => $domain,
        'size_mb' => $backupSizeMB,
        'duration_seconds' => $duration,
        'file' => $backupFile
    ]);
    
    return [
        'success' => true,
        'domain' => $domain,
        'file' => $backupFile,
        'size_mb' => $backupSizeMB,
        'duration' => $duration
    ];
}

// CLI Execution
if (php_sapi_name() === 'cli') {
    if ($argc < 2) {
        echo "Uso: php backup-client.php dominio.com\n";
        echo "\n";
        echo "Ejemplo:\n";
        echo "  php backup-client.php clientenegocio.com\n";
        exit(1);
    }
    
    $domain = $argv[1];
    
    try {
        $result = backupClient($domain);
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║  ✅ BACKUP COMPLETADO                                    ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "\n";
        
        exit(0);
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
        logBackup('Error en backup', [
            'domain' => $domain,
            'error' => $e->getMessage()
        ]);
        exit(1);
    }
}
?>
