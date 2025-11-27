# 🎨 PROMPTS GPT-4O - AGENTE PROSPECTOR

Prompts optimizados para máxima calidad de análisis.

---

## 📸 PROMPT 1: ANÁLISIS VISUAL (GPT-4o Vision)

**Cuándo usar:** Después de descargar las fotos de Google Maps

**Input:** Array de imágenes del negocio

**Prompt:**

```
Eres un experto en branding, diseño web y análisis visual de negocios.

CONTEXTO:
- Negocio: {{NOMBRE_NEGOCIO}}
- Industria: {{CATEGORIA}}
- Ubicación: {{CIUDAD}}

TAREA:
Analiza estas {{N_FOTOS}} fotografías del negocio y genera un reporte visual detallado.

ANÁLISIS REQUERIDO:

1. DESCRIPCIÓN AMBIENTE (2-3 frases completas)
   - ¿Qué ves en las fotos?
   - ¿Qué ambiente/sensación transmite?
   - Nivel de profesionalismo (1-10)
   - Estilo de decoración/diseño

2. PALETA DE COLORES DOMINANTES
   Para cada color principal (mínimo 3, máximo 5):
   - Código HEX aproximado
   - Nombre del color
   - Porcentaje presencia visual estimado
   - Dónde se usa (paredes, mobiliario, branding, etc)
   
   IMPORTANTE: Los colores deben ser los que realmente dominan las fotos, no inventes.

3. ESTILO VISUAL
   - Una frase definiendo el estilo (ej: "Industrial moderno", "Minimalista escandinavo")
   - Mood/sensación (ej: "Energético y juvenil", "Elegante y sofisticado")
   - Target demográfico inferido (edad, nivel socioeconómico)

4. EQUIPAMIENTO/PRODUCTOS/MOBILIARIO VISIBLE
   Lista específica de lo que ves:
   - Si ves marcas, nómbralas
   - Clasifica nivel: económico/medio/premium/lujo
   - Cantidad aproximada
   - Estado de conservación

5. PERSONAS EN LAS FOTOS (si hay)
   - Demografía: edad aproximada, género
   - Vestimenta/estilo
   - Qué actividades realizan
   - Expresiones/actitudes

6. DIFERENCIADORES VISUALES
   ¿Qué hace único a este negocio visualmente?
   - Elementos destacables
   - Comparado con estándar de la industria
   - Puntos de atención visual

7. CALIDAD FOTOGRÁFICA
   - Nivel: Amateur/Semi-profesional/Profesional
   - Lighting: Pobre/Aceptable/Bueno/Excelente
   - Composición: Básica/Buena/Excelente
   - Autenticidad: ¿Son fotos reales del negocio o stock photos?

FORMATO DE RESPUESTA:
Devuelve SOLO un JSON válido con esta estructura:

{
  "descripcion_ambiente": "string detallado",
  "colores_dominantes": [
    {
      "hex": "#hexcode",
      "nombre": "nombre color",
      "porcentaje": numero,
      "uso": "descripción dónde se usa"
    }
  ],
  "estilo_visual": "string",
  "mood": "string",
  "target_visual_inferido": "string",
  "equipamiento_visible": ["item1", "item2"],
  "personas_en_fotos": {
    "demografia": "string",
    "nivel_fitness": "string",
    "vestimenta": "string"
  },
  "diferenciadores_visuales": ["diferenciador1", "diferenciador2"],
  "calidad_fotografica": {
    "nivel": "string",
    "lighting": "string",
    "autenticidad": "string"
  }
}

NO agregues comentarios ni explicaciones fuera del JSON.
```

---

## 💬 PROMPT 2: ANÁLISIS DE RESEÑAS (GPT-4o Text)

**Cuándo usar:** Después de obtener reseñas de Google

**Input:** Array de reseñas (texto + rating + autor + fecha)

**Prompt:**

```
Eres un experto en análisis de sentimiento y copywriting para web.

CONTEXTO:
- Negocio: {{NOMBRE_NEGOCIO}}
- Industria: {{CATEGORIA}}  
- Total reseñas a analizar: {{N_REVIEWS}}

TAREA:
Analiza estas reseñas de Google y extrae insights accionables para crear una web de conversión alta.

ANÁLISIS REQUERIDO:

1. SENTIMENT ANALYSIS
   - Calcular % positivo/neutral/negativo
   - Tono predominante en las reseñas positivas
   - Patrón de lenguaje común

2. KEYWORDS POSITIVAS MÁS FRECUENTES
   Identifica palabras/frases que se repiten en reseñas positivas.
   Para cada keyword:
   - La palabra/frase exacta
   - Número de menciones
   - Score de sentiment (0-1)
   
   Mínimo 8 keywords, ordenadas por frecuencia.

3. KEYWORDS NEGATIVAS/OBJECIONES
   Identifica quejas o dudas recurrentes.
   Para cada una:
   - La palabra/frase exacta
   - Número de menciones
   - Score de sentiment (-1 a 0)

4. SELECCIONA 3 TESTIMONIOS DESTACADOS
   Criterios de selección:
   - Rating 5 estrellas
   - Menciona resultados específicos o transformaciones
   - Menciona servicios/beneficios únicos del negocio
   - Longitud mínima 50 palabras
   - Preferir reseñas recientes (últimos 6 meses)
   - Verificado (con foto de perfil real)
   
   Para cada testimonio:
   - Texto ORIGINAL completo (sin editar)
   - Nombre autor (inicial + apellido)
   - Rating
   - Fecha (YYYY-MM-DD)
   - "impacto_score" (1-10): qué tan útil es para web
   - "usabilidad_web": breve nota de por qué es bueno

5. UNIQUE SELLING POINTS IDENTIFICADOS
   ¿Qué mencionan repetidamente los clientes satisfechos?
   Lista de 5-7 USPs con:
   - Descripción del USP
   - Número de menciones
   - Citas ejemplo (1-2 por USP)

6. OBJECIONES COMUNES
   Problemas/dudas mencionados. Para cada uno:
   - La objeción
   - Frecuencia
   - Sugerencia de respuesta para FAQ

FORMATO DE RESPUESTA:
JSON válido siguiendo esta estructura:

{
  "google_reviews": {
    "rating_promedio": 4.7,
    "total_resenas": 142,
    "distribucion": {
      "5_estrellas": 98,
      "4_estrellas": 32,
      "3_estrellas": 8,
      "2_estrellas": 3,
      "1_estrella": 1
    }
  },
  "sentiment_analysis": {
    "sentiment_general": "string",
    "tono_predominante": "string",
    "keywords_positivas_frecuencia": [
      {
        "keyword": "string",
        "menciones": numero,
        "sentiment": 0.XX
      }
    ],
    "keywords_negativas_frecuencia": [...]
  },
  "testimonios_destacados": [
    {
      "texto_original": "string completo",
      "autor": "Nombre A.",
      "rating": 5,
      "fecha": "YYYY-MM-DD",
      "verificado": true,
      "impacto_score": 9.5,
      "usabilidad_web": "string explicación"
    }
  ],
  "unique_selling_points_identificados": [
    "USP 1 (X menciones)",
    "USP 2 (Y menciones)"
  ],
  "objeciones_comunes": [
    {
      "objecion": "string",
      "frecuencia": numero,
      "respuesta_sugerida": "string"
    }
  ]
}

NO agregues comentarios fuera del JSON.
```

---

## 🏆 PROMPT 3: ANÁLISIS COMPETITIVO (GPT-4o Text)

**Cuándo usar:** Después de obtener datos de competidores cercanos

**Input:** Datos del negocio principal + datos de 5 competidores

**Prompt:**

```
Eres un estratega de marketing y posicionamiento de marca.

CONTEXTO:
Analiza la competencia de este negocio para identificar oportunidades de diferenciación.

NEGOCIO PRINCIPAL:
{{JSON_NEGOCIO_PRINCIPAL}}

COMPETIDORES (5):
{{JSON_COMPETIDORES}}

TAREA:
Realiza análisis competitivo profundo y genera recomendaciones estratégicas.

ANÁLISIS REQUERIDO:

1. ANÁLISIS COMPARATIVO
   Para cada competidor:
   - Nombre
   - Rating vs nuestro rating
   - Número reseñas vs nuestras reseñas
   - Precio aproximado (si visible)
   - Fortalezas principales
   - Debilidades evidentes
   - Gap de oportunidad (qué podemos explotar)

2. DIFERENCIADORES ÚNICOS DEL NEGOCIO PRINCIPAL
   ¿Qué tiene este negocio que los competidores NO tienen?
   Mínimo 5 diferenciadores verificables.
   
   Tipos de diferenciadores:
   - Servicios únicos
   - Certificaciones
   - Equipamiento/productos específicos
   - Rating superior
   - Beneficios incluidos
   - Experiencia/años
   - Cualquier otra ventaja competitiva

3. GAPS DE MERCADO IDENTIFICADOS
   ¿Qué NO está ofreciendo nadie en el mercado local?
   - Servicios faltantes
   - Necesidades no cubiertas
   - Oportunidades de nicho

4. ANÁLISIS DE PRECIOS
   - Rango de precios del mercado (min-max)
   - Precio promedio
   - Posicionamiento sugerido del negocio principal
   - Justificación de precio premium (si aplica)

5. DEBILIDADES COMUNES DE COMPETIDORES
   ¿Qué están haciendo mal todos/la mayoría?
   - Web desactualizada
   - Fotos de mala calidad
   - No tienen presencia online
   - Servicio al cliente deficiente
   - etc.

6. OPORTUNIDADES ESTRATÉGICAS
   Basado en todo el análisis, lista 4-5 oportunidades específicas:
   - Qué hacer
   - Por qué funcionaría
   - Qué gap explota

FORMATO DE RESPUESTA:
JSON válido:

{
  "competidores_principales": [
    {
      "nombre": "string",
      "rating": 4.2,
      "reviews": 89,
      "precio_aprox": "string",
      "fortalezas": ["string"],
      "debilidades": ["string"],
      "gap_oportunidad": "string"
    }
  ],
  "analisis_comparativo": {
    "rango_precios_mercado": "string",
    "precio_promedio": "string",
    "diferenciadores_unicos_negocio": ["string"],
    "gaps_mercado_identificados": ["string"],
    "oportunidades": ["string"]
  }
}
```

---

## ✍️ PROMPT 4: GENERACIÓN DE CONTENIDO (GPT-4o Text)

**Cuándo usar:** Después de completar todos los análisis anteriores

**Input:** JSON completo con todos los análisis

**Prompt:**

```
Eres un copywriter experto en {{INDUSTRIA}} especializado en webs de alta conversión.

CONTEXTO COMPLETO DEL NEGOCIO:
{{JSON_ANALISIS_COMPLETO}}

TAREA:
Genera contenido persuasivo y específico para la web, basándote SOLO en datos reales del análisis.

REGLAS ESTRICTAS:
- NO inventes información
- NO uses frases genéricas ("los mejores", "calidad premium") sin respaldo
- TODO debe basarse en análisis visual, reseñas o datos verificables
- Tono: {{TONO_INDUSTRIA}}
- Longitud: Conciso y poderoso, sin fluff

CONTENIDO A GENERAR:

1. HERO SECTION
   a) 3 opciones de HEADLINE
      - Gancho principal
      - Debe mencionar beneficio específico o diferenciador único
      - Máx 12 palabras
      - Evita clichés
      
   b) 3 opciones de SUBHEADLINE
      - Complementa headline
      - Agrega credibilidad (stats, proof)
      - Máx 20 palabras
      
   c) CTA PRINCIPAL
      - Acción clara
      - 2-4 palabras
      
   d) CTA SECUNDARIO
      - Alternativa de menor compromiso
      - 2-4 palabras

2. VALUE PROPOSITIONS (4 pilares)
   Para cada uno:
   - **Título**: 4-6 palabras, beneficio claro
   - **Descripción**: 1-2 frases explicando el beneficio
   - **Icon sugerido**: nombre del icono (ej: "award", "users", "shield")
   - **Proof**: De dónde sale este beneficio (ej: "Mencionado 23 veces en reseñas")
   
   Basados en: USPs identificados en reseñas + diferenciadores vs competencia

3. SERVICIOS DETALLADOS
   Para cada servicio principal (min 2, max 4):
   - **Nombre**: Claro y específico
   - **Descripción corta**: 1 frase de gancho
   - **Descripción larga**: 2-3 frases detallando qué incluye y beneficios
   - **Duración**: Si aplica
   - **Precio**: Si conocido, sino "Desde $X" o "Consultar"
   - **Incluye**: Lista bullets 3-5 ítems
   - **Ideal para**: Descripción del cliente ideal

4. STATS REALES
   Mínimo 4 números impactantes:
   - **numero**: El número
   - **label**: Qué representa
   - **source**: De dónde sale (Google Reviews, años operación, etc)
   
   Deben ser verificables, no inventes.

5. FAQS CONTEXTUALIZADAS (6-8)
   Basadas en:
   - Objeciones encontradas en reseñas
   - Preguntas comunes de la industria
   - Gaps informativos vs competencia
   
   Para cada FAQ:
   - **Pregunta**: Como la haría un cliente real
   - **Respuesta**: Específica, con datos, 2-3 frases
   - **Categoría**: Tipo de FAQ (Precios, Servicios, Logística, etc)
   - **Basado en**: Referencia de dónde sale la pregunta

FORMATO RESPUESTA:
JSON válido:

{
  "hero_section": {
    "headline_opciones": ["opcion1", "opcion2", "opcion3"],
    "subheadline_opciones": ["opcion1", "opcion2", "opcion3"],
    "cta_principal": "string",
    "cta_secundario": "string"
  },
  "value_propositions": [
    {
      "titulo": "string",
      "descripcion": "string",
      "icon": "string",
      "proof": "string"
    }
  ],
  "servicios_detallados": [...],
  "stats_reales": [...],
  "faqs_industria_contextualizadas": [...]
}

IMPORTANTE:
- Usa datos del análisis de reseñas para testimonials y USPs
- Usa análisis visual para describir ambiente/experiencia
- Usa análisis competitivo para diferenciadores
- Menciona números reales (rating, años, reviews)
```

---

## 📋 RESUMEN DE USO

```
FLUJO DEL AGENTE:

1. Google Maps API → Datos básicos
   ↓
2. Download fotos → Array de imágenes
   ↓
3. GPT-4o Vision + PROMPT 1 → análisis_visual.json
   ↓
4. Google Reviews API → Array de reseñas
   ↓
5. GPT-4o Text + PROMPT 2 → analisis_resenas.json
   ↓
6. Google Maps API → Competidores cercanos
   ↓
7. GPT-4o Text + PROMPT 3 → analisis_competencia.json
   ↓
8. GPT-4o Text + PROMPT 4 + (todos los análisis) → contenido_generado.json
   ↓
9. Ensamblar JSON final completo
   ↓
10. Validar schema
   ↓
11. Return a Francisco
```

---

## 💡 TIPS DE OPTIMIZACIÓN

**Para mejorar calidad:**

1. **Fotos**: Mínimo 10 fotos, máximo 30 (más no mejora calidad)
2. **Reseñas**: Analizar todas disponibles, pero seleccionar solo las mejores
3. **Competidores**: 5 es óptimo (ni muy pocos ni muchos)
4. **Temperature GPT-4o**: 
   - Análisis (Prompts 1-3): temp=0.3 (más determinista)
   - Contenido (Prompt 4): temp=0.7 (más creativo)

**Para reducir costos:**

- Comprimir fotos antes de enviar a Vision (max 1024px)
- Limitar reseñas a últimas 150 (suficiente muestra)
- Cachear análisis competitivo (cambia poco)

---

**Archivos creados:**
- ✅ `AGENTE_PROSPECTOR_SCHEMA.json` (estructura completa)
- ✅ `GUIA_IMPLEMENTACION_AGENTE.md` (cómo implementar)
- ✅ `PROMPTS_GPT4O_AGENTE.md` (prompts exactos)

**¿Listo para implementar el agente con estos prompts?** 🚀
