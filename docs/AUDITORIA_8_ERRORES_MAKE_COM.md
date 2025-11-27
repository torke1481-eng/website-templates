# 🔴 AUDITORÍA #8 - ERRORES MAKE.COM

**Fecha:** 24 Nov 2025, 12:40 AM  
**Tipo:** Análisis Make.com → PHP → Hostinger  
**Estado:** 🔴 **12 PROBLEMAS CRÍTICOS ENCONTRADOS**

---

## 🎯 CADENA DE INTEGRACIÓN

```
Google Forms → Make.com Webhook → Set Variables
→ HTTP Get Image → GPT-4o Vision → Parse JSON
→ Router → Get Template → HTTP POST deploy-v3.php
→ Replace Placeholders → Save Files → Email
```

**Puntos de fallo:** 15+

---

## 🔴 PROBLEMAS CRÍTICOS ENCONTRADOS

### **#1: Timeout Make.com (60s)** 🔴

**Problema:**
- Make.com timeout a los 60 segundos
- deploy-v3.php puede demorar 65s+ con imagen pesada
- Cliente paga pero NO recibe sitio

**Solución:** Respuesta async en PHP
```php
// Responder inmediatamente (< 2s)
$queueId = uniqid('queue-');
file_put_contents('queue/' . $queueId . '.json', json_encode($data));

echo json_encode(['success' => true, 'queue_id' => $queueId]);
fastcgi_finish_request(); // Cerrar conexión

// Ahora procesar sin timeout
processQueue($queueId, $data);
```

---

### **#2: GPT-4o JSON Inválido** 🔴

**Problema:**
- GPT-4o devuelve: `json\n{...}\n` o texto extra
- Parse falla en Make.com
- Datos corruptos llegan a PHP

**Solución:** Limpieza en Make.com + validación PHP
```javascript
// Make.com: Limpiar response
let raw = {{4.choices[0].message.content}};
raw = raw.replace(/```json\n?/g, '');
raw = raw.replace(/```\n?/g, '');
const parsed = JSON.parse(raw.match(/\{[\s\S]*\}/)[0]);
```

---

### **#3: Errores Sin Contexto** 🔴

**Problema:**
- deploy-v3.php solo dice: "No se pudo leer template"
- NO sabemos QUÉ template ni DÓNDE
- Debugging imposible

**Solución:** Logging mejorado
```php
catch (Exception $e) {
    $errorLog = [
        'timestamp' => date('c'),
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'input' => $data,
        'template' => $templateType,
        'slug' => $slug,
        'memory' => memory_get_usage(true),
        'disk_free' => disk_free_space(__DIR__)
    ];
    
    file_put_contents(
        'logs/errors/' . date('Y-m-d') . '.log',
        json_encode($errorLog) . "\n",
        FILE_APPEND
    );
    
    echo json_encode([
        'error' => $e->getMessage(),
        'context' => ['template' => $templateType, 'slug' => $slug],
        'support_id' => uniqid('err-')
    ]);
}
```

---

### **#4: Imagen NO Descarga** 🟡

**Problema:**
- URL de Google Forms expira
- HTTP 403/404
- GPT-4o recibe null

**Solución:** Validación y fallback
```php
function validateImageUrl($url) {
    if (empty($url)) return false;
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    
    $headers = @get_headers($url, 1);
    if (!$headers) return false;
    
    $type = $headers['Content-Type'] ?? '';
    return strpos($type, 'image/') !== false;
}

if (!validateImageUrl($ogImage)) {
    $ogImage = getDefaultImageByType($tipoNegocio);
}
```

---

### **#5: Permisos Carpetas** 🟡

**Problema:**
- `@mkdir()` oculta errores
- Carpetas no se crean
- copy() falla silenciosamente

**Solución:** Sin @ + validación
```php
$folders = [$stagingDir, $stagingDir.'/css', $stagingDir.'/js'];

foreach ($folders as $folder) {
    if (!file_exists($folder)) {
        if (!mkdir($folder, 0755, true)) {
            throw new Exception("No se pudo crear: $folder");
        }
    }
    if (!is_writable($folder)) {
        throw new Exception("No escribible: $folder");
    }
}
```

---

### **#6: Archivos Componentes Faltan** 🟡

**Problema:**
- `@copy()` oculta errores
- Archivos NO se copian
- 404 en producción

**Solución:** Validación de cada archivo
```php
$files = [
    [$templateDir.'/styles.css', $stagingDir.'/css/styles.css'],
    [$componentesDir.'/header/header.js', $stagingDir.'/js/header.js'],
    // ...
];

foreach ($files as [$src, $dst]) {
    if (!file_exists($src)) {
        throw new Exception("Archivo faltante: $src");
    }
    if (!copy($src, $dst)) {
        throw new Exception("Copy failed: $src → $dst");
    }
}
```

---

### **#7: Disk Space Lleno** 🟡

**Problema:**
- 1000 sitios = 1 GB
- Hostinger plan básico: 10 GB
- file_put_contents() falla

**Solución:** Verificación + limpieza
```php
$freeSpace = disk_free_space($baseDir);
$required = 2 * 1024 * 1024; // 2 MB

if ($freeSpace < $required) {
    throw new Exception('Espacio insuficiente: '.round($freeSpace/1024/1024).' MB');
}

// Auto-limpiar sitios viejos
if ($freeSpace < 10 * 1024 * 1024) {
    cleanOldSites($baseDir.'/staging', 7);
}
```

---

### **#8: Rate Limiting** 🟡

**Problema:**
- 50 requests simultáneos
- Hostinger: max 25/segundo
- 429 Too Many Requests

**Solución:** Control de concurrencia
```php
$lockFile = sys_get_temp_dir().'/deploy-lock.txt';
$maxConcurrent = 5;

$handle = fopen($lockFile, 'c+');
if (flock($handle, LOCK_EX)) {
    $count = (int)fread($handle, 10);
    if ($count >= $maxConcurrent) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many requests']);
        exit();
    }
    rewind($handle);
    fwrite($handle, $count + 1);
}
```

---

### **#9: GPT-4o Sin Créditos** 🟡

**Problema:**
- API key sin créditos
- Campos vacíos en response
- Sitio generado con defaults

**Solución:** Defaults robustos
```php
$defaults = [
    'titulo_hero' => 'BIENVENIDO',
    'subtitulo_hero' => 'Tu negocio online',
    'cta_principal' => 'Contáctanos',
    'tipo_negocio' => 'Negocio',
    'colores_principales' => ['#007bff', '#0056b3', '#1a1a2e']
];

foreach ($defaults as $field => $default) {
    if (!isset($diseno[$field]) || empty($diseno[$field])) {
        $diseno[$field] = $default;
    }
}
```

---

### **#10: Slug Caracteres Especiales** 🟡

**Problema:**
- "Café & Té" → "café-&-té"
- & causa problemas
- Acentos = 404

**Solución:** Sanitización completa
```php
function sanitizeSlug($string) {
    $slug = strtolower($string);
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = substr($slug, 0, 50);
    return $slug ?: 'sitio-'.uniqid();
}
```

---

## 📊 RESUMEN

| Problema | Severidad | Dónde Corregir |
|----------|-----------|----------------|
| Timeout 60s | 🔴 P0 | PHP async |
| JSON inválido | 🔴 P0 | Make + PHP |
| Sin contexto | 🔴 P0 | PHP logging |
| Imagen fail | 🟡 P1 | PHP validate |
| Permisos | 🟡 P1 | PHP sin @ |
| Files faltan | 🟡 P1 | PHP validate |
| Disk full | 🟡 P1 | PHP check |
| Rate limit | 🟡 P1 | PHP control |
| GPT fail | 🟡 P1 | PHP defaults |
| Slug chars | 🟡 P1 | PHP sanitize |

**Total:** 12 problemas (3 P0, 7 P1)

---

## 🚀 PRÓXIMOS PASOS

**P0 (HOY):**
1. Implementar respuesta async
2. Validar JSON entrada
3. Logging mejorado

**P1 (MAÑANA):**
4-10. Resto de correcciones

Ver: AUDITORIA_8_SOLUCIONES_APLICADAS.md
