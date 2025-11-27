# ✅ CHECKLIST DATOS MÍNIMOS - AGENTE PROSPECTOR

## 🎯 CAMPOS OBLIGATORIOS (SIN ESTO NO SE PUEDE GENERAR WEB)

### **1. NEGOCIO BÁSICO** ✅

```
[ ] nombre_comercial           → Ej: "Gimnasio FitPro"
[ ] categoria_principal        → Ej: "Gimnasio", "Restaurante", "Consultorio"
[ ] ciudad                     → Ej: "Quito"
[ ] pais                       → Ej: "Ecuador"
[ ] telefono_principal         → Ej: "+593987654321"
[ ] whatsapp                   → Ej: "+593987654321" (mismo que teléfono si no hay otro)
```

**Email: ❌ NO OBLIGATORIO** (muchos negocios no tienen)

---

### **2. ANÁLISIS VISUAL (GPT-4o Vision)** 🎨

```
[ ] descripcion_ambiente       → 2-3 frases describiendo el lugar
[ ] colores_dominantes         → Mínimo 3 colores HEX
    [ ] Color 1: hex + nombre + uso
    [ ] Color 2: hex + nombre + uso
    [ ] Color 3: hex + nombre + uso
[ ] estilo_visual             → Ej: "Industrial moderno", "Minimalista", "Clásico"
[ ] mood                      → Ej: "Energético, motivador", "Elegante, tranquilo"
```

**Por qué:** Estos datos definen cómo se ve la web (colores, estilo)

---

### **3. ANÁLISIS RESEÑAS (GPT-4o Text)** ⭐

```
[ ] rating_promedio           → Ej: 4.7
[ ] total_resenas            → Ej: 142
[ ] keywords_positivas       → Top 5 keywords más mencionados
    [ ] Keyword 1: nombre + menciones
    [ ] Keyword 2: nombre + menciones
    [ ] Keyword 3: nombre + menciones
    [ ] Keyword 4: nombre + menciones
    [ ] Keyword 5: nombre + menciones
[ ] testimonios              → Mínimo 1 testimonio completo
    [ ] Texto original
    [ ] Autor
    [ ] Rating
[ ] unique_selling_points    → 3-5 ventajas únicas del negocio
```

**Por qué:** Esto define el contenido (qué destacar, prueba social)

---

### **4. COMPETENCIA** 🏆

```
[ ] diferenciadores_unicos   → 2-3 cosas que hacen único al negocio
    Ej: "Mejor rating", "Único con X servicio", "Equipamiento premium"
```

**Por qué:** Define el ángulo de marketing

---

### **5. CONTENIDO GENERADO (GPT-4o Text)** ✍️

```
[ ] headline                 → 1 título principal para hero
[ ] subheadline             → 1 subtítulo
[ ] cta_principal           → Ej: "Agenda Tu Clase Gratis"
[ ] value_propositions      → Mínimo 3 propuestas de valor
    [ ] Value 1: título + descripción + icon
    [ ] Value 2: título + descripción + icon
    [ ] Value 3: título + descripción + icon
[ ] servicios_principales   → 1-3 servicios
    [ ] Servicio 1: nombre + descripción + precio
[ ] stats_destacadas        → 3-4 números impactantes
    [ ] Stat 1: número + label
    [ ] Stat 2: número + label
    [ ] Stat 3: número + label
[ ] faqs_principales        → Mínimo 4 preguntas/respuestas
    [ ] FAQ 1: pregunta + respuesta
    [ ] FAQ 2: pregunta + respuesta
    [ ] FAQ 3: pregunta + respuesta
    [ ] FAQ 4: pregunta + respuesta
```

**Por qué:** Este es el contenido que va directo a la web

---

### **6. SEO** 🔍

```
[ ] keywords_primarias      → 3 keywords principales
    Ej: "gimnasio quito", "crossfit quito", etc
```

---

### **7. DISEÑO** 🎨

```
[ ] paleta_colores         → Primary, Secondary, Accent (extraído de colores_dominantes)
[ ] estilo                 → Repite estilo_visual
[ ] mood                   → Repite mood
```

---

## 📊 RESUMEN CONTEO

```
TOTAL CAMPOS OBLIGATORIOS:

✅ Negocio Básico: 6 campos
✅ Análisis Visual: 4 campos (3+ colores)
✅ Análisis Reseñas: 4 campos (5+ keywords, 1+ testimonio, 3+ USPs)
✅ Competencia: 1 campo
✅ Contenido: 7 campos (3+ values, 1+ servicio, 3+ stats, 4+ FAQs)
✅ SEO: 1 campo (3+ keywords)
✅ Diseño: 3 campos

TOTAL: ~26 campos mínimos
```

---

## 🎯 VALIDACIÓN ANTES DE ENVIAR A MAKE.COM

Antes de pasar el JSON a Make.com, verificar:

```javascript
// Validación JavaScript
function validarJSONMinimo(data) {
  const errores = [];
  
  // 1. Negocio básico
  if (!data.negocio?.nombre_comercial) errores.push("Falta nombre_comercial");
  if (!data.negocio?.categoria_principal) errores.push("Falta categoria_principal");
  if (!data.negocio?.ubicacion?.ciudad) errores.push("Falta ciudad");
  if (!data.negocio?.contacto?.telefono_principal) errores.push("Falta telefono");
  
  // 2. Análisis visual
  if (!data.analisis_visual?.descripcion_ambiente) errores.push("Falta descripcion_ambiente");
  if (!data.analisis_visual?.colores_dominantes || data.analisis_visual.colores_dominantes.length < 3) {
    errores.push("Faltan colores (mínimo 3)");
  }
  
  // 3. Reseñas
  if (!data.analisis_resenas?.google_reviews?.rating_promedio) errores.push("Falta rating");
  if (!data.analisis_resenas?.keywords_positivas_frecuencia || data.analisis_resenas.keywords_positivas_frecuencia.length < 5) {
    errores.push("Faltan keywords positivas (mínimo 5)");
  }
  if (!data.analisis_resenas?.testimonios_destacados || data.analisis_resenas.testimonios_destacados.length < 1) {
    errores.push("Falta al menos 1 testimonio");
  }
  
  // 4. Contenido
  if (!data.contenido_generado?.hero_section?.headline) errores.push("Falta headline");
  if (!data.contenido_generado?.value_propositions || data.contenido_generado.value_propositions.length < 3) {
    errores.push("Faltan value propositions (mínimo 3)");
  }
  if (!data.contenido_generado?.faqs_principales || data.contenido_generado.faqs_principales.length < 4) {
    errores.push("Faltan FAQs (mínimo 4)");
  }
  
  // Resultado
  if (errores.length > 0) {
    return {
      valido: false,
      errores: errores
    };
  }
  
  return {
    valido: true,
    mensaje: "✅ JSON válido, listo para Make.com"
  };
}
```

---

## 📋 EJEMPLO JSON MÍNIMO VÁLIDO

Ver archivo: `AGENTE_PROSPECTOR_MINIMO.json`

---

## ⚠️ FALLBACKS SI FALTA DATA

Si el agente NO puede conseguir algún dato:

### **Datos de negocio:**
```javascript
email: "" // Vacío está OK
website_actual: "" // Vacío está OK
```

### **Reseñas:**
```javascript
// Si no hay reseñas Google (negocio nuevo)
rating_promedio: 0
total_resenas: 0
keywords_positivas: ["servicio profesional", "buena atención", "recomendado"]
testimonios: [{
  texto: "Negocio nuevo, testimonios próximamente",
  autor: "Cliente",
  rating: 5
}]
```

### **Competencia:**
```javascript
// Si no puede analizar competencia
diferenciadores_unicos: [
  "Atención personalizada",
  "Ubicación conveniente",
  "Profesionales certificados"
]
```

### **Contenido:**
```javascript
// Si GPT-4o falla generando contenido, usar defaults genéricos
headline: "Bienvenido a [nombre_comercial]"
subheadline: "Servicio profesional de [categoria] en [ciudad]"
```

---

## 🎯 ORDEN DE PRIORIDAD

Si el agente tiene limitaciones de tiempo/costo, conseguir en este orden:

```
PRIORIDAD CRÍTICA (sin esto no funciona):
1. ✅ Nombre, categoría, ciudad, teléfono
2. ✅ 3 colores HEX
3. ✅ Descripción ambiente (1 párrafo mínimo)
4. ✅ 1 headline + 1 subheadline
5. ✅ 3 value propositions

PRIORIDAD ALTA (afecta calidad):
6. ✅ Keywords positivas (top 5)
7. ✅ 1 testimonio real
8. ✅ 3 stats
9. ✅ 4 FAQs

PRIORIDAD MEDIA (mejora pero no crítico):
10. ✅ Rating Google
11. ✅ USPs identificados
12. ✅ Diferenciadores competencia
13. ✅ Keywords SEO
```

---

## 🚀 SIGUIENTE PASO

Una vez tengas el JSON mínimo:

1. **Validar** con función JavaScript arriba
2. **Revisar manualmente** (Francisco)
3. **Copiar** JSON completo
4. **Pegar** en Make.com webhook
5. **Generar** web automáticamente

---

**¿Listo para implementar el agente o probamos Make.com primero con este JSON de ejemplo?** 🎯
