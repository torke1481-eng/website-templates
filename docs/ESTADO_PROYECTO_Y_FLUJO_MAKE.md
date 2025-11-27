# 🎯 ESTADO DEL PROYECTO Y FLUJO MAKE.COM OPTIMIZADO

**Fecha:** 27 Noviembre 2025  
**Objetivo:** Análisis completo + Flujo Make.com enfocado en CALIDAD

---

## 📊 ESTADO ACTUAL DEL PROYECTO

### ✅ LO QUE ESTÁ LISTO Y FUNCIONAL

| Componente | Archivo | Estado | Notas |
|------------|---------|--------|-------|
| **Database MySQL** | `_system/config/db.php` | ✅ 100% | 4 tablas, vistas, procedures |
| **Schema SQL** | `_system/config/schema.sql.txt` | ✅ 100% | Listo para ejecutar |
| **Template Premium** | `_system/templates/landing-pro/` | ✅ 100% | 12 secciones, SEO, responsive |
| **Deploy Script** | `_system/generator/deploy-v4-mejorado.php` | ✅ 90% | Robusto, async, validación |
| **Prompt Claude** | `_system/config/PROMPT_MAKE_COMPLETO.txt` | ✅ 100% | Template inline + instrucciones |
| **JSON Agente** | Último JSON que enviaste | ✅ 95% | Servicios con nombres reales |
| **Documentación** | `docs/` (42 archivos) | ✅ 100% | Completa |

### ⚠️ LO QUE FALTA CONECTAR

| Brecha | Descripción | Impacto | Solución |
|--------|-------------|---------|----------|
| **Deploy Simple** | No existe script para recibir HTML de Claude | 🔴 Alto | Crear `deploy-simple.php` |
| **Mapeo JSON→Prompt** | El JSON del agente debe formatearse para Claude | 🟡 Medio | Hacer en Make.com |
| **Verificación Post-Deploy** | No hay endpoint para verificar que la página existe | 🟡 Medio | Agregar en deploy |

---

## 🔑 DECISIÓN CRÍTICA: ¿QUIÉN GENERA EL HTML?

### OPCIÓN A: Claude genera HTML COMPLETO (RECOMENDADA ✅)

```
JSON Agente → Make.com → Claude (genera TODO el HTML) → Deploy
```

**Ventajas:**
- Claude tiene el template embebido en el prompt
- No hay mapeo de 100+ variables
- Resultado más coherente y personalizado
- Claude puede adaptar contenido inteligentemente

**El prompt `PROMPT_MAKE_COMPLETO.txt` ya tiene:**
- Template HTML completo con CSS inline
- Instrucciones claras de reemplazo
- Reglas de secciones condicionales
- Fallbacks para datos faltantes

### OPCIÓN B: PHP reemplaza variables en template

```
JSON Agente → Make.com → PHP (str_replace 100+ vars) → Deploy
```

**Desventajas:**
- Requiere mapear 100+ variables
- Menos flexible
- Más código PHP que mantener
- Errores de variables no reemplazadas

---

## 🚀 FLUJO MAKE.COM OPTIMIZADO PARA CALIDAD

### FILOSOFÍA: "Lento pero seguro"

```
PRIORIDAD: CALIDAD > VELOCIDAD
TIEMPO ESTIMADO: 60-90 segundos por página
COSTO: ~$0.03-0.05 por página
RESULTADO: Página 90%+ lista
```

---

## 📋 FLUJO DETALLADO (12 MÓDULOS)

```
┌─────────────────────────────────────────────────────────────┐
│                    FLUJO MAKE.COM                           │
│              "Generación de Alta Calidad"                   │
└─────────────────────────────────────────────────────────────┘

     ┌──────────────────────────────────────┐
     │ 1. WEBHOOK - Recibir JSON Agente    │
     │    Trigger: Cuando agente completa   │
     │    Input: JSON completo del negocio  │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 2. VALIDACIÓN CRÍTICA               │
     │    ✓ nombre existe                   │
     │    ✓ ciudad existe                   │
     │    ✓ teléfono/whatsapp existe        │
     │    ✓ al menos 1 servicio             │
     │    → Si falla: Error + Notificación  │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 3. PREPARAR DATOS PARA CLAUDE       │
     │    • Formatear JSON limpio           │
     │    • Extraer campos clave            │
     │    • Limpiar WhatsApp (solo números) │
     │    • Generar slug                    │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 4. CONSTRUIR PROMPT COMPLETO        │
     │    • Cargar PROMPT_MAKE_COMPLETO.txt │
     │    • Insertar JSON en {{JSON_NEGOCIO}}│
     │    • Agregar instrucciones extra     │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 5. CLAUDE API - GENERACIÓN          │
     │    Model: claude-3-5-sonnet-latest   │
     │    Max tokens: 16000                 │
     │    Temperature: 0.3 (más consistente)│
     │    Timeout: 120 segundos             │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 6. VALIDACIÓN HTML GENERADO         │
     │    ✓ Empieza con <!DOCTYPE html>     │
     │    ✓ Termina con </html>             │
     │    ✓ Tiene <header>, <main>, <footer>│
     │    ✓ No tiene [placeholder] sin usar │
     │    ✓ Tamaño > 10KB y < 500KB         │
     │    → Si falla: Retry 1 vez           │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 7. MEJORAS AUTOMÁTICAS              │
     │    • Agregar año actual en copyright │
     │    • Verificar links WhatsApp        │
     │    • Agregar meta viewport si falta  │
     │    • Limpiar espacios extra          │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 8. DEPLOY A SERVIDOR                │
     │    POST → deploy-simple.php          │
     │    Body: { html, slug, metadata }    │
     │    Header: X-API-Key (seguridad)     │
     │    Respuesta: { url_staging }        │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 9. VERIFICAR DEPLOY                 │
     │    GET → url_staging                 │
     │    ✓ Status 200                      │
     │    ✓ Contenido tiene nombre negocio  │
     │    → Si falla: Retry o Error         │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 10. GUARDAR EN DATABASE             │
     │     POST → api/save-website.php      │
     │     • domain, slug, status           │
     │     • staging_url, created_at        │
     │     • json_config (backup)           │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 11. NOTIFICACIÓN EMAIL              │
     │     To: tu email                     │
     │     Subject: "Nueva web: [nombre]"   │
     │     Body:                            │
     │       • Link preview staging         │
     │       • Link aprobar                 │
     │       • Link rechazar                │
     │       • Datos del negocio            │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 12. FIN - ESPERA APROBACIÓN         │
     │     Status: pending_approval         │
     │     Siguiente: Click en email        │
     └──────────────────────────────────────┘
```

---

## 🔧 CONFIGURACIÓN DE CADA MÓDULO

### MÓDULO 1: Webhook

```
Tipo: Webhooks > Custom webhook
Nombre: "Recibir JSON Prospector"
```

### MÓDULO 2: Validación (JavaScript)

```javascript
// Código para módulo Tools > Run JavaScript

const data = JSON.parse(inputData.json);

// Validaciones críticas
const errors = [];

if (!data.negocio?.nombre) errors.push("Falta nombre del negocio");
if (!data.negocio?.ubicacion?.ciudad && !data.landing_page_ready?.contacto?.ciudad) {
  errors.push("Falta ciudad");
}
if (!data.negocio?.contacto?.telefono_whatsapp && !data.landing_page_ready?.contacto?.whatsapp) {
  errors.push("Falta WhatsApp");
}
if (!data.landing_page_ready?.servicios?.items?.length) {
  errors.push("Faltan servicios");
}

if (errors.length > 0) {
  return { valid: false, errors: errors.join(", ") };
}

return { valid: true, errors: null };
```

### MÓDULO 3: Preparar Datos (JavaScript)

```javascript
const data = JSON.parse(inputData.json);
const lp = data.landing_page_ready || {};
const negocio = data.negocio || {};
const contacto = negocio.contacto || lp.contacto || {};

// Limpiar WhatsApp (solo números)
let whatsapp = contacto.telefono_whatsapp || contacto.whatsapp || "";
whatsapp = whatsapp.replace(/[^0-9]/g, "");

// Generar slug
let slug = negocio.nombre_slug || negocio.nombre || "sitio";
slug = slug.toLowerCase()
  .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
  .replace(/[^a-z0-9]+/g, "-")
  .replace(/-+/g, "-")
  .replace(/^-|-$/g, "")
  .substring(0, 50);

return {
  nombre: negocio.nombre,
  slug: slug,
  ciudad: negocio.ubicacion?.ciudad || lp.contacto?.ciudad || "Argentina",
  whatsapp: whatsapp,
  telefono: contacto.telefono || "",
  email: contacto.email || "",
  direccion: negocio.ubicacion?.direccion_completa || lp.contacto?.direccion || "",
  emoji: lp.emoji_logo || negocio.emoji || "🏢",
  colores: lp.colores_css || {},
  servicios: lp.servicios?.items || [],
  testimonios: lp.testimonios?.items || [],
  hero: lp.hero || {},
  sobre_nosotros: lp.sobre_nosotros || {},
  seo: lp.seo || {},
  json_completo: JSON.stringify(data)
};
```

### MÓDULO 4: Construir Prompt

```
Tipo: Tools > Set Variable

Variable: prompt_completo

Valor:
---
Eres un desarrollador web senior. Genera una landing page HTML COMPLETA y FUNCIONAL.

# REGLAS OBLIGATORIAS

1. OUTPUT: Solo HTML. Empieza con <!DOCTYPE html>, termina con </html>. Sin ``` ni explicaciones.
2. VARIABLES: Reemplaza TODOS los campos con los datos proporcionados. NUNCA dejar placeholders.
3. WHATSAPP: Solo números, sin +, espacios ni guiones. Usar: {{3.whatsapp}}
4. SECCIONES CONDICIONALES: Si testimonios están vacíos → ELIMINAR sección completa.
5. COLORES: Usar primary={{3.colores.primary}}, accent={{3.colores.accent}}

# DATOS DEL NEGOCIO

Nombre: {{3.nombre}}
Ciudad: {{3.ciudad}}
Teléfono: {{3.telefono}}
WhatsApp: {{3.whatsapp}}
Dirección: {{3.direccion}}
Emoji: {{3.emoji}}

## Colores
- Primary: {{3.colores.primary}}
- Secondary: {{3.colores.secondary}}
- Accent: {{3.colores.accent}}
- Background: {{3.colores.background}}
- Text: {{3.colores.text}}

## Hero
- Título: {{3.hero.titulo_principal}}
- Subtítulo: {{3.hero.subtitulo}}
- CTA: {{3.hero.cta_principal.texto}}

## Sobre Nosotros
- Título: {{3.sobre_nosotros.titulo}}
- Descripción: {{3.sobre_nosotros.descripcion}}
- Puntos clave: {{3.sobre_nosotros.puntos_clave}}

## Servicios
{{3.servicios}}

## Testimonios
{{3.testimonios}}

## SEO
- Title: {{3.seo.title}}
- Description: {{3.seo.description}}
- Keywords: {{3.seo.keywords}}

# TEMPLATE HTML - GENERAR EXACTAMENTE ESTA ESTRUCTURA:

[AQUÍ VA EL CONTENIDO DE PROMPT_MAKE_COMPLETO.txt DESDE LÍNEA 16]
---
```

### MÓDULO 5: Claude API

```
Tipo: HTTP > Make a request

URL: https://api.anthropic.com/v1/messages
Method: POST

Headers:
- x-api-key: {{CLAUDE_API_KEY}}
- anthropic-version: 2023-06-01
- content-type: application/json

Body (JSON):
{
  "model": "claude-3-5-sonnet-latest",
  "max_tokens": 16000,
  "temperature": 0.3,
  "messages": [
    {
      "role": "user",
      "content": "{{4.prompt_completo}}"
    }
  ]
}

Timeout: 120 segundos
Parse response: Yes
```

### MÓDULO 6: Validar HTML (JavaScript)

```javascript
const html = inputData.html;

const checks = {
  has_doctype: html.trim().startsWith("<!DOCTYPE html>"),
  has_html_end: html.trim().endsWith("</html>"),
  has_header: html.includes("<header"),
  has_main: html.includes("<main") || html.includes("<section"),
  has_footer: html.includes("<footer"),
  no_placeholders: !html.includes("{{") && !html.includes("[placeholder]"),
  size_ok: html.length > 10000 && html.length < 500000
};

const allValid = Object.values(checks).every(v => v === true);

return {
  valid: allValid,
  checks: checks,
  size_kb: Math.round(html.length / 1024),
  html: html
};
```

### MÓDULO 7: Mejoras Automáticas (JavaScript)

```javascript
let html = inputData.html;

// Reemplazar año en copyright
const year = new Date().getFullYear();
html = html.replace(/© 2024/g, `© ${year}`);
html = html.replace(/©2024/g, `© ${year}`);

// Asegurar que WhatsApp link es correcto
const whatsapp = inputData.whatsapp;
html = html.replace(/wa\.me\/\[WHATSAPP_NUMEROS\]/g, `wa.me/${whatsapp}`);
html = html.replace(/wa\.me\/undefined/g, `wa.me/${whatsapp}`);

// Limpiar espacios múltiples
html = html.replace(/\n\s*\n\s*\n/g, "\n\n");

return { html_final: html };
```

### MÓDULO 8: Deploy (HTTP Request)

```
Tipo: HTTP > Make a request

URL: https://otavafitness.com/_system/generator/deploy-simple.php
Method: POST

Headers:
- Content-Type: application/json
- X-API-Key: {{TU_API_KEY_SECRETA}}

Body (JSON):
{
  "html": "{{7.html_final}}",
  "slug": "{{3.slug}}",
  "nombre": "{{3.nombre}}",
  "metadata": {
    "ciudad": "{{3.ciudad}}",
    "whatsapp": "{{3.whatsapp}}",
    "created_at": "{{now}}"
  }
}

Parse response: Yes
```

### MÓDULO 9: Verificar Deploy (HTTP Request)

```
Tipo: HTTP > Make a request

URL: {{8.staging_url}}
Method: GET

Expected status: 200
```

### MÓDULO 10: Guardar en Database (HTTP Request)

```
Tipo: HTTP > Make a request

URL: https://otavafitness.com/_system/api/save-website.php
Method: POST

Headers:
- Content-Type: application/json
- X-API-Key: {{TU_API_KEY_SECRETA}}

Body (JSON):
{
  "domain": "{{3.slug}}",
  "business_name": "{{3.nombre}}",
  "template": "landing-pro",
  "staging_url": "{{8.staging_url}}",
  "status": "pending_approval",
  "config": {{3.json_completo}}
}
```

### MÓDULO 11: Email (Gmail)

```
Tipo: Email > Send an email

To: tu-email@gmail.com
Subject: 🆕 Nueva web lista: {{3.nombre}}

Body (HTML):
<h2>Nueva página web generada</h2>

<p><strong>Negocio:</strong> {{3.nombre}}</p>
<p><strong>Ciudad:</strong> {{3.ciudad}}</p>

<h3>🔗 Links</h3>
<p>
  <a href="{{8.staging_url}}" style="background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">
    👁️ Ver Preview
  </a>
</p>

<p>
  <a href="https://otavafitness.com/_system/api/approve.php?id={{10.website_id}}&action=approve">
    ✅ Aprobar
  </a>
  |
  <a href="https://otavafitness.com/_system/api/approve.php?id={{10.website_id}}&action=reject">
    ❌ Rechazar
  </a>
</p>

<h3>📊 Datos</h3>
<ul>
  <li>WhatsApp: {{3.whatsapp}}</li>
  <li>Servicios: {{length(3.servicios)}}</li>
  <li>Testimonios: {{length(3.testimonios)}}</li>
</ul>
```

---

## 📁 ARCHIVOS QUE NECESITAS CREAR

### 1. `deploy-simple.php` (CRÍTICO)

```php
<?php
/**
 * DEPLOY SIMPLE - Recibe HTML de Claude y lo guarda
 * Ruta: _system/generator/deploy-simple.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Verificar API Key
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$validKey = 'TU_API_KEY_SECRETA_AQUI'; // Cambiar esto

if ($apiKey !== $validKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Obtener datos
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['html']) || !isset($input['slug'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing html or slug']);
    exit;
}

$html = $input['html'];
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower($input['slug']));
$nombre = $input['nombre'] ?? $slug;
$metadata = $input['metadata'] ?? [];

// Crear directorio staging
$stagingBase = '/home/u253890393/domains/otavafitness.com/staging';
$siteDir = $stagingBase . '/' . $slug;

if (!file_exists($siteDir)) {
    mkdir($siteDir, 0755, true);
}

// Guardar HTML
$htmlFile = $siteDir . '/index.html';
$result = file_put_contents($htmlFile, $html);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save HTML']);
    exit;
}

// Guardar metadata
$metadataFile = $siteDir . '/.metadata.json';
file_put_contents($metadataFile, json_encode([
    'slug' => $slug,
    'nombre' => $nombre,
    'created_at' => date('c'),
    'metadata' => $metadata
], JSON_PRETTY_PRINT));

// Respuesta exitosa
$stagingUrl = 'https://otavafitness.com/staging/' . $slug . '/';

echo json_encode([
    'success' => true,
    'staging_url' => $stagingUrl,
    'slug' => $slug,
    'size_kb' => round(strlen($html) / 1024, 2)
]);
```

### 2. `save-website.php` (Para database)

```php
<?php
/**
 * SAVE WEBSITE - Guarda en MySQL
 * Ruta: _system/api/save-website.php
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Verificar API Key
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$validKey = 'TU_API_KEY_SECRETA_AQUI';

if ($apiKey !== $validKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $websiteId = insertWebsite(
        $input['domain'],
        $input['business_name'],
        $input['template'],
        $input['config'] ?? []
    );
    
    // Actualizar con staging URL
    $db = getDB();
    $stmt = $db->prepare("UPDATE websites SET staging_url = ?, status = ? WHERE id = ?");
    $stmt->execute([$input['staging_url'], $input['status'], $websiteId]);
    
    echo json_encode([
        'success' => true,
        'website_id' => $websiteId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
```

### 3. `approve.php` (Para aprobar/rechazar)

```php
<?php
/**
 * APPROVE - Aprobar o rechazar sitio
 * Ruta: _system/api/approve.php
 */

require_once __DIR__ . '/../config/db.php';

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$id || !in_array($action, ['approve', 'reject'])) {
    die('Parámetros inválidos');
}

try {
    $db = getDB();
    
    if ($action === 'approve') {
        // Mover de staging a producción
        $stmt = $db->prepare("UPDATE websites SET status = 'published', published_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        
        // TODO: Copiar archivos de staging a domains/
        
        echo "<h1>✅ Sitio Aprobado</h1>";
        echo "<p>El sitio ha sido publicado.</p>";
        
    } else {
        $stmt = $db->prepare("UPDATE websites SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
        
        echo "<h1>❌ Sitio Rechazado</h1>";
        echo "<p>El sitio ha sido marcado para revisión.</p>";
    }
    
} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . $e->getMessage() . "</p>";
}
```

---

## 📋 CHECKLIST PARA IMPLEMENTAR

### [FileZilla] Archivos a crear/subir:

```
□ _system/generator/deploy-simple.php
□ _system/api/save-website.php
□ _system/api/approve.php
```

### [cPanel] Configuración:

```
□ Verificar que MySQL está funcionando
□ Ejecutar schema.sql.txt si no está hecho
□ Configurar credenciales en db.php
```

### [Make.com] Configuración:

```
□ Crear nuevo escenario
□ Agregar webhook (copiar URL)
□ Configurar 12 módulos según especificación
□ Agregar API Key de Claude
□ Agregar tu API Key secreta
□ Probar con JSON del agente
```

---

## ⏱️ TIEMPOS ESTIMADOS

| Paso | Tiempo |
|------|--------|
| Webhook recibe JSON | 0.5 seg |
| Validación | 0.5 seg |
| Preparar datos | 0.5 seg |
| Construir prompt | 0.5 seg |
| **Claude genera HTML** | **30-60 seg** |
| Validar HTML | 0.5 seg |
| Mejoras automáticas | 0.5 seg |
| Deploy a servidor | 2-3 seg |
| Verificar deploy | 1-2 seg |
| Guardar en DB | 0.5 seg |
| Enviar email | 1-2 seg |
| **TOTAL** | **40-70 seg** |

---

## 💰 COSTOS ESTIMADOS

| Recurso | Costo por página |
|---------|------------------|
| Claude API (~8K tokens output) | ~$0.024 |
| Make.com (12 operaciones) | ~$0.012 |
| **TOTAL** | **~$0.036** |

Con 100 páginas/mes: ~$3.60

---

## 🎯 RESULTADO ESPERADO

Con este flujo obtendrás:

1. **Página 90%+ lista** - Claude genera HTML completo y coherente
2. **Colores correctos** - Usa la paleta del JSON
3. **Contenido real** - Servicios, testimonios, contacto del negocio
4. **SEO básico** - Title, description, keywords
5. **Responsive** - Mobile-first incluido en el template
6. **WhatsApp funcional** - Links correctos
7. **Preview antes de publicar** - Staging para revisar
8. **Notificación por email** - Sabes cuando está listo

---

## 🚀 SIGUIENTE PASO

1. **Crear los 3 archivos PHP** (deploy-simple, save-website, approve)
2. **Subirlos al servidor** [FileZilla]
3. **Configurar Make.com** con los 12 módulos
4. **Probar con el JSON de Nefrovet**
5. **Verificar resultado**

¿Quieres que cree los archivos PHP ahora?
