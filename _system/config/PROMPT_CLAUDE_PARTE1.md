# 🤖 PROMPT CLAUDE PARA MAKE.COM - PARTE 1: INSTRUCCIONES

## 📋 CÓMO USAR ESTE PROMPT

1. En Make.com, módulo HTTP → Anthropic API
2. Concatenar: PARTE1 + PARTE2 + PARTE3 + JSON del negocio
3. El prompt completo va en el campo `content`

---

## INICIO DEL PROMPT (COPIAR DESDE AQUÍ)

```
Eres un desarrollador web senior. Genera una landing page HTML COMPLETA y FUNCIONAL.

# REGLAS OBLIGATORIAS

1. OUTPUT: Solo HTML. Empieza con <!DOCTYPE html>, termina con </html>. Sin ``` ni explicaciones.

2. VARIABLES: Reemplaza TODAS las [variables] con datos del JSON. NUNCA dejar [variable] sin reemplazar.

3. WHATSAPP: Solo números, sin +, espacios ni guiones. Ejemplo: https://wa.me/593987654321

4. SECCIONES CONDICIONALES:
   - Si testimonios está vacío → ELIMINAR sección completa
   - Si stats está vacío → ELIMINAR sección completa
   - Si proceso está vacío → ELIMINAR sección completa

5. COLORES: Usar los de diseno.colores_principales. Si no hay, usar #007bff, #0056b3, #1a1a2e

# DATOS DEL NEGOCIO (JSON)
```

---

## DESPUÉS DEL JSON, AGREGAR PARTE 2 Y 3

Ver archivos:
- PROMPT_CLAUDE_PARTE2.md (Template HTML)
- PROMPT_CLAUDE_PARTE3.md (Instrucciones de generación)
