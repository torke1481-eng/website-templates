# 🤖 Prompts Optimizados para GPT-4o Vision

Colección de prompts probados para análisis de imágenes de negocios.

---

## 📸 Prompt Principal - Análisis Completo

```
Eres un experto en branding, diseño web y marketing digital. Analiza la imagen del negocio proporcionada y extrae información clave para generar una landing page profesional y efectiva.

Analiza esta imagen de un negocio/local y proporciona la siguiente información en formato JSON estricto:

{
  "tipo_negocio": "Identifica el tipo exacto de negocio",
  "subtipo": "Clasificación más específica si es posible",
  "colores_principales": ["#HEX1", "#HEX2", "#HEX3"],
  "ambiente": "moderno | clásico | minimalista | elegante | casual",
  "titulo_hero": "TÍTULO IMPACTANTE EN MAYÚSCULAS (MAX 80 CARACTERES)",
  "subtitulo_hero": "Subtítulo descriptivo de 20-30 palabras",
  "descripcion_about": "Descripción persuasiva de 60-80 palabras",
  "caracteristicas": [
    {"icon": "emoji", "titulo": "Característica", "descripcion": "Descripción breve"}
  ],
  "trust_badges": ["Badge 1", "Badge 2", "Badge 3"],
  "cta_principal": "Texto CTA",
  "emoji_logo": "emoji",
  "keywords_seo": ["keyword1", "keyword2", "keyword3"],
  "meta_description": "Meta description SEO (MAX 160 caracteres)"
}

IMPORTANTE: Responde SOLO con JSON válido, sin markdown.
```

---

## 🎯 Ejemplos por Tipo de Negocio

### Restaurante/Cafetería
```json
{
  "tipo_negocio": "cafetería",
  "subtipo": "café de especialidad",
  "colores_principales": ["#8B4513", "#D4A373", "#F5E6D3"],
  "ambiente": "moderno-acogedor",
  "titulo_hero": "EL MEJOR CAFÉ ARTESANAL DE LA CIUDAD",
  "subtitulo_hero": "Granos seleccionados, tostados diariamente por baristas expertos",
  "cta_principal": "Visítanos Hoy",
  "emoji_logo": "☕"
}
```

### Gimnasio/Fitness
```json
{
  "tipo_negocio": "gimnasio",
  "subtipo": "crossfit",
  "colores_principales": ["#FF6B35", "#004E89", "#F7F7F7"],
  "ambiente": "energético-moderno",
  "titulo_hero": "TRANSFORMA TU CUERPO Y MENTE",
  "subtitulo_hero": "Entrenamientos personalizados con equipamiento de última generación",
  "cta_principal": "Prueba Gratis",
  "emoji_logo": "💪"
}
```

---

**Último archivo creado!**
