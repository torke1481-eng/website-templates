# 📂 ESTRUCTURA DEL PROYECTO EN HOSTINGER

Este documento refleja la estructura exacta del proyecto desplegado en Hostinger.

## 🗂️ Estructura de Carpetas

```
/public_html/
├── generator/                    # Scripts PHP de generación
│   ├── deploy-v2.php            # ✅ ACTIVO - Generador principal
│   ├── deploy-site.php          # Generador completo (con timeout)
│   ├── deploy-simple.php        # Generador básico de prueba
│   ├── test-paths.php           # Diagnóstico de rutas
│   └── test-simple.php          # Test de PHP
│
├── staging/                      # Sitios generados (dinámico)
│   └── [slug-cliente]/          # Carpeta por cliente
│       ├── index.html
│       ├── css/
│       │   ├── styles.css
│       │   ├── header-styles.css
│       │   ├── footer-styles.css
│       │   ├── chatbot-styles.css
│       │   └── custom.css       # Generado con colores de Claude
│       ├── js/
│       │   ├── main.js
│       │   └── chatbot.js
│       ├── images/
│       │   └── hero.[jpg|png]   # Imagen del cliente
│       └── .metadata.json
│
└── templates/                    # Templates HTML base
    ├── landing-basica/          # ✅ TEMPLATE ACTIVO
    │   ├── index.html           # HTML con placeholders
    │   ├── styles.css           # CSS base mejorado
    │   └── script.js            # JS con smooth scroll y animaciones
    │
    ├── ecommerce-completo/
    ├── servicios-profesionales/
    │
    └── componentes-globales/     # Componentes reutilizables
        ├── header/
        │   ├── header.html
        │   ├── header-styles.css
        │   └── header-script.js
        ├── footer/
        │   ├── footer.html
        │   └── footer-styles.css
        └── chatbot/
            ├── chatbot-styles.css
            └── chatbot-script.js
```

---

## 🔗 URLs Importantes

### **Make.com Endpoint**
```
https://otavafitness.com/generator/deploy-v2.php
```

### **Sitios Generados**
```
https://otavafitness.com/staging/[slug-cliente]/
```

**Ejemplo actual:**
```
https://otavafitness.com/staging/otava-dev-solution/
```

---

## 📋 Archivos PHP Activos

### **1. deploy-v2.php** ✅ EN USO
- **Ubicación**: `/generator/deploy-v2.php`
- **Función**: Genera sitios completos con todos los placeholders reemplazados
- **Características**:
  - ✅ Crea estructura de carpetas
  - ✅ Copia CSS/JS de templates
  - ✅ Reemplaza todos los placeholders
  - ✅ Genera CSS personalizado con colores de Claude
  - ✅ Procesa características dinámicas
  - ✅ Sin descarga de imágenes (evita timeout)
  - ✅ Timeout 180s, Memoria 256MB

**Placeholders que reemplaza:**
- Header: BRAND_EMOJI, BRAND_NAME, BRAND_TAGLINE, NAV_ITEMS
- Hero: HERO_TITLE, HERO_SUBTITLE, HERO_BADGE, CTA_PRIMARY/SECONDARY
- Features: FEATURES_TITLE, FEATURES_SUBTITLE, FEATURE_CARDS
- About: ABOUT_TITLE, ABOUT_DESCRIPTION, ABOUT_LIST_ITEMS
- Footer: CURRENT_YEAR, COPYRIGHT_TEXT, COLUMN_1/2/3, SOCIAL_LINKS
- Actions: Botones con WhatsApp y Email

---

## 🎨 Templates Actualizados

### **landing-basica/index.html**
- ✅ IDs de sección: `#inicio`, `#servicios`, `#nosotros`, `#contacto`
- ✅ Navegación funcional
- ✅ Estructura semántica

### **landing-basica/styles.css**
- ✅ Animaciones fade-in al scroll
- ✅ Estilos para navegación activa
- ✅ Header con efecto al scroll
- ✅ Variables CSS personalizables
- ✅ Responsive completo

### **landing-basica/script.js**
- ✅ Smooth scroll entre secciones
- ✅ Navegación activa automática
- ✅ Observer para animaciones
- ✅ Header cambia al hacer scroll

---

## 🔄 Flujo de Generación

```
1. Tally Form → Usuario envía datos
2. Make.com → Procesa formulario
3. GPT-4o → Analiza imagen y genera contenido
4. Claude Sonnet 4.5 → Genera diseño y colores
5. Make.com → POST a deploy-v2.php con JSON
6. deploy-v2.php → Genera sitio completo en /staging/
7. Respuesta → preview_url enviada por email
```

---

## 📊 Datos que Recibe deploy-v2.php

```json
{
  "template_type": "landing-basica",
  "nombre_negocio": "Nombre",
  "slug": "nombre-negocio",
  "email": "email@ejemplo.com",
  "telefono": "+123456789",
  "foto_url": "https://...",
  
  "info_negocio": {
    "tipo_negocio": "Tipo",
    "industria": "Industria",
    "tono": "Profesional"
  },
  
  "diseno": {
    "emoji_logo": "🌐",
    "colores_principales": "[#xxx, #yyy, #zzz]",
    "colores_complementarios": "[#aaa, #bbb]",
    "titulo_hero": "Título",
    "subtitulo_hero": "Subtítulo",
    "cta_principal": "Texto",
    "cta_secundario": "Texto",
    "meta_description": "Descripción",
    "descripcion_about": "Sobre nosotros",
    "caracteristicas": "[{icon, titulo, descripcion}, ...]"
  }
}
```

---

## ✅ Estado Actual del Proyecto

- ✅ Templates subidos a Hostinger
- ✅ Generador PHP funcionando
- ✅ Navegación smooth scroll implementada
- ✅ Animaciones CSS activas
- ✅ Placeholders completamente reemplazados
- ✅ Colores personalizados aplicados
- ✅ Sitios 100% funcionales

---

## 🚀 Próximos Pasos

1. ⏳ Integrar descarga de imágenes (opcional)
2. ⏳ Agregar más templates
3. ⏳ Sistema de administración de sitios
4. ⏳ Deployment a dominio del cliente

---

**Última actualización**: 24 Nov 2025, 20:50 UTC-3
