<?php
/**
 * CLEANUP-OLD.PHP
 * 
 * Limpia previews de staging antiguos (>7 días)
 * Se ejecuta automáticamente con cron diario
 * 
 * Uso: php cleanup-old.php
 */

// Configuración
$baseDir = dirname(dirname(__DIR__));
$stagingDir = $baseDir . '/staging';
$maxAge = 7; // días
$logDir = $baseDir . '/_system/logs';

// Función de logging
function logAction($message, $data = []) {
    global $logDir;
    
    if (!file_exists($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/cleanup.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message";
    
    if (!empty($data)) {
        $logEntry .= ' | ' . json_encode($data);
    }
    
    file_put_contents($logFile, $logEntry . "\n", FILE_APPEND);
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  🧹 LIMPIEZA DE STAGING - Eliminando >$maxAge días           \n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

logAction('Iniciando limpieza de staging');

// Verificar que existe carpeta staging
if (!file_exists($stagingDir)) {
    echo "⚠️  Carpeta staging no existe, creando...\n";
    mkdir($stagingDir, 0755, true);
    logAction('Carpeta staging creada');
    echo "✅ Carpeta staging creada\n\n";
    exit(0);
}

// Listar contenido de staging
$items = glob($stagingDir . '/*', GLOB_ONLYDIR);

if (empty($items)) {
    echo "ℹ️  No hay sitios en staging\n";
    logAction('Staging vacío, nada que limpiar');
    echo "\n";
    exit(0);
}

echo "📂 Encontrados " . count($items) . " sitios en staging\n\n";

$now = time();
$cutoffTime = $now - ($maxAge * 86400); // 86400 = segundos en un día

$deleted = 0;
$kept = 0;
$errors = 0;
$spaceFreed = 0;

foreach ($items as $itemPath) {
    $itemName = basename($itemPath);
    
    // Obtener fecha de modificación
    $modTime = filemtime($itemPath);
    $age = floor(($now - $modTime) / 86400);
    
    echo "🔍 $itemName\n";
    echo "   Edad: $age días\n";
    
    if ($modTime < $cutoffTime) {
        // Calcular tamaño antes de eliminar
        $size = 0;
        if (is_dir($itemPath)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($itemPath, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        
        $sizeMB = round($size / 1024 / 1024, 2);
        
        // Eliminar
        try {
            deleteDirectory($itemPath);
            
            if (!file_exists($itemPath)) {
                echo "   ✅ ELIMINADO ($sizeMB MB liberados)\n";
                $deleted++;
                $spaceFreed += $size;
                
                logAction('Sitio eliminado', [
                    'slug' => $itemName,
                    'age_days' => $age,
                    'size_mb' => $sizeMB
                ]);
            } else {
                echo "   ❌ ERROR al eliminar\n";
                $errors++;
                
                logAction('Error al eliminar', [
                    'slug' => $itemName,
                    'age_days' => $age
                ]);
            }
        } catch (Exception $e) {
            echo "   ❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
            $errors++;
            
            logAction('Excepción al eliminar', [
                'slug' => $itemName,
                'error' => $e->getMessage()
            ]);
        }
    } else {
        echo "   ⏳ Mantener (aún reciente)\n";
        $kept++;
    }
    
    echo "\n";
}

// ======================================================================
// RESUMEN
// ======================================================================
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  📊 RESUMEN DE LIMPIEZA\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$spaceFreedMB = round($spaceFreed / 1024 / 1024, 2);

echo "Total encontrados:  " . count($items) . " sitios\n";
echo "✅ Eliminados:      $deleted sitios\n";
echo "⏳ Mantenidos:      $kept sitios\n";
echo "❌ Errores:         $errors\n";
echo "\n";
echo "💾 Espacio liberado: {$spaceFreedMB} MB\n";
echo "\n";

logAction('Limpieza completada', [
    'deleted' => $deleted,
    'kept' => $kept,
    'errors' => $errors,
    'space_freed_mb' => $spaceFreedMB
]);

if ($deleted > 0) {
    echo "✅ LIMPIEZA COMPLETADA\n";
} else {
    echo "ℹ️  NO HAY NADA QUE LIMPIAR\n";
}

echo "\n";

/**
 * Función auxiliar para eliminar directorios recursivamente
 */
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    $items = array_diff(scandir($dir), ['.', '..']);
    
    foreach ($items as $item) {
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

exit(0);
?>
