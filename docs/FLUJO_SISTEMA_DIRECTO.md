# 🚀 FLUJO DEL SISTEMA - DIRECTO

**Fecha:** Noviembre 2025  
**Versión:** 2.0

## 📊 Arquitectura

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        FLUJO DIRECTO                                    │
│                   (Sin intermediarios)                                  │
└─────────────────────────────────────────────────────────────────────────┘

     ┌──────────────────────────────────────┐
     │ 1. AGENTE IA (Prospector)           │
     │    • Recopila datos del negocio      │
     │    • Genera JSON estructurado        │
     │    • Llama directamente a Claude     │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 2. CLAUDE API                        │
     │    • Recibe JSON + Prompt            │
     │    • Genera HTML completo            │
     │    • Retorna página lista            │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 3. DEPLOY (PHP)                      │
     │    POST → deploy-simple.php          │
     │    • Guarda HTML en servidor         │
     │    • Retorna URL                     │
     └────────────────┬─────────────────────┘
                      ↓
     ┌──────────────────────────────────────┐
     │ 4. GUARDAR EN DB (Opcional)          │
     │    POST → save-website.php           │
     │    • Registra en MySQL               │
     └──────────────────────────────────────┘
```

---

## 🔧 ENDPOINTS DISPONIBLES

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/generator/deploy-simple.php` | POST | Guarda HTML en servidor |
| `/api/save-website.php` | POST | Guarda en base de datos |
| `/api/enrich-business.php` | POST | Enriquece datos (opcional) |
| `/api/validate-html.php` | POST | Valida calidad (opcional) |
| `/api/approve.php` | GET | Aprueba/rechaza (si usas staging) |

**Base URL:** `https://otavafitness.com/_system`
**API Key:** Definida en `secrets.php`

---

## 📋 USO BÁSICO

### Opción 1: Deploy directo (más simple)

```bash
curl -X POST https://otavafitness.com/_system/generator/deploy-simple.php \
  -H "Content-Type: application/json" \
  -H "X-API-Key: TU_API_KEY" \
  -d '{
    "html": "<!DOCTYPE html>...",
    "slug": "mi-negocio",
    "nombre": "Mi Negocio"
  }'
```

**Respuesta:**
```json
{
  "success": true,
  "url": "https://otavafitness.com/domains/mi-negocio/",
  "slug": "mi-negocio",
  "size_kb": 45.2
}
```

### Opción 2: Con registro en DB

Después del deploy, guardar en base de datos:

```bash
curl -X POST https://otavafitness.com/_system/api/save-website.php \
  -H "Content-Type: application/json" \
  -H "X-API-Key: TU_API_KEY" \
  -d '{
    "domain": "mi-negocio",
    "business_name": "Mi Negocio",
    "template": "landing-pro",
    "staging_url": "https://otavafitness.com/domains/mi-negocio/"
  }'
```

---

## 🎯 FLUJO RECOMENDADO PARA EL AGENTE

El agente IA debe:

1. **Recopilar datos** del negocio (nombre, servicios, contacto, etc.)
2. **Llamar a Claude** con el prompt y los datos
3. **Recibir HTML** generado
4. **Llamar a deploy-simple.php** para publicar
5. **Opcionalmente** guardar en DB con save-website.php

---

## 📁 Archivos del Sistema

```
_system/
├── api/
│   ├── get-prompt.php        # Obtiene prompt para Claude
│   ├── save-website.php      # Guarda en MySQL
│   ├── enrich-business.php   # Enriquece datos (opcional)
│   └── validate-html.php     # Valida calidad (opcional)
│
├── config/
│   ├── db.php                    # Conexión MySQL
│   ├── secrets.php               # API Keys y configuración
│   ├── schema.sql.txt            # Schema de la DB
│   └── PROMPT_CLAUDE_TEMPLATE.txt # Prompt para Claude
│
├── generator/
│   ├── deploy-simple.php     # Deploy principal
│   └── template-engine.php   # Motor de templates
│
└── templates/
    ├── landing-pro/          # Template principal
    ├── componentes-globales/ # CSS/JS compartidos
    └── content-blocks/       # Contenido por industria
```

---

## 🔧 ENDPOINTS PARA EL AGENTE

### 1. Obtener Prompt (antes de llamar a Claude)

```bash
curl -X GET https://otavafitness.com/_system/api/get-prompt.php \
  -H "X-API-Key: TU_API_KEY"
```

**Respuesta:**
```json
{
  "success": true,
  "prompt": "Eres un desarrollador web senior...",
  "version": "1.0"
}
```

### 2. Deploy (después de que Claude genera HTML)

```bash
curl -X POST https://otavafitness.com/_system/api/deploy-simple.php \
  -H "Content-Type: application/json" \
  -H "X-API-Key: TU_API_KEY" \
  -d '{
    "html": "<!DOCTYPE html>...",
    "slug": "mi-negocio",
    "nombre": "Mi Negocio"
  }'
```

### 3. Guardar en DB (opcional)

```bash
curl -X POST https://otavafitness.com/_system/api/save-website.php \
  -H "Content-Type: application/json" \
  -H "X-API-Key: TU_API_KEY" \
  -d '{
    "domain": "mi-negocio",
    "business_name": "Mi Negocio",
    "template": "landing-pro"
  }'
```

---

## ⚙️ Configuración

Todo está centralizado en `_system/config/secrets.php`:

```php
define('API_KEY', 'tu-api-key');
define('DEPLOY_MODE', 'production');  // o 'staging'
define('DOMAINS_BASE', '/path/to/domains');
define('SITE_URL', 'https://otavafitness.com');
```

---

## 🤖 FLUJO DEL AGENTE (Pseudocódigo)

```python
def generar_landing(negocio_nombre, ubicacion):
    # 1. Recopilar datos (Google Maps, reseñas, fotos)
    datos = recopilar_datos(negocio_nombre, ubicacion)
    
    # 2. Obtener prompt del servidor
    prompt_response = requests.get(
        "https://otavafitness.com/_system/api/get-prompt.php",
        headers={"X-API-Key": API_KEY}
    )
    prompt_template = prompt_response.json()["prompt"]
    
    # 3. Construir prompt con datos
    prompt_final = prompt_template.replace("{{JSON_NEGOCIO}}", json.dumps(datos))
    
    # 4. Llamar a Claude
    html = claude_api.generate(prompt_final)
    
    # 5. Deploy
    deploy_response = requests.post(
        "https://otavafitness.com/_system/generator/deploy-simple.php",
        headers={"X-API-Key": API_KEY},
        json={
            "html": html,
            "slug": generar_slug(negocio_nombre),
            "nombre": negocio_nombre
        }
    )
    
    # 6. Retornar URL
    return deploy_response.json()["url"]
```
