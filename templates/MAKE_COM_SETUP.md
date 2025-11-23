# 🔧 Configuración Completa de Make.com - Para Principiantes

Guía **SÚPER DETALLADA** paso a paso para configurar la automatización.
**Asume CERO conocimiento previo de Make.com.**

---

## 📋 Prerrequisitos

Antes de empezar, necesitas tener:

### 1. Cuenta en Make.com
- ✅ Crea cuenta gratis en: https://make.com
- ✅ Plan GRATIS funciona (1,000 operaciones/mes = ~30 sitios)
- ✅ Plan PRO ($9/mes): 10,000 ops = ~300 sitios (recomendado después)

### 2. API Key de OpenAI
- ✅ Cuenta en: https://platform.openai.com
- ✅ Agregar $5-10 USD de crédito
- ✅ Crear API Key (la necesitarás después)

### 3. Lugar donde Subir Sitios (Elige UNO)
**Opción A:** Hostinger FTP (si ya tienes hosting)
**Opción B:** GitHub Pages (gratis, más fácil)
**Opción C:** Google Drive (para empezar rápido)

### 4. Google Form Listo
- ✅ Ya lo tienes creado
- ✅ Ya configuraste el webhook (✓ paso completado)

---

## 🎯 ¿Qué Vamos a Construir?

Un **escenario** (=automatización) que hace esto:

```
📝 Google Form → 🔗 Webhook → 🤖 GPT-4o Vision → 📄 HTML → 💾 Guardar → 📧 Email
```

**En español:**
1. Usuario llena formulario
2. Datos llegan a Make.com automáticamente
3. GPT-4o analiza fotos y genera contenido
4. Make.com arma el HTML completo
5. Sube archivos a tu servidor
6. Te envía email con el sitio listo

**Tiempo:** ~30 segundos por sitio

---

## 📚 Conceptos Básicos de Make.com

### ¿Qué es un "Scenario"?
Es una automatización. Como una receta de cocina: pasos en orden.

### ¿Qué es un "Module"?
Cada paso de la receta. Ejemplos:
- Módulo "Webhook" = recibir datos
- Módulo "OpenAI" = llamar a GPT-4o
- Módulo "HTTP" = descargar archivo

### ¿Cómo se Conectan?
Con flechas. Los datos fluyen de izquierda a derecha:
```
Módulo 1 → Módulo 2 → Módulo 3
```

### ¿Cómo se Referencian Datos?
Con llaves dobles: `{{1.nombre}}` = dato "nombre" del Módulo 1

---

## ✅ PASO 1: Webhook (YA LO HICISTE)

**Estado:** ✓ Completado

Ya tienes:
- ✓ Webhook creado
- ✓ URL copiada
- ✓ Google Form conectado
- ✓ Datos llegando a Make.com

**Tu webhook recibe esto del formulario:**
```json
{
  "Nombre del negocio": "Cafetería Ejemplo",
  "Email": "cliente@ejemplo.com",
  "Teléfono": "+54911XXXXXXXX",
  "Tipo de sitio": "Landing Page",
  "Logo": "https://url-imagen-logo.jpg",
  "Foto Principal": "https://url-imagen.jpg",
  "Color Principal": "#667eea",
  "Descripción": "Cafetería moderna..."
}
```

**Siguiente paso:** Procesar esos datos

---

## 📝 PASO 2: Limpiar y Organizar Datos

**¿Para qué?** Los datos del formulario vienen "sucios". Este paso los organiza.

### Agregar el Módulo

```
1. Click en el círculo pequeño a la DERECHA del webhook
   (aparece cuando pasas el mouse)
2. Se abre el menú de apps
3. Busca: "Tools"
4. Click en "Tools"
5. Selecciona: "Set variables" ← ⚠️ IMPORTANTE: CON "S" AL FINAL (PLURAL)
```

⚠️ **NO confundir:**
- ❌ "Set variable" (singular) = una sola variable
- ✅ "Set variables" (plural) = múltiples variables ← USA ESTE

### Configurar Variables

Vas a crear 5 variables en este módulo usando "+ Add item":

**Variable 1: Nombre Limpio**
```
Variable name: nombre_negocio
Variable value: {{1.`Nombre del negocio`}}
```
👆 Nota: Si el campo del form tiene espacios, usa backticks (`) o busca en la lista

**Variable 2: Email**
```
Variable name: email_cliente  
Variable value: {{1.Email}}
```

**Variable 3: Slug (para URL)**
```
Variable name: slug_negocio
Variable value: {{lower(replace(1.`Nombre del negocio`; " "; "-"))}}
```
👆 Esto convierte "Mi Tienda" → "mi-tienda" (para URLs)

**Variable 4: URL de Foto**
```
Variable name: foto_url
Variable value: {{1.`Foto Principal`}}
```

**Variable 5: Tipo de Sitio**
```
Variable name: tipo_sitio
Variable value: {{1.`Tipo de sitio`}}
```

**Después de agregar las 5, se verá así:**
```
┌─────────────────────────────────────┐
│ Set variables                       │
├─────────────────────────────────────┤
│ Variables                           │
│                                     │
│ 1. nombre_negocio    {{1.`Nombre...│
│ 2. email_cliente     {{1.Email}}   │
│ 3. slug_negocio      {{lower(re... │
│ 4. foto_url          {{1.`Foto ... │
│ 5. tipo_sitio        {{1.`Tipo ... │
│                                     │
│ [+ Add item]                        │
│                                     │
│     [Cancel]              [OK]      │
└─────────────────────────────────────┘
```

Click "OK" para guardar todas.

### ¿Cómo Acceder a los Datos del Módulo 1?

```
Cuando escribas {{  aparecerá un menú:

┌────────────────────────────┐
│ 1. Webhooks                │ ← Click aquí
├────────────────────────────┤
│ • Nombre del negocio       │
│ • Email                    │
│ • Teléfono                 │
│ • Foto Principal           │
│ ...                        │
└────────────────────────────┘

Click en el campo que necesitas
```

**Resultado:** Ahora tienes los datos organizados en el Módulo 2

---

## 🖼️ PASO 3: Descargar la Imagen del Negocio

**¿Para qué?** GPT-4o necesita ver la imagen. La descargamos a Make.com.

### Agregar Módulo HTTP

```
1. Click en el círculo a la derecha del Módulo 2
2. Busca: "HTTP"
3. Click en "HTTP"
4. Selecciona: "Get a file" (no "Make a request")
```

### Configurar el Módulo

**Pantalla de configuración:**
```
┌─────────────────────────────────────┐
│ Get a file                          │
├─────────────────────────────────────┤
│ URL *                               │
│ [                              ]    │
│                                     │
│ Method                              │
│ [GET                          ▼]    │
└─────────────────────────────────────┘
```

**URL:** Click en el campo y escribe `{{` luego:
```
1. Aparece menú → Click "2. Tools"
2. Click "foto_url"
3. Queda: {{2.foto_url}}
```

**Method:** Déjalo en GET (por defecto)

Click "OK"

**Resultado:** La imagen ahora está en Make.com (Módulo 3)

---

## 🤖 PASO 4: GPT-4o Vision Analiza la Imagen

**¿Para qué?** La IA analiza la foto del negocio y genera TODO el contenido del sitio.

### 1. Agregar Módulo OpenAI

```
1. Click en el círculo a la derecha del Módulo 3 (HTTP)
2. Busca: "OpenAI"
3. Click en "OpenAI"
4. Selecciona: "Create a Chat Completion"
```

### 2. Conectar tu API Key (PRIMERA VEZ)

```
Se abre configuración:
┌─────────────────────────────────────┐
│ Connection                          │
│ [No connection yet]         [Add]   │
└─────────────────────────────────────┘

Click "Add"
```

Aparece modal:
```
┌─────────────────────────────────────────────┐
│ Create a connection                         │
├─────────────────────────────────────────────┤
│ Connection name:                            │
│ [My OpenAI                         ]        │
│                                             │
│ API Key: *                                  │
│ [                                  ]        │
│                                             │
│     [Cancel]              [Save]            │
└─────────────────────────────────────────────┘
```

**API Key:** 
1. Abre nueva pestaña: https://platform.openai.com/api-keys
2. Click "Create new secret key"
3. Copia la key (empieza con sk-...)
4. Pégala en Make.com
5. Click "Save"

### 3. Configurar el Modelo

```
┌─────────────────────────────────────┐
│ Model *                             │
│ [Select model               ▼]      │
└─────────────────────────────────────┘

Click en el dropdown
Busca y selecciona: "gpt-4o"
(NO "gpt-4" sin la "o", necesitas Vision)
```

### 4. Configurar Mensajes

**Verás sección "Messages":**
```
┌─────────────────────────────────────────┐
│ Messages                                │
│                                         │
│ [+ Add item]                            │
└─────────────────────────────────────────┘
```

#### Mensaje 1: System (Instrucciones para la IA)

```
1. Click "+ Add item"
2. Role: [System        ▼]
3. Message Content: [Text ▼]
```

Copia y pega este texto en "Message Content":
```
Eres un experto en branding y diseño web. Analiza imágenes de negocios y extrae información clave para generar landing pages profesionales. Siempre respondes en formato JSON válido sin markdown.

{
  "tipo_negocio": "Identifica el tipo exacto (ej: cafetería, gimnasio, restaurante, tienda, consultorio, etc)",
  "subtipo": "Más específico si es posible (ej: cafetería de especialidad, gimnasio crossfit)",
  "colores_principales": ["Color hex 1", "Color hex 2", "Color hex 3"],
  "colores_complementarios": ["Color hex 1", "Color hex 2"],
  "ambiente": "moderno | clásico | minimalista | elegante | casual | industrial | vintage",
  "publico_objetivo": "Describe el público objetivo detectado",
  "titulo_hero": "Título impactante en mayúsculas (MAX 80 caracteres, sin puntuación final)",
  "subtitulo_hero": "Subtítulo descriptivo de 20-30 palabras que complemente el título",
  "descripcion_about": "Descripción persuasiva del negocio (60-80 palabras). Debe sonar profesional y convincente.",
  "caracteristicas": [
    {
      "icon": "emoji apropiado",
      "titulo": "Nombre característic (2-4 palabras)",
      "descripcion": "Explicación breve (10-15 palabras)"
    }
  ],
  "trust_badges": [
    "Elemento de confianza 1",
    "Elemento de confianza 2",
    "Elemento de confianza 3"
  ],
  "cta_principal": "Texto call-to-action sugerido (2-4 palabras)",
  "cta_secundario": "CTA alternativo",
  "emoji_logo": "Emoji apropiado para usar como logo",
  "keywords_seo": ["keyword1", "keyword2", "keyword3", "keyword4", "keyword5"],
  "meta_description": "Meta description SEO-optimized (MAX 160 caracteres)"
}

REGLAS IMPORTANTES:
- Colores deben ser HEX válidos (#RRGGBB)
- Características: mínimo 3, máximo 6
- Trust badges: exactamente 3
- Títulos y textos en español neutro
- JSON válido sin caracteres de escape innecesarios
- No incluyas markdown, solo JSON puro
```

**Image part:**
```
URL: {{3.url}}
```

O si usaste base64:
```
URL: {{4.output}}
```

**Response Format**: `json_object`

**Temperature**: `0.7`

**Max Tokens**: `2000`

---

## Paso 6: Parsear Respuesta de GPT-4o

**Módulo**: JSON > Parse JSON

**JSON String:**
```
{{5.choices[].message.content}}
```

**Output**: Objeto JSON con todos los datos extraídos

---

## Paso 7: Router - Seleccionar Template

**Módulo**: Flow Control > Router

### Ruta 1: Landing Page Básica
**Filter:**
```javascript
{{6.tipo_negocio}} contains "servicio" OR
{{6.tipo_negocio}} contains "consultorio" OR
{{6.tipo_negocio}} contains "profesional" OR
{{2.tipo_web}} = "landing"
```

### Ruta 2: E-commerce
**Filter:**
```javascript
{{6.tipo_negocio}} contains "tienda" OR
{{6.tipo_negocio}} contains "shop" OR
{{2.tipo_web}} = "ecommerce"
```

### Ruta 3: Blog/Contenido
**Filter:**
```javascript
{{2.tipo_web}} = "blog"
```

---

## Paso 8: Leer Template Base

**Módulo**: HTTP > Get a file

**Para Ruta 1 (Landing):**
```
URL: https://tu-repositorio.com/templates/landing-basica/index.html
Method: GET
```

**Output**: Contenido HTML del template

---

## Paso 9: Reemplazar Variables en HTML

**Módulo**: Tools > Text parser > Replace

Configurar múltiples reemplazos:

| Pattern (Buscar) | Replace With (Reemplazar con) |
|------------------|-------------------------------|
| `{{PAGE_TITLE}}` | `{{2.nombre_negocio}}` |
| `{{PAGE_DESCRIPTION}}` | `{{6.meta_description}}` |
| `{{HERO_IMAGE}}` | `{{3.url}}` |
| `{{HERO_BADGE}}` | `{{6.emoji_logo}} {{6.publico_objetivo}}` |
| `{{HERO_TITLE}}` | `{{6.titulo_hero}}` |
| `{{HERO_SUBTITLE}}` | `{{6.subtitulo_hero}}` |
| `{{CTA_PRIMARY_TEXT}}` | `{{6.cta_principal}}` |
| `{{CTA_SECONDARY_TEXT}}` | `{{6.cta_secundario}}` |
| `{{FEATURES_TITLE}}` | `¿Por Qué Elegir {{2.nombre_negocio}}?` |
| `{{ABOUT_TITLE}}` | `Sobre {{2.nombre_negocio}}` |
| `{{ABOUT_DESCRIPTION}}` | `{{6.descripcion_about}}` |
| `{{BRAND_NAME}}` | `{{2.nombre_negocio}}` |
| `{{CURRENT_YEAR}}` | `{{formatDate(now; "YYYY")}}` |

**Para Características (requiere Iterator):**

Usar módulo **Iterator** + **Aggregator** para construir el HTML de características:

```html
<div class="feature-card">
  <div class="feature-icon">{{6.caracteristicas[].icon}}</div>
  <h3>{{6.caracteristicas[].titulo}}</h3>
  <p>{{6.caracteristicas[].descripcion}}</p>
</div>
```

---

## Paso 10: Reemplazar Variables en CSS

**Módulo**: HTTP > Get a file
```
URL: https://tu-repositorio.com/templates/landing-basica/styles.css
```

**Módulo**: Tools > Text parser > Replace

| Pattern | Replace With |
|---------|--------------|
| `--brand-primary: #667eea;` | `--brand-primary: {{6.colores_principales[1]}};` |
| `--brand-secondary: #764ba2;` | `--brand-secondary: {{6.colores_principales[2]}};` |

---

## Paso 11: Construir Estructura de Archivos

**Módulo**: Tools > Create Bundle (Array Aggregator)

Crear array con todos los archivos:

```javascript
[
  {
    "path": "/index.html",
    "content": {{9.text}}  // HTML procesado
  },
  {
    "path": "/styles.css",
    "content": {{10.text}}  // CSS procesado
  },
  {
    "path": "/script.js",
    "content": {{HTTP_GET_script}}
  }
]
```

---

## Paso 12: Subir a Hostinger vía FTP

**Módulo**: FTP > Upload files

**Configuración:**
```
Host: ftp.tudominio.com
Port: 21
Username: {{env.FTP_USER}}
Password: {{env.FTP_PASS}}
Remote Directory: /public_html/clientes/{{2.slug_negocio}}/
```

**Files**: Usar el bundle del Paso 11

**Create directories**: YES

---

## Paso 13: Generar URL del Sitio

**Módulo**: Tools > Set variable

```javascript
site_url = "https://tudominio.com/clientes/" + {{2.slug_negocio}}
```

---

## Paso 14: Enviar Email de Notificación (A TI, no al cliente)

**Módulo**: Email > Send an email (Gmail, SendGrid, etc.)

**To**: `tu@email.com` (TU EMAIL, no el del cliente)

**Subject**: `🚀 Nuevo sitio generado - {{2.nombre_negocio}}`

**Body (HTML):**
```html
<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px; }
    .content { padding: 30px 0; }
    .info-box { background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0; }
    .btn { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
    .color-box { display: inline-block; width: 40px; height: 40px; border-radius: 5px; margin: 0 5px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🎉 Nuevo Sitio Web Generado</h1>
    </div>
    
    <div class="content">
      <h2>Revisa y envía al cliente</h2>
      
      <div class="info-box">
        <h3>📋 Datos del Cliente:</h3>
        <p><strong>Negocio:</strong> {{2.nombre_negocio}}</p>
        <p><strong>Email:</strong> {{2.email_cliente}}</p>
        <p><strong>Teléfono:</strong> {{2.telefono}}</p>
        <p><strong>Tipo de sitio:</strong> {{2.tipo_web}}</p>
      </div>
      
      <div class="info-box">
        <h3>🌐 URL del Sitio Generado:</h3>
        <p><a href="{{13.site_url}}" class="btn" target="_blank">Ver Sitio Generado</a></p>
        <p style="font-size: 14px; color: #666;">{{13.site_url}}</p>
      </div>
      
      <div class="info-box">
        <h3>🎨 Análisis de IA (GPT-4o Vision):</h3>
        <p><strong>Tipo de negocio detectado:</strong> {{6.tipo_negocio}}</p>
        <p><strong>Ambiente:</strong> {{6.ambiente}}</p>
        <p><strong>Título generado:</strong> {{6.titulo_hero}}</p>
        <p><strong>Colores extraídos:</strong></p>
        <div>
          <span class="color-box" style="background: {{6.colores_principales[1]}};"></span>
          <span class="color-box" style="background: {{6.colores_principales[2]}};"></span>
          <span class="color-box" style="background: {{6.colores_principales[3]}};"></span>
        </div>
        <p style="font-size: 12px; color: #666;">
          {{6.colores_principales[1]}} | {{6.colores_principales[2]}} | {{6.colores_principales[3]}}
        </p>
      </div>
      
      <div class="info-box">
        <h3>✅ Próximos pasos:</h3>
        <ol>
          <li>Revisa el sitio generado en el link de arriba</li>
          <li>Verifica que todo se vea bien</li>
          <li>Si está OK, envía el link al cliente manualmente</li>
          <li>Si necesita ajustes, edita y vuelve a subir</li>
        </ol>
      </div>
      
      <p><strong>Nota:</strong> El sitio ya está publicado y funcionando. Solo falta que tú lo revises y lo envíes al cliente.</p>
      
      <p style="margin-top: 30px; font-size: 12px; color: #666;">
        Generado automáticamente por tu sistema de templates IA
      </p>
    </div>
  </div>
</body>
</html>
```

---

## Paso 15: Notificación Interna (Opcional)

**Módulo**: Slack > Send a message

**Channel**: `#web-generadas`

**Message**:
```
🎉 Nuevo sitio generado!

📌 Cliente: {{2.nombre_negocio}}
🌐 URL: {{13.site_url}}
🎨 Tipo: {{6.tipo_negocio}}
📧 Email: {{2.email_cliente}}
⏱️ Generado: {{formatDate(now; "DD/MM/YYYY HH:mm")}}

Colores detectados: {{6.colores_principales[1]}} | {{6.colores_principales[2]}}
```

---

## 🔒 Variables de Entorno

Configurar en Make.com > Scenario Settings > Environment Variables:

```
FTP_HOST = ftp.tudominio.com
FTP_USER = tu_usuario_ftp
FTP_PASS = tu_password_seguro
OPENAI_API_KEY = sk-proj-xxxxxxxxxxxxx
TEMPLATE_REPO_URL = https://github.com/tu-repo/templates
EMAIL_FROM = noreply@tudominio.com
```

---

## ⚙️ Configuración de Error Handling

Para cada módulo crítico, añadir **Error Handler**:

### Break
- Si falla GPT-4o Vision
- Si falla FTP upload

### Ignore
- Si email falla (no es crítico)

### Rollback
- Eliminar archivos parciales en FTP si falla algo después del upload

---

## 📊 Testing del Scenario

### Test Data

```json
{
  "nombre_negocio": "Test Café",
  "email": "test@ejemplo.com",
  "telefono": "+5491112345678",
  "tipo_web": "landing",
  "foto_principal": "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085",
  "descripcion_adicional": "Cafetería de especialidad"
}
```

### Checklist de Testing

- [ ] Webhook recibe datos correctamente
- [ ] GPT-4o Vision analiza imagen
- [ ] JSON parseado sin errores
- [ ] Variables reemplazadas en HTML
- [ ] Colores actualizados en CSS
- [ ] Archivos subidos a FTP correctamente
- [ ] Email enviado al cliente
- [ ] Sitio accesible en navegador
- [ ] Sitio responsive en móvil

---

## 💰 Costos Estimados

Por cada generación:

| Servicio | Costo Unitario |
|----------|----------------|
| GPT-4o Vision (1 imagen + análisis) | ~$0.50 USD |
| Make.com (operaciones) | ~$0.10 USD |
| Almacenamiento Hostinger | Incluido |
| Email (SendGrid/Gmail) | Gratis / $0.01 |
| **TOTAL** | **~$0.60 USD** |

Con Make.com Plan Pro (10,000 ops/mes): **~150-200 sitios por mes**

---

## 🚀 Optimizaciones

### Caché de Templates
Almacenar templates en Make.com Data Store para evitar leerlos cada vez.

### Batch Processing
Procesar múltiples solicitudes en lote (útil si tienes muchos pedidos).

### Webhooks Avanzados
Implementar validación y autenticación en el webhook.

---

## 📞 Soporte

Si tienes problemas con la configuración:

1. Revisa logs en Make.com
2. Verifica variables de entorno
3. Testea cada módulo individualmente
4. Contacta soporte de Make.com si es necesario

---

**¡Listo! Tu sistema de generación automática está configurado 🎉**
