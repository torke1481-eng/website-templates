# 🚀 Sistema de Templates Modulares para Make.com + GPT-4o Vision

Sistema automatizado de generación de sitios web mediante análisis de imágenes con IA y personalización automática.

## 📋 Índice

1. [Descripción General](#descripción-general)
2. [Estructura del Proyecto](#estructura-del-proyecto)
3. [Templates Disponibles](#templates-disponibles)
4. [Componentes Globales](#componentes-globales)
5. [Flujo de Automatización Make.com](#flujo-de-automatización-makecom)
6. [Guía de Uso](#guía-de-uso)
7. [Personalización con GPT-4o Vision](#personalización-con-gpt-4o-vision)
8. [Deployment a Hostinger](#deployment-a-hostinger)

---

## 🎯 Descripción General

Este sistema permite **generar sitios web personalizados automáticamente** mediante:

- **Formulario de cliente** → Datos básicos del negocio
- **Upload de foto** → Imagen del negocio/local/producto
- **GPT-4o Vision** → Analiza la imagen y extrae:
  - Tipo de negocio
  - Colores dominantes
  - Estilo/ambiente
  - Genera contenido persuasivo
- **Make.com** → Automatiza todo el proceso
- **Tú revisas** → Validas el resultado
- **Envías al cliente** → Entregas el sitio personalizado

### ⚡ Tiempo de Generación
- **Manual**: 8-12 horas
- **Con este sistema**: 10-15 minutos (generación + revisión)

### 💰 Costo por Generación
- **GPT-4o Vision API**: ~$0.50 - $1.00 USD
- **Make.com**: Plan Pro recomendado
- **Hostinger**: Hosting existente

---

## 📁 Estructura del Proyecto

```
templates/
│
├── componentes-globales/          # Componentes reutilizables
│   ├── header/
│   │   ├── header.html
│   │   ├── header-styles.css
│   │   ├── header-script.js
│   │   └── config.json
│   │
│   ├── footer/
│   │   ├── footer.html
│   │   ├── footer-styles.css
│   │   └── config.json
│   │
│   ├── chatbot/
│   │   ├── chatbot.html
│   │   ├── chatbot-styles.css
│   │   ├── chatbot-script.js
│   │   └── config.json
│   │
│   ├── carrito/                   # Para e-commerce
│   └── productos/                 # Grid de productos
│
├── landing-basica/                # ✅ TEMPLATE 1
│   ├── index.html
│   ├── styles.css
│   ├── script.js
│   └── config.json
│
├── ecommerce-completo/            # TEMPLATE 2 (próximo)
├── ecommerce-auth/                # TEMPLATE 3 (próximo)
├── blog-contenido/                # TEMPLATE 4 (próximo)
├── servicios-profesionales/       # TEMPLATE 5 (próximo)
│
└── README.md                      # Este archivo
```

---

## 🎨 Templates Disponibles

### ✅ Template 1: Landing Page Básica

**Ideal para:**
- Servicios profesionales (abogados, médicos, consultores)
- Eventos y conferencias
- Lanzamientos de productos
- Captación de leads
- Portfolios

**Incluye:**
- ✅ Header moderno con navegación
- ✅ Hero section con CTA
- ✅ Sección de características/beneficios
- ✅ Sobre nosotros
- ✅ CTA final
- ✅ Footer
- ⚪ Chatbot (opcional)

**Variables personalizables:** 25+
**Tiempo de generación:** 10 minutos (automático) + tu revisión

---

### 🔜 Template 2: E-commerce Completo

**Ideal para:**
- Tiendas online de ropa, tecnología, alimentos
- Productos artesanales
- Retail en general

**Incluirá:**
- ✅ Todo del Template 1
- ✅ Catálogo de productos con filtros
- ✅ Carrito de compras
- ✅ Sistema de categorías
- ✅ Chatbot con FAQs de compra

---

### 🔜 Template 3: E-commerce + Autenticación

**Ideal para:**
- Tiendas con membresías
- Suscripciones
- Programas de fidelización

**Incluirá:**
- ✅ Todo del Template 2
- ✅ Sistema de login/registro
- ✅ Perfil de usuario
- ✅ Historial de compras

---

## 🧩 Componentes Globales

Todos los templates reutilizan estos componentes:

### 1. Header Moderno
- Sticky header con glassmorphism
- Mega menú (opcional)
- Búsqueda expandible
- Responsive mobile menu
- **Variables:** 8
- **Colores personalizables:** Sí

### 2. Footer
- Multi-columna (3-4 columnas)
- Redes sociales
- Copyright automático
- **Variables:** 10+
- **Layouts:** 3 opciones

### 3. Chatbot Flotante
- FAQs expandibles
- Respuestas automáticas
- Input de mensajes
- **Variables:** 5
- **FAQs personalizables por IA:** Sí

---

## 🔄 Flujo de Automatización Make.com

### Diagrama de Flujo

```
1. FORMULARIO CLIENTE
   ├─ Nombre del negocio
   ├─ Email / Teléfono
   ├─ Tipo de web (landing / e-commerce / blog)
   └─ Fotos del negocio (1-3 imágenes)
   
2. WEBHOOK MAKE.COM
   ├─ Recibe datos del formulario
   └─ Descarga imágenes
   
3. GPT-4o VISION ANÁLISIS
   ├─ Envía imagen principal a OpenAI API
   ├─ Prompt: "Analiza negocio y extrae datos..."
   └─ Recibe JSON con:
       ├─ Tipo de negocio
       ├─ Colores dominantes (hex)
       ├─ Ambiente/estilo
       ├─ Título sugerido
       ├─ Descripción generada
       ├─ Características (3-6)
       └─ CTAs sugeridos
   
4. SELECCIÓN DE TEMPLATE
   ├─ Si tipo = "servicio" → landing-basica
   ├─ Si tipo = "tienda" → ecommerce-completo
   └─ Si tiene = "blog" → blog-contenido
   
5. PERSONALIZACIÓN
   ├─ Mapear JSON de IA → Variables del template
   ├─ Reemplazar {{VARIABLES}} en HTML
   ├─ Actualizar :root CSS con colores
   └─ Incluir componentes necesarios
   
6. GENERACIÓN DE ARCHIVOS
   ├─ index.html personalizado
   ├─ styles.css con colores únicos
   ├─ Copiar scripts necesarios
   └─ Optimizar imágenes
   
7. DEPLOYMENT VÍA FTP
   ├─ Conectar a Hostinger FTP
   ├─ Crear carpeta: /clientes/nombre-negocio/
   ├─ Subir archivos generados
   └─ Configurar permisos
   
8. NOTIFICACIÓN AL DESARROLLADOR (TÚ)
   ├─ Email con:
   │   ├─ URL del sitio generado
   │   ├─ Datos del cliente
   │   ├─ Información extraída por IA
   │   └─ Link para revisión
   └─ Tú revisas y envías al cliente manualmente
```

---

## 📖 Guía de Uso

### Paso 1: Configurar Formulario

Crear formulario web (TypeForm, Google Forms, o custom) con campos:

```javascript
{
  "nombre_negocio": "string",
  "email": "email",
  "telefono": "string",
  "tipo_web": "dropdown [landing | ecommerce | blog | servicios]",
  "fotos": "file_upload (max 3 imágenes)",
  "descripcion_adicional": "textarea (opcional)"
}
```

### Paso 2: Configurar Webhook en Make.com

1. Crear nuevo escenario en Make.com
2. Añadir módulo **Webhook > Custom Webhook**
3. Copiar URL del webhook
4. Configurar en formulario para enviar datos a esa URL

### Paso 3: Configurar GPT-4o Vision

Módulo **OpenAI > Create a Chat Completion**

**Configuración:**
```json
{
  "model": "gpt-4o",
  "messages": [
    {
      "role": "system",
      "content": "Eres un experto en branding y diseño web..."
    },
    {
      "role": "user",
      "content": [
        {
          "type": "text",
          "text": "Analiza esta imagen de un negocio y proporciona..."
        },
        {
          "type": "image_url",
          "image_url": {
            "url": "{{IMAGEN_CLIENTE}}"
          }
        }
      ]
    }
  ],
  "response_format": { "type": "json_object" }
}
```

### Paso 4: Procesar Respuesta IA

Parsear JSON respuesta:

```javascript
// Make.com módulo: JSON > Parse JSON
{
  "tipo_negocio": "...",
  "colores_principales": ["#hex1", "#hex2"],
  "titulo_hero": "...",
  "subtitulo_hero": "...",
  "descripcion_about": "...",
  "caracteristicas": [...]
}
```

### Paso 5: Reemplazar Variables

Módulo **Text Parser > Replace**

Para cada archivo del template:

```javascript
// Leer template original
let html = leerArchivo('templates/landing-basica/index.html');

// Reemplazar variables
html = html.replace('{{PAGE_TITLE}}', datos.nombre_negocio);
html = html.replace('{{HERO_TITLE}}', respuestaIA.titulo_hero);
html = html.replace('{{HERO_SUBTITLE}}', respuestaIA.subtitulo_hero);
// ... continuar con todas las variables

// Guardar archivo personalizado
guardarArchivo(html);
```

### Paso 6: Actualizar CSS

```javascript
// Leer CSS
let css = leerArchivo('templates/landing-basica/styles.css');

// Reemplazar colores
css = css.replace('--brand-primary: #667eea;', 
                 `--brand-primary: ${respuestaIA.colores_principales[0]};`);
css = css.replace('--brand-secondary: #764ba2;', 
                 `--brand-secondary: ${respuestaIA.colores_principales[1]};`);

guardarArchivo(css);
```

### Paso 7: Subir a Hostinger

Módulo **FTP > Upload a file**

**Configuración FTP:**
```
Host: ftp.tudominio.com
Port: 21
Username: tu_usuario_ftp
Password: tu_password_ftp
Remote path: /public_html/clientes/{{NOMBRE_NEGOCIO}}/
```

---

## 🤖 Personalización con GPT-4o Vision

### Prompt Optimizado

```
Eres un experto en branding y diseño web. Analiza la imagen del negocio proporcionada y extrae información clave para generar una landing page profesional.

Analiza esta imagen de un negocio/local y proporciona la siguiente información en formato JSON:

1. tipo_negocio: Identifica el tipo exacto (restaurante, gimnasio, tienda, consultorio, cafetería, etc)
2. colores_principales: Array con 3 colores hex dominantes extraídos de la imagen
3. ambiente: Describe el ambiente (moderno/clásico/minimalista/elegante/casual/juvenil)
4. titulo_hero: Genera un título impactante en mayúsculas (max 80 caracteres)
5. subtitulo_hero: Genera subtítulo descriptivo (20-30 palabras)
6. descripcion_about: Descripción persuasiva del negocio (60-80 palabras)
7. caracteristicas: Array de 3-6 características/beneficios principales con:
   - icon: emoji apropiado
   - titulo: Nombre de la característica (2-4 palabras)
   - descripcion: Breve explicación (10-15 palabras)
8. trust_badges: Array de 3 elementos que generen confianza
9. cta_principal: Texto sugerido para call-to-action principal
10. emoji_logo: Sugiere un emoji apropiado para usar como logo

IMPORTANTE: Responde SOLO con JSON válido, sin markdown ni explicaciones adicionales.
```

### Ejemplo de Respuesta GPT-4o

```json
{
  "tipo_negocio": "cafetería moderna",
  "colores_principales": ["#8B4513", "#D4A373", "#F5E6D3"],
  "ambiente": "moderno-acogedor",
  "titulo_hero": "EL MEJOR CAFÉ ARTESANAL DE LA CIUDAD",
  "subtitulo_hero": "Disfruta de granos seleccionados, tostados diariamente y preparados por baristas expertos en un ambiente único",
  "descripcion_about": "Somos una cafetería boutique dedicada al arte del café de especialidad. Seleccionamos los mejores granos de origen único, los tostamos en casa y los preparamos con técnicas de barismo premiadas. Cada taza es una experiencia sensorial única en un espacio diseñado para tu comodidad.",
  "caracteristicas": [
    {
      "icon": "☕",
      "titulo": "Café de Origen",
      "descripcion": "Granos seleccionados de las mejores fincas del mundo"
    },
    {
      "icon": "🏆",
      "titulo": "Baristas Premiados",
      "descripcion": "Equipo certificado con reconocimientos internacionales"
    },
    {
      "icon": "🌿",
      "titulo": "100% Sostenible",
      "descripcion": "Comercio justo y prácticas eco-friendly en toda nuestra cadena"
    },
    {
      "icon": "📍",
      "titulo": "Ubicación Prime",
      "descripcion": "En el corazón de la ciudad con espacio para trabajar"
    }
  ],
  "trust_badges": [
    "✓ Café certificado orgánico",
    "✓ Tostado diario",
    "✓ Wi-Fi gratis"
  ],
  "cta_principal": "Visítanos Hoy",
  "emoji_logo": "☕"
}
```

---

## 📤 Deployment a Hostinger

### Opción 1: FTP Automático (Recomendado)

**Módulo Make.com**: FTP > Upload Files

```javascript
// Configuración
{
  "server": "ftp.tudominio.com",
  "port": 21,
  "username": process.env.FTP_USER,
  "password": process.env.FTP_PASS,
  "remote_path": "/public_html/clientes/{{SLUG_NEGOCIO}}/",
  "files": [
    "index.html",
    "styles.css",
    "script.js",
    "componentes-globales/**"
  ]
}
```

### Opción 2: API de Hostinger

Si Hostinger tiene API disponible:

```javascript
// Módulo: HTTP > Make a Request
{
  "url": "https://api.hostinger.com/v1/websites",
  "method": "POST",
  "headers": {
    "Authorization": "Bearer {{API_KEY}}"
  },
  "body": {
    "domain": "{{NOMBRE_NEGOCIO}}.tudominio.com",
    "files": "{{ARCHIVOS_GENERADOS}}"
  }
}
```

### Estructura de Carpetas en Servidor

```
/public_html/
└── clientes/
    ├── cafeteria-ejemplo/
    │   ├── index.html
    │   ├── styles.css
    │   ├── script.js
    │   └── assets/
    │       └── images/
    ├── gimnasio-xyz/
    │   └── ...
    └── tienda-abc/
        └── ...
```

---

## 🎨 Personalización Avanzada

### Añadir Nuevos Templates

1. Crear carpeta en `/templates/nuevo-template/`
2. Crear archivos base:
   - `index.html`
   - `styles.css`
   - `config.json`
3. Definir variables en `config.json`
4. Añadir lógica en Make.com para detectar cuándo usar este template

### Crear Nuevos Componentes

1. Crear carpeta en `/componentes-globales/nuevo-componente/`
2. Crear archivos:
   - `componente.html`
   - `componente-styles.css`
   - `componente-script.js` (opcional)
   - `config.json`
3. Documentar variables en `config.json`

---

## 🔒 Seguridad

### Variables de Entorno en Make.com

```javascript
// No hardcodear credenciales
FTP_HOST = process.env.FTP_HOST
FTP_USER = process.env.FTP_USER
FTP_PASS = process.env.FTP_PASS
OPENAI_API_KEY = process.env.OPENAI_API_KEY
```

### Validación de Inputs

```javascript
// En Make.com, validar datos del formulario
if (!email.includes('@')) {
  throw new Error('Email inválido');
}

if (fotos.length < 1) {
  throw new Error('Se requiere al menos 1 foto');
}
```

---

## 💡 Tips y Mejores Prácticas

### 1. Optimización de Imágenes
- Comprimir fotos antes de subirlas (TinyPNG API)
- Convertir a WebP cuando sea posible
- Lazy loading para imágenes

### 2. SEO Automático
- GPT-4o genera títulos SEO-friendly
- Meta descriptions optimizadas
- Alt text automático para imágenes

### 3. Performance
- Minificar CSS/JS antes de deploy
- Usar CDN para fuentes (Google Fonts)
- Implementar cache headers

### 4. Testing
- Probar en múltiples dispositivos
- Validar HTML con W3C Validator
- Google PageSpeed Insights

---

## 📞 Soporte y Contacto

**Desarrollador**: Tu Nombre  
**Email**: tu@email.com  
**Última actualización**: Noviembre 2024  
**Versión**: 1.0.0

---

## 📝 Changelog

### v1.0.0 (2024-11-22)
- ✅ Componentes globales: Header, Footer, Chatbot
- ✅ Template 1: Landing Page Básica completo
- ✅ Integración con GPT-4o Vision documentada
- ✅ Flujo Make.com definido
- 🔜 Templates 2-5 en desarrollo

---

## 🙏 Créditos

- **Fuentes**: Google Fonts (Inter)
- **Iconos**: SVG inline
- **IA**: OpenAI GPT-4o Vision
- **Automatización**: Make.com
- **Hosting**: Hostinger

---

**¿Listo para generar sitios web automáticamente? 🚀**

Sigue la guía de uso y configura tu primer flujo en Make.com.
