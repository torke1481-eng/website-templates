# 🎯 Guía Completa: Cómo Gestionan sus Tiendas tus Clientes

Esta guía explica **cómo tus clientes van a gestionar sus productos, precios, pedidos y envíos** sin necesidad de llamarte a cada rato.

---

## 🏗️ Arquitectura del Sistema

### Tu Rol vs Rol del Cliente

```
TÚ (Super Admin):
├── Creas el sitio web inicial
├── Configuras la base de datos
├── Generas el token de administrador
├── Entregas acceso al panel admin
└── Cobras mensualidad + soporte

CLIENTE (Dueño de la tienda):
├── Accede a su panel de administración
├── Agrega/edita/elimina productos
├── Cambia precios cuando quiera
├── Ve pedidos en tiempo real
├── Actualiza estados de envío
└── Gestiona su tienda 24/7
```

---

## 🔐 Flujo de Entrega al Cliente

### Cuando Vendes un Sitio Template 3:

#### 1. Tú Creas el Sitio
```bash
# En Make.com o manualmente:
1. Generar sitio con GPT-4o Vision
2. Subir archivos a: tuservidor.com/clientes/tienda-cliente/
3. Crear registro en base de datos (tabla `sites`)
4. Sistema genera automáticamente un admin_token
```

#### 2. Le Envías al Cliente:

```
📧 EMAIL AL CLIENTE:

Hola Juan,

¡Tu tienda online está lista! 🎉

🌐 URL de tu tienda: https://tiendaropa.com
🔐 Panel de administración: https://tuservidor.com/admin/?site=tiendaropa.com

📱 Token de Acceso (guárdalo seguro):
a1b2c3d4e5f6g7h8i9j0...

Con este panel puedes:
✅ Agregar y editar productos
✅ Cambiar precios cuando quieras
✅ Ver pedidos en tiempo real
✅ Actualizar estados de envío
✅ Gestionar tu inventario

📹 Tutorial en video: [link a video]
📄 Manual PDF: [link a PDF]

¿Dudas? Responde este email o llama al +54 9 11...

Saludos,
Tu Nombre
```

#### 3. Cliente Accede a su Panel:

```
1. Entra a: https://tuservidor.com/admin/?site=su-tienda.com
2. Ingresa su token de admin
3. ¡Ya puede gestionar todo!
```

---

## 🛠️ Panel de Administración del Cliente

### Pantalla Principal: Dashboard

```
┌────────────────────────────────────────────┐
│  🛍️ Admin Panel                           │
│  Tienda Ropa                               │
├────────────────────────────────────────────┤
│  📦 Productos                              │
│  🛒 Pedidos                                │
│  🚚 Envíos                                 │
│  ⚙️ Configuración                          │
└────────────────────────────────────────────┘

Estadísticas:
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ 📦 Total     │ │ ✅ Activos   │ │ 📉 Sin Stock │
│    24        │ │    22        │ │     2        │
└──────────────┘ └──────────────┘ └──────────────┘
```

---

## 📦 Gestión de Productos

### Cómo el Cliente Agrega Productos

#### Paso 1: Click en "➕ Agregar Producto"

```
Formulario:
┌────────────────────────────────────────────┐
│ Nombre del Producto *                      │
│ [Remera Urbana Negra              ]        │
│                                            │
│ Descripción                                │
│ [Remera 100% algodón con corte    ]        │
│ [moderno. Talles: S, M, L, XL     ]        │
│                                            │
│ Precio *        │ Stock                    │
│ [2500.00]       │ [50]                     │
│                                            │
│ Categoría       │ Badge                    │
│ [Remeras]       │ [Nuevo]                  │
│                                            │
│ URL de Imagen                              │
│ [https://imgur.com/abc123.jpg    ]        │
│                                            │
│    [Cancelar]         [Guardar Producto]  │
└────────────────────────────────────────────┘
```

#### Paso 2: Sistema Guarda en Base de Datos

```sql
INSERT INTO products (
    site_id, name, description, price, 
    image_url, category, badge, stock
) VALUES (
    1, 'Remera Urbana Negra', 'Remera 100% algodón...', 2500.00,
    'https://imgur.com/abc123.jpg', 'Remeras', 'Nuevo', 50
);
```

#### Paso 3: Aparece Automáticamente en la Tienda

El producto se muestra **inmediatamente** en:
- `https://tiendaropa.com` (tienda pública)
- Panel de admin del cliente
- API de productos

**Sin necesidad de que tú hagas nada.** ✅

---

### Cómo el Cliente Edita Precios

#### Escenario Real:

```
Cliente: "Quiero hacer una oferta del 20% en remeras"

Antes ($2500):
┌────────────────────────────────────────┐
│ Remera Urbana Negra                    │
│ Precio: $2500.00                       │
│             [✏️ Editar] [🗑️ Eliminar]  │
└────────────────────────────────────────┘

Cliente hace click en [✏️ Editar]:
┌────────────────────────────────────────┐
│ Precio *                               │
│ [2000.00]  ← Cambia de 2500 a 2000    │
│                                        │
│ Badge                                  │
│ [⭐ Oferta 20% OFF] ← Agrega badge    │
│                                        │
│            [Guardar Cambios]          │
└────────────────────────────────────────┘

Después ($2000):
┌────────────────────────────────────────┐
│ Remera Urbana Negra                    │
│ Precio: $2000.00  [⭐ Oferta 20% OFF]  │
│             [✏️ Editar] [🗑️ Eliminar]  │
└────────────────────────────────────────┘
```

**Cambio es instantáneo en la tienda pública.** ⚡

---

### Cómo el Cliente Actualiza Stock

#### Cuando se vende o llega mercadería:

```
Producto: Remera Urbana Negra
Stock actual: 50 unidades

Cliente edita:
┌────────────────────────────────────────┐
│ Stock                                  │
│ [30]  ← Vendió 20, quedan 30           │
│                                        │
│            [Actualizar]                │
└────────────────────────────────────────┘

Si stock = 0:
- El producto muestra: "Sin stock"
- Se oculta automáticamente (opcional)
- Cliente recibe notificación (futuro)
```

---

## 🛒 Gestión de Pedidos

### Cómo Funcionan los Pedidos

#### 1. Cliente Final Hace Pedido en la Tienda:

```
Usuario en tiendaropa.com:
1. Agrega productos al carrito
2. Click "Finalizar Compra"
3. Completa datos de envío
4. Sistema crea pedido en BD

INSERT INTO orders (
    site_id, user_id, order_number, total,
    status, shipping_address...
) VALUES (...);
```

#### 2. Dueño de la Tienda Ve el Pedido INSTANTÁNEAMENTE:

```
Panel Admin > Pedidos:

┌────────────────────────────────────────────────────────────┐
│ N° Pedido    │ Cliente      │ Fecha    │ Total  │ Estado  │
├────────────────────────────────────────────────────────────┤
│ #1-20241122  │ María López  │ Hoy      │ $5,200 │ 🟡 Pendiente │
│ #1-20241121  │ Juan Pérez   │ Ayer     │ $3,500 │ ✅ Enviado   │
│ #1-20241120  │ Ana García   │ 2 días   │ $1,800 │ ✅ Entregado │
└────────────────────────────────────────────────────────────┘

Click en pedido para ver detalles:
┌────────────────────────────────────────────┐
│ PEDIDO #1-20241122                         │
├────────────────────────────────────────────┤
│ Cliente: María López                       │
│ Email: maria@email.com                     │
│ Teléfono: +54 9 11 1234-5678              │
│                                            │
│ Dirección de envío:                        │
│ Calle Falsa 123, Piso 4A                  │
│ Buenos Aires, CABA                         │
│                                            │
│ PRODUCTOS:                                 │
│ • Remera Urbana Negra x2    $5,000        │
│ • Gorra Deportiva x1         $1,200        │
│                                            │
│ TOTAL: $6,200                              │
│                                            │
│ Estado actual: 🟡 Pendiente               │
│                                            │
│ [Confirmar] [Marcar como Enviado]         │
└────────────────────────────────────────────┘
```

---

## 🚚 Seguimiento de Envíos

### Sistema de Tracking

#### Cuando el Cliente Despacha un Pedido:

```
Panel Admin > Pedidos > [Ver Pedido #1-20241122]

┌────────────────────────────────────────────┐
│ MARCAR COMO ENVIADO                        │
├────────────────────────────────────────────┤
│ Empresa de envío:                          │
│ [Correo Argentino ▼]                       │
│                                            │
│ Número de seguimiento:                     │
│ [RA123456789AR            ]                │
│                                            │
│ URL de tracking (opcional):                │
│ [https://correo...        ]                │
│                                            │
│ Fecha estimada de entrega:                 │
│ [25/11/2024]                               │
│                                            │
│            [Guardar y Notificar Cliente]  │
└────────────────────────────────────────────┘
```

#### Sistema Automático:

```sql
-- Se crea registro de tracking
INSERT INTO shipping_tracking (
    order_id, carrier, tracking_number,
    tracking_url, status, estimated_delivery
) VALUES (
    15, 'Correo Argentino', 'RA123456789AR',
    'https://correo...', 'in_transit', '2024-11-25'
);

-- Se actualiza el pedido
UPDATE orders 
SET status = 'shipped' 
WHERE id = 15;
```

#### Cliente Final Recibe Notificación:

```
📧 EMAIL AUTOMÁTICO:

¡Tu pedido está en camino! 📦

Pedido: #1-20241122
Empresa: Correo Argentino
Tracking: RA123456789AR

🔍 Seguí tu envío:
https://correoargentino.com.ar/track?code=RA123456789AR

Entrega estimada: 25 de Noviembre

¿Dudas? Contactanos en tiendaropa.com/contacto
```

#### En el Panel del Cliente (Dueño):

```
Panel Admin > Envíos:

┌────────────────────────────────────────────────────────────┐
│ ENVÍOS ACTIVOS                                             │
├────────────────────────────────────────────────────────────┤
│ Pedido #1-20241122                                         │
│ Cliente: María López                                       │
│ Correo Argentino - RA123456789AR                          │
│                                                            │
│ Timeline:                                                  │
│ ● 22/11 10:30 - Paquete despachado (Buenos Aires)        │
│ ● 23/11 08:15 - En tránsito (Centro de distribución)     │
│ ○ 25/11       - Entrega estimada                          │
│                                                            │
│ Estado: 🚚 En tránsito                                    │
│                                                            │
│ [Actualizar Estado] [Contactar Cliente]                   │
└────────────────────────────────────────────────────────────┘
```

---

## 🔄 Flujo Completo de un Pedido

### De Principio a Fin:

```
1. PEDIDO CREADO (automático)
   └─ Usuario hace compra en tienda pública
   └─ Sistema crea registro en BD
   └─ Dueño ve pedido en panel
   └─ Estado: 🟡 Pendiente

2. DUEÑO CONFIRMA (manual)
   └─ Revisa productos disponibles
   └─ Confirma que puede cumplir
   └─ Click "Confirmar Pedido"
   └─ Estado: ✅ Confirmado
   └─ Cliente recibe notificación

3. DUEÑO PREPARA ENVÍO (manual)
   └─ Empaca productos
   └─ Va al correo/empresa de envío
   └─ Obtiene número de tracking
   └─ Ingresa datos en panel
   └─ Estado: 📦 Enviado
   └─ Cliente recibe email con tracking

4. PRODUCTO EN TRÁNSITO (automático/manual)
   └─ Dueño puede actualizar ubicación
   └─ Cliente puede consultar estado
   └─ Estado: 🚚 En tránsito

5. PRODUCTO ENTREGADO (manual)
   └─ Correo entrega al cliente
   └─ Cliente confirma recepción (opcional)
   └─ Dueño marca como entregado
   └─ Estado: ✅ Entregado
   └─ Ciclo completado
```

---

## ⚙️ Configuración del Sitio

### Qué Puede Cambiar el Cliente:

```
Panel Admin > Configuración:

┌────────────────────────────────────────────┐
│ INFORMACIÓN GENERAL                        │
├────────────────────────────────────────────┤
│ Nombre de la Tienda:                       │
│ [Tienda Ropa Urban]                        │
│                                            │
│ Email de Contacto:                         │
│ [contacto@tiendaropa.com]                  │
│                                            │
│ Teléfono:                                  │
│ [+54 9 11 1234-5678]                       │
│                                            │
│ WhatsApp (solo números):                   │
│ [5491112345678]                            │
│                                            │
│            [Guardar Cambios]              │
└────────────────────────────────────────────┘
```

**Estos cambios se reflejan automáticamente en la tienda pública.**

---

## 📊 Lo Que TÚ Puedes Ver (Super Admin)

### Dashboard de TODOS tus Clientes:

```
Tu Panel Principal:
https://tuservidor.com/super-admin/

┌────────────────────────────────────────────────────────────┐
│ MIS CLIENTES                                               │
├────────────────────────────────────────────────────────────┤
│ Tienda          │ Productos │ Pedidos  │ Estado │ Pago    │
├────────────────────────────────────────────────────────────┤
│ Tienda Ropa     │ 24        │ 156      │ ✅      │ Al día │
│ Servicios Juan  │ -         │ 45       │ ✅      │ Al día │
│ Tech Store      │ 18        │ 89       │ ⚠️      │ Vence  │
└────────────────────────────────────────────────────────────┘

Estadísticas Globales:
┌──────────────────────────────────────────┐
│ Total Clientes Activos: 45               │
│ Ingresos Mensuales: $18,000              │
│ Pedidos este mes: 1,245                  │
│ Productos totales: 2,156                 │
└──────────────────────────────────────────┘
```

---

## 💡 Preguntas Frecuentes

### ¿Los clientes necesitan saber programar?

**NO.** El panel es 100% visual. Solo necesitan:
- Saber usar un navegador
- Completar formularios simples
- Click en botones

### ¿Cómo suben las fotos de productos?

**Opción 1:** Imgur.com (gratis, fácil)
```
1. Cliente va a imgur.com
2. Sube foto
3. Copia URL
4. Pega en campo "URL de Imagen"
```

**Opción 2:** Google Drive (público)
**Opción 3:** Tú les ofreces subir fotos por WhatsApp y lo haces ($5-10 extra)

### ¿Qué pasa si cambian un precio y ya hay pedidos viejos?

**Los pedidos históricos NO cambian.** 

Cada pedido guarda el precio **al momento de la compra**:
```sql
-- Tabla order_items guarda el precio histórico
product_price: 2500.00  ← Precio cuando compró

-- Aunque el producto ahora cueste 2000
-- El pedido viejo sigue mostrando 2500
```

### ¿Pueden los clientes eliminar productos?

**Sí, pero es "soft delete":**
- El producto se marca como `active = 0`
- Desaparece de la tienda pública
- Sigue en la base de datos para reportes
- Los pedidos viejos siguen mostrándolo

### ¿El seguimiento de envíos es automático?

**Semi-automático:**
1. Cliente ingresa número de tracking **manualmente**
2. Sistema genera email automático
3. Actualizaciones de estado: **manuales**

**Futuro:** Integración con API de Correo Argentino/Andreani para tracking automático.

---

## 💰 Qué Cobrar por Esto

### Servicios Incluidos en la Mensualidad:

```
$40-60/mes incluye:
✅ Hosting en tu servidor
✅ Base de datos MySQL
✅ Gestión ilimitada de productos
✅ Pedidos ilimitados
✅ Panel de administración
✅ Seguimiento de envíos
✅ Soporte por email (24-48hs)

Servicios Extra:
💵 Subir fotos de productos: $5 por 10 fotos
💵 Capacitación 1-a-1: $30/hora
💵 Personalización de diseño: $50-100
💵 Integración con MercadoPago: $100 setup
💵 Backup mensual en CD: $10/mes
```

---

## 🎓 Tutorial para Entregar al Cliente

### Script de Onboarding (Email/Video):

```
"Hola Juan,

Te explico rápido cómo usar tu panel:

1️⃣ AGREGAR PRODUCTOS
   - Panel > Productos > ➕ Agregar
   - Completá nombre, precio, descripción
   - Subí foto a imgur.com y pegá URL
   - Guardar

2️⃣ CAMBIAR PRECIOS
   - Buscá el producto en la lista
   - Click en ✏️ Editar
   - Cambiá el precio
   - Guardar

3️⃣ VER PEDIDOS
   - Panel > Pedidos
   - Ahí ves todos los pedidos en tiempo real
   - Click en cada uno para ver detalles

4️⃣ MARCAR COMO ENVIADO
   - Abrí el pedido
   - Click "Marcar como Enviado"
   - Ingresá número de tracking
   - El cliente recibe email automático

¿Dudas? Respondeme este email o llamame.

Video tutorial (5 min): [link]

Saludos,
Tu Nombre
```

---

## ✅ Resumen Final

### Sistema Completamente Automatizado:

| Acción | Quién | Cómo |
|--------|-------|------|
| **Crear sitio** | TÚ | Make.com + GPT-4o |
| **Agregar productos** | CLIENTE | Panel admin |
| **Cambiar precios** | CLIENTE | Panel admin |
| **Ver pedidos** | CLIENTE | Panel admin (tiempo real) |
| **Actualizar envíos** | CLIENTE | Panel admin |
| **Notificar clientes** | SISTEMA | Automático |
| **Cobrar mensualidad** | TÚ | Manual o automático |
| **Soporte técnico** | TÚ | Solo si el cliente pregunta |

**Resultado: Cliente 100% autónomo, tú cobras mensualidad sin esfuerzo.** 🚀

---

**¿Preguntas? Seguimos con la implementación técnica.**
