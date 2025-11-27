# 🤖 GUÍA DE IMPLEMENTACIÓN - AGENTE PROSPECTOR

## 📋 RESUMEN EJECUTIVO

Tu agente prospector debe generar un JSON de ~200-300 líneas con toda la inteligencia necesaria para crear una web personalizada de altísima calidad.

**Archivo creado:** `AGENTE_PROSPECTOR_SCHEMA.json`

---

## 🎯 OBJETIVO

**Entrada:** Nombre del negocio + ubicación (ej: "FitPro Gym, Quito")  
**Salida:** JSON completo con análisis profundo

**Procesamiento:**
1. Google Maps API → Datos básicos
2. GPT-4o Vision → Análisis visual de fotos
3. GPT-4o Text → Análisis de reseñas + contenido
4. Comparativa competencia
5. Generación contenido contextualizado

---

## 📊 ESTRUCTURA POR SECCIONES

### **1. METADATA (obligatorio)**

```json
{
  "metadata": {
    "version": "1.0",
    "timestamp": "ISO 8601",
    "agent_version": "prospector-v2",
    "scan_duration_seconds": 45
  }
}
```

**Propósito:** Trazabilidad y debugging

---

### **2. NEGOCIO (datos duros)**

**Fuente:** Google Maps API + Google Places API

```json
{
  "negocio": {
    "nombre_comercial": "string",      // Del perfil Google
    "categoria_principal": "string",   // Categoría Maps
    "ubicacion": {...},                // Dirección completa
    "contacto": {...},                 // Teléfono, email, redes
    "operacion": {...}                 // Horarios, años
  }
}
```

**⚠️ CRÍTICO:**
- `telefono_principal`: Formato internacional (+593...)
- `redes_sociales.instagram`: Sin @ (solo username)
- `coordenadas`: Precisión 4 decimales mínimo

---

### **3. ANÁLISIS VISUAL (GPT-4o Vision)**

**Prompt para GPT-4o Vision:**

```
Eres un experto en branding y diseño web analizando un negocio.

TAREA:
Analiza estas {N} fotos del negocio y extrae:

1. DESCRIPCIÓN AMBIENTE (2-3 frases)
   - ¿Qué ves? ¿Qué sensación transmite?
   - Nivel de profesionalismo 1-10
   
2. COLORES DOMINANTES
   - Identifica 3-4 colores principales
   - Código HEX aproximado
   - % de presencia visual
   - Dónde se usa cada uno
   
3. EQUIPAMIENTO/PRODUCTOS VISIBLE
   - Lista específica (marcas si son visibles)
   - Nivel de gama (económico/medio/premium)
   
4. PERSONAS EN FOTOS
   - Demografía aproximada
   - Vestimenta/estilo
   - Actividades que realizan
   
5. DIFERENCIADORES VISUALES
   - ¿Qué lo hace único visualmente?
   - Comparado con competencia genérica
   
6. CALIDAD FOTOGRÁFICA
   - Profesional/amateur
   - Lighting/composición
   
Responde en JSON estructurado.
```

**Ejemplo output esperado:**
```json
{
  "descripcion_ambiente": "Gimnasio moderno estilo industrial...",
  "colores_dominantes": [
    {"hex": "#1a1a1a", "porcentaje": 40, "uso": "Paredes, piso"}
  ],
  "mood": "Energético, intenso, profesional"
}
```

---

### **4. ANÁLISIS RESEÑAS (GPT-4o Text)**

**Fuente:** Google Reviews API

**Prompt para GPT-4o:**

```
Analiza estas {N} reseñas de Google y extrae:

1. SENTIMENT ANALYSIS
   - % positivo/neutral/negativo
   - Tono predominante
   
2. KEYWORDS MÁS FRECUENTES
   - Positivas: lista con conteo
   - Negativas: lista con conteo
   
3. SELECCIONA 3 TESTIMONIOS DESTACADOS
   Criterios:
   - Rating 5 estrellas
   - Menciona resultados específicos
   - Menciona beneficios/servicios únicos
   - Largo >50 palabras
   - Reciente (últimos 6 meses)
   - Verificado
   
4. UNIQUE SELLING POINTS
   - ¿Qué mencionan repetidamente?
   - Servicios/beneficios destacados
   
5. OBJECIONES COMUNES
   - Quejas recurrentes
   - Dudas antes de comprar
   
Responde en JSON.
```

**⚠️ IMPORTANTE:**
- Testimonios deben ser REALES (texto original)
- Incluir autor, fecha, rating
- Calcular "impacto_score" (qué tan útil para web)

---

### **5. ANÁLISIS COMPETENCIA**

**Proceso:**

1. **Buscar competidores cercanos** (Google Maps API)
   - Radio 3km
   - Misma categoría
   - Rating >3.5

2. **Para cada competidor analizar:**
   - Rating y número reseñas
   - Precio aproximado (si visible)
   - Servicios (de descripción)
   - Website (si existe)

3. **Prompt GPT-4o:**
```
Compara este negocio con sus 5 competidores:

NEGOCIO PRINCIPAL: {datos}
COMPETIDORES: {lista}

Identifica:
1. Gaps de mercado (qué nadie ofrece)
2. Diferenciadores únicos del principal
3. Oportunidades de posicionamiento
4. Rango de precios mercado

Responde en JSON.
```

---

### **6. CONTENIDO GENERADO (GPT-4o Creative)**

**Prompt para generar contenido:**

```
Eres un copywriter experto en {INDUSTRIA}.

CONTEXTO NEGOCIO:
{Todo el análisis anterior}

GENERA:

1. HERO SECTION
   - 3 opciones de headline (gancho principal)
   - 3 opciones subheadline
   - CTA principal
   - CTA secundario

2. VALUE PROPOSITIONS (4 pilares)
   Para cada uno:
   - Título corto (4-6 palabras)
   - Descripción 1-2 frases
   - Icon sugerido
   - Proof (de dónde sale este beneficio)

3. SERVICIOS DETALLADOS
   Basado en análisis:
   - Nombre servicio
   - Descripción corta y larga
   - Precio (si conocido)
   - Qué incluye
   - Para quién es ideal

4. STATS REALES
   - Números verificables
   - Labels claros
   - Source de dónde sale

5. FAQS (6-8)
   Basadas en:
   - Objeciones encontradas en reseñas
   - Preguntas comunes industria
   - Respuestas específicas contextualizadas

TONO: {según industria}
LARGO: Copywriting efectivo, no fluff

Responde JSON estructurado.
```

---

### **7. SEO KEYWORDS**

**Proceso:**

1. **Usar herramienta SEO** (ej: Google Keyword Planner API)
   - Buscar volumen de búsqueda local
   - Dificultad keywords

2. **Generar lista:**
   - 3-5 keywords primarias (alta prioridad)
   - 5-10 keywords secundarias
   - 5-10 long-tail (baja competencia)

**Formato:**
```json
{
  "keyword": "gimnasio crossfit quito",
  "volumen_mensual": 320,
  "dificultad": "media",
  "prioridad": "alta"
}
```

---

### **8. RECOMENDACIONES DISEÑO**

**Basado en análisis visual + industria:**

```json
{
  "paleta_colores": {
    "primary": "#hex",
    "secondary": "#hex",
    "rationale": "Por qué estos colores"
  },
  "tipografia": {
    "headings": "Font sugerida",
    "body": "Font sugerida",
    "rationale": "Por qué estas fonts"
  },
  "secciones_recomendadas_orden": [
    "Hero",
    "Stats",
    "Value props",
    "..."
  ]
}
```

---

## ⚙️ IMPLEMENTACIÓN TÉCNICA

### **Tu agente debe:**

```python
# Pseudocódigo

def analizar_negocio(nombre, ubicacion):
    # 1. Obtener datos básicos
    google_data = google_maps_api(nombre, ubicacion)
    
    # 2. Descargar fotos
    photos = download_photos(google_data.photos_urls)
    
    # 3. Análisis visual
    visual_analysis = gpt4o_vision(
        photos=photos,
        prompt=PROMPT_ANALISIS_VISUAL
    )
    
    # 4. Obtener reseñas
    reviews = google_reviews_api(google_data.place_id)
    
    # 5. Análisis reseñas
    reviews_analysis = gpt4o_text(
        reviews=reviews,
        prompt=PROMPT_ANALISIS_REVIEWS
    )
    
    # 6. Buscar competencia
    competitors = find_competitors(
        location=google_data.coordinates,
        category=google_data.category,
        radius_km=3
    )
    
    # 7. Análisis competitivo
    competitive_analysis = gpt4o_text(
        business=google_data,
        competitors=competitors,
        prompt=PROMPT_COMPETENCIA
    )
    
    # 8. Generar contenido
    content = gpt4o_text(
        context={
            "basic": google_data,
            "visual": visual_analysis,
            "reviews": reviews_analysis,
            "competitive": competitive_analysis
        },
        prompt=PROMPT_GENERAR_CONTENIDO
    )
    
    # 9. Keywords SEO
    keywords = keyword_research(
        business_name=google_data.name,
        category=google_data.category,
        location=ubicacion
    )
    
    # 10. Ensamblar JSON final
    output = {
        "metadata": {...},
        "negocio": google_data,
        "analisis_visual": visual_analysis,
        "analisis_resenas": reviews_analysis,
        "analisis_competencia": competitive_analysis,
        "contenido_generado": content,
        "seo_keywords": keywords,
        "recomendaciones_diseno": design_recs
    }
    
    # 11. Validar schema
    validate_json(output, SCHEMA)
    
    return output
```

---

## ✅ VALIDACIÓN DEL JSON

**Antes de devolver, verificar:**

```python
def validar_json(data):
    checks = [
        data['negocio']['contacto']['telefono_principal'] != "",
        len(data['analisis_visual']['colores_dominantes']) >= 3,
        len(data['analisis_resenas']['testimonios_destacados']) >= 3,
        len(data['contenido_generado']['value_propositions']) == 4,
        len(data['contenido_generado']['faqs_industria_contextualizadas']) >= 6,
        data['seo_keywords']['keywords_primarias'] is not None
    ]
    
    if not all(checks):
        raise ValidationError("JSON incompleto")
    
    return True
```

---

## 📤 OUTPUT FINAL

**El agente debe devolver:**

1. **JSON completo** (schema adjunto)
2. **Log de proceso** (para debugging)
3. **Fotos descargadas** (opcional, para web)

**Formato entrega:**

```json
{
  "status": "success",
  "data": { ... JSON completo ... },
  "log": {
    "duration_seconds": 45,
    "apis_called": ["google_maps", "google_reviews", "gpt4o"],
    "photos_analyzed": 24,
    "reviews_analyzed": 142
  }
}
```

---

## 🎯 EJEMPLO DE USO

**Input:**
```
Negocio: "FitPro Gym"
Ubicación: "Quito, Ecuador"
```

**Output:**
Ver `AGENTE_PROSPECTOR_SCHEMA.json` (ejemplo completo)

---

## 🔄 INTEGRACIÓN CON MAKE.COM

Una vez tu agente genera el JSON:

1. **Francisco revisa** el JSON
2. **Ajusta manualmente** si necesario
3. **Carga en Tally** (formulario prellenado)
4. **Tally → Make.com** con JSON completo
5. **Make genera web** con todo el contexto

---

## 📊 MÉTRICAS DE CALIDAD

**Un buen JSON debe tener:**

- ✅ 3+ testimonios reales con impacto_score >8
- ✅ 4 value propositions únicos verificables
- ✅ 3+ colores HEX con rationale visual
- ✅ 6+ FAQs contextualizadas
- ✅ 3+ diferenciadores competitivos
- ✅ Keywords con volumen de búsqueda
- ✅ Contenido sugerido específico (no genérico)

---

## 🚀 PRÓXIMOS PASOS

1. ✅ Schema JSON creado
2. ⏳ Implementar agente con estos prompts
3. ⏳ Testear con 2-3 negocios reales
4. ⏳ Refinar prompts según resultados
5. ⏳ Crear formulario Tally prellenado
6. ⏳ Integrar con Make.com

---

**¿Siguiente paso: Crear los prompts específicos de GPT-4o optimizados?** 🎯
