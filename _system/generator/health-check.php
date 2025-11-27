<?php
/**
 * HEALTH-CHECK.PHP
 * 
 * Verifica salud de todos los dominios
 * Score: DNS, HTTP, SSL, Files
 * Envía alertas si hay problemas
 * 
 * Uso: php health-check.php
 * Cron: 0 * * * * (cada hora)
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
set_time_limit(300);

function logHealth($message, $context = []) {
    $logDir = __DIR__ . '/../logs/health';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $entry = [
        'timestamp' => date('c'),
        'message' => $message,
        'context' => $context
    ];
    
    file_put_contents(
        $logDir . '/health-' . date('Y-m-d') . '.log',
        json_encode($entry) . "\n",
        FILE_APPEND
    );
}

function checkDomainHealth($domain, $path) {
    $checks = [];
    $issues = [];
    
    echo "🔍 Verificando: $domain\n";
    
    // 1. DNS Check
    echo "  [1/5] DNS... ";
    $ip = @gethostbyname($domain);
    $checks['dns'] = ($ip !== $domain && filter_var($ip, FILTER_VALIDATE_IP));
    
    if ($checks['dns']) {
        echo "✅ OK ($ip)\n";
    } else {
        echo "❌ FAIL\n";
        $issues[] = "DNS no resuelve";
    }
    
    // 2. HTTP Check
    echo "  [2/5] HTTP... ";
    $ch = curl_init("https://$domain");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_NOBODY => true
    ]);
    
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    
    $checks['http'] = ($httpCode === 200);
    $checks['http_code'] = $httpCode;
    $checks['response_time'] = round($httpTime, 2);
    
    if ($checks['http']) {
        echo "✅ OK (200, {$httpTime}s)\n";
    } else {
        echo "❌ FAIL ($httpCode)\n";
        $issues[] = "HTTP error: $httpCode";
    }
    
    // 3. SSL Check
    echo "  [3/5] SSL... ";
    $sslValid = false;
    $sslDaysLeft = 0;
    
    $streamContext = stream_context_create([
        "ssl" => [
            "capture_peer_cert" => true,
            "verify_peer" => false,
            "verify_peer_name" => false
        ]
    ]);
    
    $client = @stream_socket_client(
        "ssl://{$domain}:443",
        $errno,
        $errstr,
        10,
        STREAM_CLIENT_CONNECT,
        $streamContext
    );
    
    if ($client) {
        $cont = stream_context_get_params($client);
        $cert = openssl_x509_parse($cont['options']['ssl']['peer_certificate']);
        
        if ($cert) {
            $validUntil = $cert['validTo_time_t'];
            $sslDaysLeft = floor(($validUntil - time()) / 86400);
            $sslValid = ($sslDaysLeft > 0);
        }
        
        fclose($client);
    }
    
    $checks['ssl'] = $sslValid;
    $checks['ssl_days_left'] = $sslDaysLeft;
    
    if ($sslValid) {
        echo "✅ OK ($sslDaysLeft días)\n";
        if ($sslDaysLeft < 30) {
            $issues[] = "SSL expira en $sslDaysLeft días";
        }
    } else {
        echo "❌ FAIL\n";
        $issues[] = "SSL inválido o expirado";
    }
    
    // 4. Files Check
    echo "  [4/5] Files... ";
    $filesOk = file_exists($path . '/index.html');
    $checks['files_exist'] = $filesOk;
    
    if ($filesOk) {
        echo "✅ OK\n";
    } else {
        echo "❌ FAIL (index.html no existe)\n";
        $issues[] = "Archivos faltantes";
    }
    
    // 5. Disk Usage
    echo "  [5/5] Disk... ";
    $diskUsage = 0;
    if (is_dir($path)) {
        $output = shell_exec("du -sb " . escapeshellarg($path));
        if ($output) {
            $diskUsage = intval($output);
        }
    }
    
    $checks['disk_usage'] = $diskUsage;
    $checks['disk_usage_mb'] = round($diskUsage / 1024 / 1024, 2);
    
    echo "✅ {$checks['disk_usage_mb']} MB\n";
    
    // Score total
    $score = 0;
    if ($checks['dns']) $score += 25;
    if ($checks['http']) $score += 25;
    if ($checks['ssl']) $score += 25;
    if ($checks['files_exist']) $score += 25;
    
    $checks['health_score'] = $score;
    
    // Status
    if ($score === 100) {
        $checks['status'] = 'healthy';
        $statusEmoji = '✅';
    } elseif ($score >= 75) {
        $checks['status'] = 'warning';
        $statusEmoji = '⚠️';
    } elseif ($score >= 50) {
        $checks['status'] = 'degraded';
        $statusEmoji = '⚠️';
    } else {
        $checks['status'] = 'critical';
        $statusEmoji = '🔴';
    }
    
    $checks['issues'] = $issues;
    
    echo "  $statusEmoji Score: $score/100 - {$checks['status']}\n";
    
    if (!empty($issues)) {
        echo "  ⚠️  Problemas:\n";
        foreach ($issues as $issue) {
            echo "      - $issue\n";
        }
    }
    
    echo "\n";
    
    return $checks;
}

function notifyAdmin($message, $details = []) {
    // Aquí puedes integrar:
    // - Email
    // - Slack webhook
    // - Discord webhook
    // - Telegram bot
    
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $alert = [
        'timestamp' => date('c'),
        'message' => $message,
        'details' => $details
    ];
    
    file_put_contents(
        $logDir . '/alerts.log',
        json_encode($alert) . "\n",
        FILE_APPEND
    );
    
    echo "🚨 ALERTA: $message\n";
}

// Main execution
try {
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║  🏥 HEALTH CHECK - " . date('Y-m-d H:i:s') . "                  ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "\n";
    
    // Leer dominios
    $configFile = dirname(__DIR__) . '/config/domains.json';
    
    if (!file_exists($configFile)) {
        throw new Exception("Config file no encontrado: $configFile");
    }
    
    $domains = json_decode(file_get_contents($configFile), true);
    
    if (empty($domains)) {
        echo "ℹ️  No hay dominios para verificar\n\n";
        exit(0);
    }
    
    echo "📊 Total dominios: " . count($domains) . "\n\n";
    
    $report = [];
    $healthyCount = 0;
    $warningCount = 0;
    $criticalCount = 0;
    
    foreach ($domains as $domainInfo) {
        $domain = $domainInfo['domain'];
        $path = $domainInfo['path'];
        
        try {
            $health = checkDomainHealth($domain, $path);
            
            $report[] = [
                'domain' => $domain,
                'health' => $health,
                'timestamp' => date('c')
            ];
            
            // Contar por estado
            if ($health['status'] === 'healthy') {
                $healthyCount++;
            } elseif ($health['status'] === 'critical') {
                $criticalCount++;
                
                // Alertar si crítico
                notifyAdmin(
                    "⚠️ CRITICAL: $domain health score: {$health['health_score']}/100",
                    $health
                );
            } else {
                $warningCount++;
                
                // Alertar si hay problemas específicos
                if (!empty($health['issues'])) {
                    notifyAdmin(
                        "⚠️ WARNING: $domain - " . implode(', ', $health['issues']),
                        $health
                    );
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Error verificando $domain: " . $e->getMessage() . "\n\n";
            
            $report[] = [
                'domain' => $domain,
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ];
            
            $criticalCount++;
        }
    }
    
    // Guardar reporte
    $reportDir = dirname(__DIR__) . '/logs/health';
    if (!file_exists($reportDir)) {
        mkdir($reportDir, 0755, true);
    }
    
    $reportFile = $reportDir . '/report-' . date('Y-m-d_H-i') . '.json';
    file_put_contents(
        $reportFile,
        json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
    
    // Resumen
    echo "═══════════════════════════════════════════════════════════\n";
    echo "📈 RESUMEN:\n";
    echo "   ✅ Healthy:  $healthyCount\n";
    echo "   ⚠️  Warning:  $warningCount\n";
    echo "   🔴 Critical: $criticalCount\n";
    echo "\n";
    echo "📄 Reporte: $reportFile\n";
    echo "\n";
    
    // Log general
    logHealth('Health check completado', [
        'total' => count($domains),
        'healthy' => $healthyCount,
        'warning' => $warningCount,
        'critical' => $criticalCount
    ]);
    
    // Exit code según estado
    if ($criticalCount > 0) {
        exit(2); // Critical
    } elseif ($warningCount > 0) {
        exit(1); // Warning
    } else {
        exit(0); // OK
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR FATAL: " . $e->getMessage() . "\n\n";
    logHealth('Error fatal', ['error' => $e->getMessage()]);
    exit(3);
}
?>
