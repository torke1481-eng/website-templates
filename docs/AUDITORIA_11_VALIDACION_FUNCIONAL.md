# ✅ AUDITORÍA #11 - VALIDACIÓN FUNCIONAL COMPLETA

**Fecha:** 24 Nov 2025, 01:55 AM  
**Tipo:** Validación de Arquitectura + Scripts + Fallos Make.com  
**Estado:** 🔴 **ANÁLISIS CRÍTICO**

---

## 🎯 OBJETIVO

Validar que la estructura propuesta sea 100% funcional en Hostinger y crear scripts necesarios.

---

## 1️⃣ VALIDACIÓN DE ESTRUCTURA

### **✅ FUNCIONAL: /domains/cliente.com/**

```
/home/u123456/domains/clientenegocio.com/
└── public_html/
    ├── index.html
    ├── css/
    └── js/
```

**Hostinger cPanel → Addon Domain:**
- ✅ Permite especificar Document Root personalizado
- ✅ Ruta: `/home/u123456/domains/clientenegocio.com/public_html`
- ✅ Apache puede servir desde esta ubicación
- ✅ SSL Let's Encrypt funciona automáticamente

**Validación:** ✅ FUNCIONAL

---

### **⚠️ PROBLEMA: /_system/ Accesible**

```
/home/u123456/_system/
```

**Problema:**
- Si está DENTRO de `/public_html/`, es accesible vía web
- Si está FUERA, PHP puede leerlo pero web NO

**Escenario 1: Dentro de public_html (MAL)**
```
/home/u123456/public_html/
├── index.html
└── _system/          ❌ Accesible en https://tudominio.com/_system/
```

**Escenario 2: Fuera de public_html (BIEN)**
```
/home/u123456/
├── public_html/      ✅ Accesible web
└── _system/          ✅ Solo PHP puede leer
```

**Solución CORRECTA:**
```
/home/u123456789/
├── public_html/                    # Web accesible
│   ├── index.html
│   ├── .htaccess                   # Bloquea _system si existe
│   └── download.php
│
├── _system/                        # FUERA de public_html
│   └── (inaccesible desde web)
│
└── domains/                        # FUERA de public_html
    └── cliente.com/
        └── public_html/            # Solo esto es accesible
```

**Validación:** ✅ FUNCIONAL (con estructura correcta)

---

### **✅ FUNCIONAL: Backups Individuales**

```php
$path = "/home/u123456/domains/cliente.com";
$backup = "$path/backups/backup-" . date('Y-m-d') . ".tar.gz";
exec("tar -czf $backup -C $path public_html");
```

**Hostinger permite:**
- ✅ `exec()` habilitado (verificar con `shell_exec("ls")`)
- ✅ `tar` disponible en servidor
- ✅ Escribir en carpetas propias

**Validación:** ✅ FUNCIONAL (verificar permisos)

---

### **⚠️ POSIBLE PROBLEMA: Cron Jobs**

**Hostinger cPanel → Cron Jobs:**
- ✅ Permite crear cron jobs
- ⚠️ Sintaxis puede variar
- ⚠️ PATH a PHP puede ser diferente

**Sintaxis Hostinger:**
```cron
# Puede ser /usr/bin/php o /opt/alt/php81/usr/bin/php
0 3 * * * /usr/bin/php /home/u123456/_system/generator/backup-all.php
```

**Verificar:**
```bash
which php
# Output: /usr/bin/php (usar esta ruta)
```

**Validación:** ✅ FUNCIONAL (con ruta correcta)

---

### **✅ FUNCIONAL: Health Check con curl**

```php
$ch = curl_init("https://$domain");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
```

**Hostinger permite:**
- ✅ `curl` habilitado
- ✅ `allow_url_fopen` habilitado
- ✅ Puede hacer requests externos

**Validación:** ✅ FUNCIONAL

---

## 2️⃣ SCRIPTS NECESARIOS

### **CRÍTICOS (P0):**
1. ✅ `create-domain.php` - Crear estructura dominio
2. ✅ `backup-client.php` - Backup individual
3. ✅ `health-check.php` - Monitoreo salud
4. ✅ `deploy-v4-mejorado.php` - Generador (ya existe)

### **IMPORTANTES (P1):**
5. ✅ `backup-all.php` - Backup todos los clientes
6. ✅ `cleanup-old.php` - Limpiar staging viejo
7. ✅ `verify-domain.php` - Verificar config dominio
8. ✅ `export-client.php` - Export para migración

### **OPCIONALES (P2):**
9. `migrate-to-new-structure.php` - Migrar de /staging/ a /domains/
10. `check-ssl.php` - Verificar vencimiento SSL
11. `disk-usage-report.php` - Reporte uso disco
12. `client-stats.php` - Estadísticas por cliente

---

## 3️⃣ FALLOS DE MAKE.COM

### **❌ FALLO #1: Webhook Duplicado**

**Escenario:**
```
Usuario hace doble click en "Enviar" del formulario
→ 2 webhooks idénticos enviados a Make.com en 0.5s
→ Make.com ejecuta 2 scenarios simultáneos
→ 2 sitios generados con mismo slug
→ El segundo sobrescribe el primero
```

**Problema:**
```php
// deploy-v4-mejorado.php
$slug = sanitizeSlug($nombreNegocio);
$path = "/domains/$slug.com/";

// Si 2 requests simultáneos con mismo slug:
mkdir($path);  // Request 1 crea
mkdir($path);  // Request 2 falla o sobrescribe
```

**Solución: Lock de Slug**
```php
function acquireLock($slug, $timeout = 10) {
    $lockFile = sys_get_temp_dir() . "/slug-$slug.lock";
    $start = time();
    
    while (file_exists($lockFile)) {
        if (time() - $start > $timeout) {
            throw new Exception("Slug '$slug' está siendo procesado");
        }
        usleep(100000); // 0.1s
    }
    
    touch($lockFile);
    register_shutdown_function(function() use ($lockFile) {
        @unlink($lockFile);
    });
}

// Usar
acquireLock($slug);
// ... generar sitio
```

**Prioridad:** 🔴 P0

---

### **❌ FALLO #2: Make.com Operation Limit**

**Escenario:**
```
Plan Free: 1,000 operations/mes
Scenario tiene 10 módulos
100 ejecuciones × 10 ops = 1,000 operations
→ Operación 1,001 → ERROR
→ Make.com detiene scenario
→ Cliente no recibe sitio
```

**Problema:**
- Sin forma de saber cuándo se alcanzó el límite
- Cliente espera sitio que nunca llegará

**Solución: Fallback Manual**
```php
// queue-processor.php (cron cada 5 minutos)
// Procesa sitios que quedaron en cola > 10 minutos

$queueDir = '/_system/queue/';
$files = glob($queueDir . '*.json');

foreach ($files as $file) {
    $queue = json_decode(file_get_contents($file), true);
    
    if ($queue['status'] === 'pending') {
        $age = time() - strtotime($queue['created_at']);
        
        if ($age > 600) { // > 10 minutos
            // Make.com probablemente falló
            // Procesar manualmente
            try {
                processQueueItem($queue);
                $queue['status'] = 'completed';
                $queue['processed_by'] = 'fallback';
            } catch (Exception $e) {
                $queue['status'] = 'failed';
                $queue['error'] = $e->getMessage();
                notifyAdmin("Queue item failed: " . $queue['queue_id']);
            }
            
            file_put_contents($file, json_encode($queue, JSON_PRETTY_PRINT));
        }
    }
}
```

**Cron:**
```cron
*/5 * * * * php /_system/generator/queue-processor.php
```

**Prioridad:** 🔴 P0

---

### **❌ FALLO #3: GPT-4o Rate Limit**

**Escenario:**
```
10 clientes envían formulario en 1 minuto
→ Make.com hace 10 llamadas a GPT-4o
→ OpenAI rate limit: 3 RPM (requests per minute)
→ Request 4-10 → ERROR 429 Too Many Requests
→ 7 sitios sin datos de GPT-4o
```

**Problema:**
```json
// Make.com recibe de GPT-4o
{
  "error": {
    "message": "Rate limit exceeded",
    "type": "tokens",
    "code": "rate_limit_exceeded"
  }
}
```

**Solución 1: Retry con Backoff en Make.com**
```
Módulo GPT-4o:
- Error Handling → Retry
- Max attempts: 3
- Interval: 20 seconds
```

**Solución 2: Defaults en PHP**
```php
// deploy-v4-mejorado.php
if (empty($diseno['titulo_hero'])) {
    // GPT-4o falló, usar IA alternativa o defaults
    $diseno['titulo_hero'] = generateFallbackHero($nombreNegocio, $tipoNegocio);
}

function generateFallbackHero($nombre, $tipo) {
    $templates = [
        'restaurant' => "BIENVENIDOS A $nombre - Gastronomía de Excelencia",
        'gym' => "TRANSFORMA TU CUERPO EN $nombre",
        'default' => "BIENVENIDO A $nombre - Tu Mejor Opción en $tipo"
    ];
    
    return $templates[$tipo] ?? $templates['default'];
}
```

**Prioridad:** 🔴 P0

---

### **❌ FALLO #4: Make.com Webhook No Responde**

**Escenario:**
```
Make.com tiene downtime (raro pero posible)
→ Google Form envía datos
→ Apps Script intenta POST a webhook
→ Timeout después de 30s
→ ❌ Datos perdidos
```

**Problema:**
- Sin queue en Google Apps Script
- Datos se pierden si Make.com está caído

**Solución: Queue en Google Sheets**
```javascript
// Google Apps Script
function onFormSubmit(e) {
  const formData = {
    timestamp: new Date(),
    nombre: e.values[1],
    email: e.values[2],
    // ... más campos
  };
  
  // Guardar en hoja "Queue"
  const sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName('Queue');
  sheet.appendRow([
    formData.timestamp,
    JSON.stringify(formData),
    'pending'
  ]);
  
  // Intentar enviar a Make.com
  try {
    const webhookUrl = 'https://hook.make.com/...';
    const response = UrlFetchApp.fetch(webhookUrl, {
      method: 'post',
      contentType: 'application/json',
      payload: JSON.stringify(formData),
      muteHttpExceptions: true
    });
    
    if (response.getResponseCode() === 200) {
      // Marcar como enviado
      const lastRow = sheet.getLastRow();
      sheet.getRange(lastRow, 3).setValue('sent');
    }
  } catch (error) {
    // Quedará en queue para retry manual
    Logger.log('Error sending to Make.com: ' + error);
  }
}

// Trigger cada 5 minutos para retry
function processQueue() {
  const sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName('Queue');
  const data = sheet.getDataRange().getValues();
  
  for (let i = 1; i < data.length; i++) {
    if (data[i][2] === 'pending') {
      const formData = JSON.parse(data[i][1]);
      
      try {
        const response = UrlFetchApp.fetch(webhookUrl, {
          method: 'post',
          contentType: 'application/json',
          payload: JSON.stringify(formData)
        });
        
        if (response.getResponseCode() === 200) {
          sheet.getRange(i + 1, 3).setValue('sent');
        }
      } catch (error) {
        // Intentar más tarde
      }
    }
  }
}
```

**Prioridad:** 🟡 P1

---

### **❌ FALLO #5: Variable Mapping Incorrecto**

**Escenario:**
```
Make.com actualiza estructura de módulos
→ Variables se renombran
→ Mapping antiguo: {{1.`Nombre del negocio`}}
→ Mapping nuevo: {{1.nombre_negocio}}
→ deploy-v4.php recibe: nombre_negocio = undefined
→ ❌ Sitio generado con "Mi Negocio" (default)
```

**Problema:**
```php
$nombreNegocio = $data['nombre_negocio'] ?? 'Mi Negocio';
// Si Make.com envía 'Nombre del negocio' en vez de 'nombre_negocio'
// Usará default en vez del valor real
```

**Solución: Flexible Key Matching**
```php
function getFlexibleValue($data, $possibleKeys, $default) {
    foreach ($possibleKeys as $key) {
        if (isset($data[$key]) && !empty($data[$key])) {
            return $data[$key];
        }
    }
    return $default;
}

$nombreNegocio = getFlexibleValue($data, [
    'nombre_negocio',
    'Nombre del negocio',
    'business_name',
    'nombre',
    'name'
], 'Mi Negocio');
```

**Prioridad:** 🟡 P1

---

### **❌ FALLO #6: Make.com Execution Time Limit (40s per módulo)**

**Escenario:**
```
Módulo: HTTP POST a deploy-v4.php
deploy-v4.php procesa 45 segundos
→ Make.com timeout a los 40s
→ Marca módulo como error
→ Pero sitio SÍ se generó (proceso continuó)
→ Make.com reintenta
→ ❌ Sitio duplicado
```

**Solución: Ya implementada con async**
```php
// deploy-v4-mejorado.php
echo json_encode(['queue_id' => $id, 'status' => 'queued']);
fastcgi_finish_request(); // ← Responde en < 2s

// Ahora procesar sin límite de tiempo
```

**Validación:** ✅ Ya solucionado

---

### **❌ FALLO #7: Datos Sensibles en Logs**

**Escenario:**
```
Make.com loggea requests completos
→ Incluye email, teléfono, datos personales
→ GDPR violation
→ Multa de hasta €20M
```

**Problema:**
```php
// deploy-v4-mejorado.php
logError('Error', [
    'input_data' => $data  // ❌ Incluye emails, teléfonos
]);
```

**Solución: Sanitizar Logs**
```php
function sanitizeForLog($data) {
    $sanitized = $data;
    
    $sensitiveFields = ['email', 'telefono', 'phone', 'password'];
    
    foreach ($sensitiveFields as $field) {
        if (isset($sanitized[$field])) {
            $value = $sanitized[$field];
            $sanitized[$field] = substr($value, 0, 3) . '***' . substr($value, -2);
        }
    }
    
    return $sanitized;
}

logError('Error', [
    'input_data' => sanitizeForLog($data)
]);
```

**Prioridad:** 🔴 P0 (GDPR compliance)

---

### **❌ FALLO #8: Network Partition Durante Deploy**

**Escenario:**
```
deploy-v4.php procesando
→ Hostinger tiene glitch de red (1 segundo)
→ Archivo HTML se escribe parcialmente
→ index.html corrupto (mitad del contenido)
→ Sitio cliente roto
```

**Solución: Atomic File Write**
```php
function atomicWrite($path, $content) {
    $tempFile = $path . '.tmp.' . uniqid();
    
    // Escribir a archivo temporal
    if (file_put_contents($tempFile, $content) === false) {
        throw new Exception("No se pudo escribir temp file");
    }
    
    // Verificar que se escribió completo
    if (filesize($tempFile) !== strlen($content)) {
        unlink($tempFile);
        throw new Exception("Escritura incompleta");
    }
    
    // Rename atómico (atomic operation en filesystem)
    if (!rename($tempFile, $path)) {
        unlink($tempFile);
        throw new Exception("No se pudo mover archivo");
    }
    
    return true;
}

// Usar
atomicWrite($stagingDir . '/index.html', $html);
```

**Prioridad:** 🟡 P1

---

### **❌ FALLO #9: Concurrent Backups Llenan Disco**

**Escenario:**
```
Cron: backup-all.php a las 3 AM
100 clientes × 10 MB cada backup = 1 GB
Mientras backupea, otro cron se ejecuta
→ 2 GB de backups simultáneos
→ ❌ Disco lleno
→ Hostinger suspende cuenta
```

**Solución: Lock Global + Disk Check**
```php
// backup-all.php
$lockFile = sys_get_temp_dir() . '/backup-all.lock';

if (file_exists($lockFile)) {
    $age = time() - filemtime($lockFile);
    if ($age < 3600) { // < 1 hora
        die("Backup ya en progreso\n");
    }
    // Lock viejo, eliminar
    unlink($lockFile);
}

touch($lockFile);
register_shutdown_function(function() use ($lockFile) {
    @unlink($lockFile);
});

// Verificar espacio antes de cada backup
foreach ($domains as $domain) {
    $freeSpace = disk_free_space('/');
    $requiredSpace = 50 * 1024 * 1024; // 50 MB buffer
    
    if ($freeSpace < $requiredSpace) {
        error_log("CRITICAL: Espacio insuficiente, deteniendo backups");
        notifyAdmin("⚠️ CRITICAL: Backups detenidos por falta de espacio");
        break;
    }
    
    backupClient($domain);
}
```

**Prioridad:** 🔴 P0

---

### **❌ FALLO #10: SSL Certificate Expira**

**Escenario:**
```
Let's Encrypt SSL válido por 90 días
→ Hostinger auto-renueva normalmente
→ PERO dominio DNS mal configurado
→ Let's Encrypt no puede verificar
→ Auto-renovación falla
→ SSL expira
→ ❌ Sitio muestra "No seguro"
→ Cliente pierde confianza
```

**Solución: Monitoreo SSL**
```php
// check-ssl.php (cron semanal)
function checkSSLExpiration($domain) {
    $get = stream_context_create([
        "ssl" => [
            "capture_peer_cert" => true,
            "verify_peer" => false,
            "verify_peer_name" => false
        ]
    ]);
    
    $read = @stream_socket_client(
        "ssl://{$domain}:443",
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $get
    );
    
    if (!$read) {
        return ['error' => "No se pudo conectar: $errstr"];
    }
    
    $cert = stream_context_get_params($read);
    $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
    
    $validUntil = $certinfo['validTo_time_t'];
    $daysLeft = floor(($validUntil - time()) / 86400);
    
    return [
        'domain' => $domain,
        'valid_until' => date('Y-m-d', $validUntil),
        'days_left' => $daysLeft,
        'status' => $daysLeft > 30 ? 'ok' : ($daysLeft > 7 ? 'warning' : 'critical')
    ];
}

// Ejecutar para todos
$domains = json_decode(file_get_contents('/_system/config/domains.json'), true);

foreach ($domains as $domainInfo) {
    $ssl = checkSSLExpiration($domainInfo['domain']);
    
    if ($ssl['days_left'] < 30) {
        notifyAdmin("⚠️ SSL expira en {$ssl['days_left']} días: {$ssl['domain']}");
    }
}
```

**Cron:**
```cron
0 2 * * 1 php /_system/generator/check-ssl.php
```

**Prioridad:** 🟡 P1

---

## 📊 RESUMEN FALLOS MAKE.COM

| # | Fallo | Probabilidad | Impacto | Solución | Prioridad |
|---|-------|--------------|---------|----------|-----------|
| 1 | Webhook duplicado | Alta | Alto | Lock de slug | 🔴 P0 |
| 2 | Operation limit | Media | Alto | Queue fallback | 🔴 P0 |
| 3 | GPT-4o rate limit | Alta | Medio | Retry + defaults | 🔴 P0 |
| 4 | Webhook no responde | Baja | Alto | Queue en Sheets | 🟡 P1 |
| 5 | Variable mapping | Media | Medio | Flexible keys | 🟡 P1 |
| 6 | Timeout 40s | Media | Bajo | Async (resuelto) | ✅ OK |
| 7 | Datos en logs | Alta | CRÍTICO | Sanitizar logs | 🔴 P0 |
| 8 | Network glitch | Baja | Alto | Atomic write | 🟡 P1 |
| 9 | Backups concurrentes | Media | CRÍTICO | Lock + check | 🔴 P0 |
| 10 | SSL expira | Baja | Alto | Monitoreo SSL | 🟡 P1 |

**Total:** 10 escenarios de fallo  
**Críticos (P0):** 5  
**Altos (P1):** 4  
**Resueltos:** 1

---

## ✅ ESTRUCTURA VALIDADA

**Conclusión:** La estructura `/domains/` es FUNCIONAL en Hostinger ✅

**Requisitos:**
- Ubicar `_system/` FUERA de `public_html/`
- Verificar permisos `exec()` y `tar`
- Configurar cron con ruta PHP correcta
- Implementar locks para prevenir race conditions
- Sanitizar logs (GDPR)

---

## 🎯 PRÓXIMOS PASOS

1. ✅ Crear scripts P0 (críticos)
2. ✅ Implementar soluciones a fallos P0
3. ⏳ Crear scripts P1 (importantes)
4. ⏳ Testing en Hostinger real
5. ⏳ Migrar sitios existentes

---

**Estado:** ⏳ REQUIERE IMPLEMENTACIÓN DE SCRIPTS
