# ⚡ Make.com - Guía Rápida Visual

**Para quien ya entiende los conceptos básicos.** Si eres principiante, usa `MAKE_COM_SETUP.md`

---

## 🎯 El Escenario Completo

```
┌─────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐
│Webhook  │→  │Set Vars │→  │HTTP GET │→  │GPT-4o   │→  │Parse    │
│         │   │         │   │ Image   │   │ Vision  │   │JSON     │
└─────────┘   └─────────┘   └─────────┘   └─────────┘   └─────────┘
                                                                ↓
┌─────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐
│ Email   │←  │Save     │←  │Replace  │←  │Get      │←  │Router   │
│  You    │   │ Files   │   │  HTML   │   │Template │   │3 Paths  │
└─────────┘   └─────────┘   └─────────┘   └─────────┘   └─────────┘
```

**Total:** 10 módulos | Tiempo: ~30 segundos por ejecución | Costo: ~$0.60 USD

---

## 📦 Módulos Necesarios

### Módulo 1: Webhooks - Custom webhook
```yaml
Configuración:
  - Nombre: "Cliente Web Generator"
  - URL: auto-generada
  - Conectar a Google Forms vía Apps Script
```

### Módulo 2: Tools - Set variable (x5)
```yaml
Variables:
  - nombre_negocio: {{1.`Nombre del negocio`}}
  - email_cliente: {{1.Email}}
  - slug_negocio: {{lower(replace(1.`Nombre del negocio`; " "; "-"))}}
  - foto_url: {{1.`Foto Principal`}}
  - tipo_sitio: {{1.`Tipo de sitio`}}
```

### Módulo 3: HTTP - Get a file
```yaml
URL: {{2.foto_url}}
Method: GET
Output: Imagen en memoria
```

### Módulo 4: OpenAI - Create a Chat Completion
```yaml
Model: gpt-4o
Response Format: json_object
Temperature: 0.7
Max Tokens: 2000

Messages:
  1. Role: System
     Content: "Eres experto en branding... (ver prompt completo abajo)"
  
  2. Role: User
     Content Type: Text with Vision
     Text: "Analiza esta imagen..."
     Image URL: {{3.data}} o {{3.url}}
```

**Prompt System Completo:**
```
Eres un experto en branding y diseño web. Analiza imágenes de negocios y extrae información clave para generar landing pages profesionales. Responde SOLO con JSON válido, sin markdown.
```

**Prompt User Completo:**
```
Analiza esta imagen de un negocio y genera este JSON:

{
  "tipo_negocio": "tipo detectado",
  "colores_principales": ["#hex1", "#hex2", "#hex3"],
  "ambiente": "moderno|clásico|minimalista|elegante",
  "titulo_hero": "Título impactante en mayúsculas (max 80 chars)",
  "subtitulo_hero": "Subtítulo descriptivo 20-30 palabras",
  "descripcion_about": "Descripción persuasiva 60-80 palabras",
  "caracteristicas": [
    {
      "icon": "emoji",
      "titulo": "Nombre (2-4 palabras)",
      "descripcion": "Breve (10-15 palabras)"
    }
  ],
  "cta_principal": "Call-to-action (2-4 palabras)",
  "keywords_seo": ["keyword1", "keyword2", "keyword3"]
}

REGLAS:
- JSON válido, sin markdown
- Colores en formato HEX válido
- Mínimo 3 características, máximo 6
- Textos en español neutro
```

### Módulo 5: JSON - Parse JSON
```yaml
JSON String: {{4.choices[1].message.content}}
Output: Objeto con todos los datos
```

### Módulo 6: Flow Control - Router

**Ruta 1: Landing Page**
```yaml
Filter: {{2.tipo_sitio}} = "Landing Page"
```

**Ruta 2: E-commerce**
```yaml
Filter: {{2.tipo_sitio}} = "E-commerce"
```

**Ruta 3: E-commerce + Auth**
```yaml
Filter: {{2.tipo_sitio}} = "E-commerce + Auth"
```

### Módulo 7: HTTP - Get a file (Template)
```yaml
URL Ruta 1: https://tu-servidor.com/templates/landing-basica/index.html
URL Ruta 2: https://tu-servidor.com/templates/ecommerce-completo/index.html
URL Ruta 3: https://tu-servidor.com/templates/ecommerce-auth/index.html
Method: GET
```

### Módulo 8: Tools - Replace (múltiples)
```yaml
Text: {{7.data}}

Reemplazos:
  {{PAGE_TITLE}} → {{2.nombre_negocio}}
  {{HERO_TITLE}} → {{5.titulo_hero}}
  {{HERO_SUBTITLE}} → {{5.subtitulo_hero}}
  {{ABOUT_DESCRIPTION}} → {{5.descripcion_about}}
  {{BRAND_NAME}} → {{2.nombre_negocio}}
  {{COLOR_PRIMARY}} → {{5.colores_principales[1]}}
  {{COLOR_SECONDARY}} → {{5.colores_principales[2]}}
  {{CTA_TEXT}} → {{5.cta_principal}}
  {{CURRENT_YEAR}} → {{formatDate(now; "YYYY")}}
```

### Módulo 9: FTP - Upload files (o Google Drive)

**Opción A: FTP (Hostinger)**
```yaml
Host: ftp.tudominio.com
Port: 21
Username: [variable de entorno]
Password: [variable de entorno]
Remote Path: /public_html/clientes/{{2.slug_negocio}}/
Files: [
  {name: "index.html", data: {{8.text}}},
  {name: "styles.css", data: [CSS]},
  {name: "script.js", data: [JS]}
]
Create directories: YES
```

**Opción B: Google Drive (más fácil)**
```yaml
Folder: "Sitios Generados"
Subfolder: {{2.slug_negocio}}
Files: Subir HTML, CSS, JS
Share: Get link
```

### Módulo 10: Email - Send an email
```yaml
To: tu@email.com
Subject: 🚀 Nuevo sitio - {{2.nombre_negocio}}

Body (HTML):
<h1>Sitio Generado</h1>
<p><strong>Cliente:</strong> {{2.nombre_negocio}}</p>
<p><strong>Email:</strong> {{2.email_cliente}}</p>
<p><strong>Tipo:</strong> {{2.tipo_sitio}}</p>

<h2>Análisis IA:</h2>
<p>Tipo: {{5.tipo_negocio}}</p>
<p>Ambiente: {{5.ambiente}}</p>
<p>Título: {{5.titulo_hero}}</p>

<h2>URL del Sitio:</h2>
<a href="https://tudominio.com/clientes/{{2.slug_negocio}}">
  Ver Sitio →
</a>

<h3>Próximos pasos:</h3>
<ol>
  <li>Revisa el sitio</li>
  <li>Ajusta si es necesario</li>
  <li>Envía al cliente</li>
</ol>
```

---

## 🔐 Variables de Entorno

Configurar en: Scenario Settings → Environment Variables

```yaml
FTP_HOST: ftp.tudominio.com
FTP_USER: tu_usuario
FTP_PASS: tu_password_seguro
OPENAI_API_KEY: sk-proj-...
TEMPLATE_BASE_URL: https://tudominio.com/templates/
```

---

## ⚠️ Error Handling

### Para GPT-4o (Módulo 4)
```
Error Handler → Resume
- Si falla: Reintentar 2 veces
- Wait: 5 segundos entre intentos
```

### Para FTP Upload (Módulo 9)
```
Error Handler → Break
- Si falla: Detener y notificar
```

### Para Email (Módulo 10)
```
Error Handler → Ignore
- Si falla: Continuar igual (no crítico)
```

---

## 🧪 Testing Rápido

### 1. Ejecutar con Datos de Prueba

Click derecho en Webhook → "Run this module only"

Pega este JSON de prueba:
```json
{
  "Nombre del negocio": "Test Café",
  "Email": "test@ejemplo.com",
  "Teléfono": "+5491112345678",
  "Tipo de sitio": "Landing Page",
  "Foto Principal": "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085",
  "Color Principal": "#6B4423",
  "Descripción": "Cafetería de especialidad"
}
```

### 2. Verificar Cada Módulo

- [ ] Webhook: Datos recibidos ✓
- [ ] Set Vars: Variables creadas ✓
- [ ] HTTP: Imagen descargada ✓
- [ ] GPT-4o: JSON generado ✓
- [ ] Parse: Objeto válido ✓
- [ ] Router: Ruta correcta ✓
- [ ] Replace: Placeholders OK ✓
- [ ] Upload: Archivos subidos ✓
- [ ] Email: Recibido ✓

---

## 💰 Costos por Ejecución

```
GPT-4o Vision:
- Input (imagen + prompt): ~1,000 tokens × $0.005 = $0.005
- Output (JSON): ~500 tokens × $0.015 = $0.0075
- Total GPT: ~$0.013

Make.com:
- 10 operaciones × $0.001 = $0.01

TOTAL: ~$0.023 USD por sitio generado
```

Con Plan Free (1,000 ops): **~100 sitios/mes**
Con Plan Pro (10,000 ops): **~1,000 sitios/mes**

---

## 🚀 Optimizaciones

### 1. Caché de Templates
En vez de descargar cada vez, guárdalos en Make.com Data Store

### 2. Batch Processing
Procesar varios formularios a la vez (útil si tienes cola)

### 3. Webhook Validation
Agregar token secreto en el webhook para seguridad

### 4. Fallbacks
Si GPT-4o falla, usar templates por defecto

---

## 📊 Monitoreo

### Dashboard de Make.com

```
Scenarios → Tu escenario → History

Ver:
- Ejecuciones totales
- Éxitos / Errores
- Tiempo promedio
- Operaciones usadas
```

### Notificaciones

Configurar alertas:
- Si error rate > 10%
- Si uso > 80% del plan
- Si tiempo > 60 segundos

---

## 🆘 Troubleshooting Común

### Error: "Invalid API Key"
```
Solución:
1. Ve a OpenAI → API Keys
2. Verifica que tenga crédito ($5 mínimo)
3. Crea nueva key si es necesario
4. Actualiza en Make.com
```

### Error: "FTP Connection Failed"
```
Solución:
1. Verifica credenciales FTP
2. Prueba conectar con FileZilla primero
3. Revisa que puerto sea 21
4. Considera usar SFTP (puerto 22)
```

### Error: "JSON Parse Failed"
```
Solución:
1. GPT-4o retornó texto en vez de JSON
2. Agrega en system prompt: "Responde SOLO JSON"
3. Usa Response Format: json_object
4. Aumenta temperature si es muy restrictivo
```

### GPT-4o Genera Contenido Malo
```
Solución:
1. Mejora la calidad de la imagen input
2. Ajusta el prompt (más específico)
3. Aumenta max_tokens a 3000
4. Prueba con otra imagen similar
```

---

## ✅ Checklist Pre-Lanzamiento

- [ ] OpenAI API Key configurada y con crédito
- [ ] FTP/Hosting funcionando
- [ ] Templates subidos al servidor
- [ ] Google Form listo con webhook
- [ ] Escenario probado con 3+ tests
- [ ] Email de notificación llegando
- [ ] Sitio generado accesible en navegador
- [ ] Responsive design funcional
- [ ] Todos los placeholders reemplazados
- [ ] Variables de entorno configuradas
- [ ] Error handlers activados

---

## 🎉 ¡Listo!

Tu sistema ya está operativo. Cada vez que alguien complete el formulario:

1. Make.com se ejecuta automáticamente ⚡
2. GPT-4o genera el contenido 🤖
3. Sitio se sube a tu servidor 💾
4. Recibes email con el link 📧
5. Total: 30 segundos ⏱️

**Siguiente paso:** Testea con formulario real y ajusta según necesites.

---

**¿Problemas? Revisa la guía detallada en `MAKE_COM_SETUP.md`**
