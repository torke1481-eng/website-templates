# ⚡ Quick Start - Sistema de Templates Automáticos

Guía rápida para empezar a generar sitios web automáticamente en 30 minutos.

---

## 🎯 ¿Qué has creado?

Un **sistema modular de generación automática de sitios web** que usa:

✅ **GPT-4o Vision** → Analiza fotos de negocios y extrae colores, estilo y genera contenido  
✅ **Make.com** → Automatiza todo el proceso  
✅ **Templates modulares** → Componentes reutilizables (header, footer, chatbot)  
✅ **Hostinger** → Deploy automático vía FTP  

**Resultado**: De formulario a sitio web generado en **10 minutos** + tu revisión 🚀

---

## 📦 Lo que tienes ahora

```
/templates/
├── 📄 README.md                      ← Documentación completa
├── 🔧 MAKE_COM_SETUP.md             ← Guía paso a paso Make.com
├── 🤖 GPT4O_VISION_PROMPT.md        ← Prompts optimizados
├── ⚡ QUICK_START.md                ← Esta guía
│
├── 🧩 componentes-globales/
│   ├── header/                       ✅ Header moderno con nav
│   ├── footer/                       ✅ Footer multi-columna
│   └── chatbot/                      ✅ Chatbot flotante
│
└── 🎨 landing-basica/               ✅ TEMPLATE COMPLETO
    ├── index.html                    → HTML con variables
    ├── styles.css                    → CSS personalizable
    ├── script.js                     → JavaScript opcional
    └── config.json                   → 25+ variables configurables
```

---

## 🚀 Empieza en 5 Pasos

### Paso 1: Crear Formulario (5 min)

Usa **TypeForm**, **Google Forms** o tu propio formulario con estos campos:

- **nombre_negocio** (texto)
- **email** (email)
- **telefono** (texto, opcional)
- **tipo_web** (dropdown: landing / ecommerce / blog)
- **foto_principal** (file upload - JPG/PNG)
- **descripcion_adicional** (textarea, opcional)

**Link al formulario ejemplo**: [Crear en TypeForm](https://www.typeform.com/)

---

### Paso 2: Configurar Make.com (15 min)

1. **Crear cuenta** en [Make.com](https://www.make.com) (Plan Pro recomendado)

2. **Crear nuevo Scenario**

3. **Añadir módulos** en este orden:
   ```
   Webhook → GPT-4o Vision → Replace Variables → FTP Upload → Send Email
   ```

4. **Copiar configuración** desde `MAKE_COM_SETUP.md`

5. **Variables de entorno** necesarias:
   ```
   OPENAI_API_KEY = tu_api_key_de_openai
   FTP_HOST = ftp.tudominio.com
   FTP_USER = tu_usuario_ftp
   FTP_PASS = tu_password_ftp
   ```

📖 **Guía completa**: Ver `MAKE_COM_SETUP.md`

---

### Paso 3: Obtener API Key de OpenAI (5 min)

1. Ir a [platform.openai.com](https://platform.openai.com/)
2. Create account / Sign in
3. API Keys → Create new secret key
4. Copiar y guardar en Make.com

**Modelo necesario**: `gpt-4o` (con Vision)  
**Costo estimado**: ~$0.50 por análisis de imagen

---

### Paso 4: Configurar FTP de Hostinger (3 min)

En tu panel de Hostinger:

1. Ir a **Files** → **FTP Accounts**
2. Crear nueva cuenta FTP o usar la principal
3. Anotar:
   - Host: `ftp.tudominio.com`
   - Username: `usuario@tudominio.com`
   - Password: `tu_password`
   - Port: `21`

---

### Paso 5: Probar el Sistema (2 min)

1. **Llenar tu formulario** con datos de prueba
2. **Subir una foto** de ejemplo (cualquier negocio)
3. **Esperar** ~30 segundos
4. **Revisar email** con link al sitio generado
5. **Abrir sitio** en navegador

🎉 **¡Listo!** Tu primer sitio generado automáticamente.

---

## 🎨 Ejemplo Práctico

### Input del Cliente

**Formulario:**
```
nombre_negocio: "Café Mocca"
email: "info@cafemocca.com"
tipo_web: "landing"
foto: [Imagen de una cafetería moderna]
```

### Análisis GPT-4o Vision

```json
{
  "tipo_negocio": "cafetería",
  "colores_principales": ["#8B4513", "#D4A373", "#F5E6D3"],
  "titulo_hero": "EL MEJOR CAFÉ ARTESANAL DE LA CIUDAD",
  "subtitulo_hero": "Granos seleccionados, tostados diariamente...",
  "emoji_logo": "☕"
}
```

### Output Final

**Sitio web publicado en:**
```
https://tudominio.com/clientes/cafe-mocca/
```

Con:
- ✅ Colores automáticos extraídos (#8B4513, #D4A373)
- ✅ Título generado por IA
- ✅ Contenido personalizado
- ✅ Responsive design
- ✅ SEO optimizado

**Tiempo total**: 10 minutos (automático) + tu revisión ⚡

---

## 💰 Costos Operativos

| Item | Costo |
|------|-------|
| GPT-4o Vision (por sitio) | ~$0.50 USD |
| Make.com Plan Pro | $29/mes (ilimitado) |
| Hosting Hostinger | Ya incluido |
| **Total por sitio** | **~$0.50 USD** |

Con Plan Pro de Make.com puedes generar **cientos de sitios por mes**.

---

## 🔥 Próximos Pasos

### Ahora mismo
1. ✅ Revisa tu estructura en `/templates/`
2. ✅ Lee `README.md` completo
3. ✅ Sigue `MAKE_COM_SETUP.md` paso a paso

### Esta semana
1. 🎯 Crear formulario de clientes
2. 🤖 Configurar Make.com scenario
3. ⚡ Probar con 3-5 negocios de prueba
4. 📊 Ajustar prompts según resultados

### Este mes
1. 🛒 Crear Template 2: E-commerce
2. 📝 Crear Template 3: Blog
3. 🎨 Personalizar componentes con tu branding
4. 💼 Ofrecer el servicio a clientes reales

---

## 📚 Recursos Útiles

### Documentación
- [`README.md`](./README.md) - Documentación completa del sistema
- [`MAKE_COM_SETUP.md`](./MAKE_COM_SETUP.md) - Configuración detallada
- [`GPT4O_VISION_PROMPT.md`](./GPT4O_VISION_PROMPT.md) - Prompts optimizados

### Templates
- [`/landing-basica/`](./landing-basica/) - Template 1 completo
- [`/componentes-globales/`](./componentes-globales/) - Componentes reutilizables

### Config Files
- Cada template tiene su `config.json` con todas las variables
- Cada componente tiene su `config.json` con opciones

---

## 🆘 Troubleshooting

### "El webhook no recibe datos"
- Verifica la URL en tu formulario
- Revisa que el webhook esté activo en Make.com
- Testea con Postman primero

### "GPT-4o retorna error"
- Verifica tu API Key
- Confirma que tienes créditos en OpenAI
- Revisa el formato del prompt

### "FTP falla al subir"
- Confirma credenciales FTP
- Verifica permisos de carpetas
- Prueba conexión FTP manual primero

### "Los colores no se ven bien"
- Ajusta el prompt de GPT-4o para mejor detección
- Puedes añadir colores manualmente en config

---

## 💡 Tips Pro

### Optimiza Costos
- Cachea templates en Make.com Data Store
- Usa compresión de imágenes (TinyPNG)
- Batch procesa múltiples sitios a la vez

### Mejora Resultados
- Pide al cliente 2-3 fotos de diferentes ángulos
- Incluye logo del cliente si lo tiene
- Usa fotos de alta calidad (min 1200px ancho)

### Automatiza Más
- Integra pasarela de pago para cobrar automáticamente
- Añade onboarding automático vía email
- Crea dashboard para que clientes editen contenido

---

## 🎓 Aprende Más

### Make.com
- [Make.com Academy](https://www.make.com/en/academy)
- [Community Forum](https://www.make.com/en/community)

### GPT-4o Vision
- [OpenAI Vision Guide](https://platform.openai.com/docs/guides/vision)
- [Best Practices](https://cookbook.openai.com/)

### FTP & Hostinger
- [Hostinger Tutorials](https://www.hostinger.com/tutorials)
- [FileZilla Guide](https://filezilla-project.org/)

---

## 🚀 ¿Listo para Empezar?

1. ✅ **Crea tu formulario** (5 min)
2. ✅ **Configura Make.com** (15 min)  
3. ✅ **Obtén API Keys** (5 min)
4. ✅ **Prueba con datos reales** (2 min)

**Total: 27 minutos** ⏱️

Después de esto, cada sitio nuevo se genera **automáticamente en 5 minutos**.

---

## 📞 Soporte

¿Problemas o preguntas?

1. Revisa `README.md` completo
2. Consulta `MAKE_COM_SETUP.md`
3. Revisa logs en Make.com
4. Contacta soporte de Make.com o OpenAI

---

**¡Éxito con tu sistema de generación automática! 🎉**

Estás a minutos de poder crear sitios web profesionales automáticamente.
