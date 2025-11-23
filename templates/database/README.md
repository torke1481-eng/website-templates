# 🗄️ Sistema Multi-Tenancy con MySQL

Sistema de base de datos centralizado para gestionar **todos tus clientes en una sola base de datos MySQL**.

---

## 🎯 Concepto Clave: Multi-Tenancy

### ❌ LO QUE NO HARÁS (Ineficiente):

```
Cliente 1 → Base de datos 1
Cliente 2 → Base de datos 2
Cliente 3 → Base de datos 3
...
Cliente 50 → Base de datos 50  😱
```

**Problemas:**
- 50 bases de datos que gestionar
- 50 backups separados
- Cambios en estructura = actualizar 50 veces
- Pesadilla de mantenimiento

### ✅ LO QUE HARÁS (Multi-Tenancy):

```
TODOS LOS CLIENTES → UNA SOLA BASE DE DATOS
├── Cliente 1 (site_id = 1)
├── Cliente 2 (site_id = 2)
├── Cliente 3 (site_id = 3)
└── ...
```

**Ventajas:**
- ✅ Una sola conexión MySQL
- ✅ Un solo backup
- ✅ Actualizaciones centralizadas
- ✅ Fácil de mantener
- ✅ Escalable a cientos de clientes

---

## 🏗️ Arquitectura del Sistema

### Tablas Principales:

```
sites (TUS CLIENTES)
  └── users (USUARIOS FINALES)
       └── orders (PEDIDOS)
            └── order_items (DETALLES)
  └── products (PRODUCTOS)
```

### Flujo de Datos:

1. **Cliente visita:** `tienda-ropa.com`
2. **Sistema identifica:** `site_id = 1` (desde tabla `sites`)
3. **Usuario se registra:** Se guarda en tabla `users` con `site_id = 1`
4. **Usuario hace pedido:** Se guarda en tabla `orders` con `site_id = 1`
5. **Tú ves dashboard:** Solo datos de `site_id = 1`

**Los datos están separados lógicamente, no físicamente.**

---

## 📊 Ejemplo Visual

### Tabla: sites

| id | site_name | domain | template_type | owner_email |
|----|-----------|--------|---------------|-------------|
| 1 | Tienda Ropa | ropa.com | ecommerce-auth | juan@email.com |
| 2 | Servicios Pro | servicios.com | landing | maria@email.com |
| 3 | Tech Store | tech.com | ecommerce | luis@email.com |

### Tabla: users

| id | site_id | email | name | Pertenece a: |
|----|---------|-------|------|--------------|
| 1 | 1 | cliente1@gmail.com | Ana | Tienda Ropa |
| 2 | 1 | cliente2@gmail.com | Pedro | Tienda Ropa |
| 3 | 2 | user@yahoo.com | Carlos | Servicios Pro |
| 4 | 3 | comprador@hotmail.com | Laura | Tech Store |

### Tabla: orders

| id | site_id | user_id | total | Pedido de: |
|----|---------|---------|-------|------------|
| 1 | 1 | 1 | 3500 | Ana en Tienda Ropa |
| 2 | 1 | 2 | 1200 | Pedro en Tienda Ropa |
| 3 | 3 | 4 | 5600 | Laura en Tech Store |

**Todos en una sola base de datos, pero separados por `site_id`.**

---

## 🚀 Instalación

### Paso 1: Crear Base de Datos

```bash
# Conéctate a MySQL
mysql -u root -p

# Ejecuta el schema
mysql -u root -p < schema.sql
```

O desde phpMyAdmin:
1. Crear base de datos `sitios_clientes`
2. Importar `schema.sql`

### Paso 2: Configurar Conexión

Edita `config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sitios_clientes');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_password');

// CAMBIAR ESTAS CLAVES DE SEGURIDAD:
define('JWT_SECRET', 'clave_super_secreta_unica');
define('PASSWORD_SALT', 'otro_salt_super_seguro');
```

### Paso 3: Estructura de Archivos

```
tu-servidor/
├── database/
│   ├── config.php           ← Configuración
│   ├── schema.sql           ← Estructura de BD
│   └── api/
│       ├── auth.php         ← Login/Registro
│       ├── profile.php      ← Perfil de usuario
│       └── orders.php       ← Pedidos
│
└── clientes/
    ├── cliente1/
    │   └── public_html/     ← Template del cliente
    ├── cliente2/
    │   └── public_html/
    └── ...
```

### Paso 4: Permisos

```bash
chmod 755 database/api/
chmod 644 database/api/*.php
chmod 600 database/config.php  # Solo tú puedes leer
```

---

## 🔌 APIs Disponibles

### 1. Autenticación (`api/auth.php`)

#### Registrar Usuario

```javascript
fetch('https://tuservidor.com/database/api/auth.php?domain=cliente.com', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        action: 'register',
        name: 'Juan Pérez',
        email: 'juan@email.com',
        password: 'mipassword123'
    })
})
.then(res => res.json())
.then(data => {
    console.log(data.data.token); // Guardar token
});
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Usuario registrado exitosamente",
    "data": {
        "user": {
            "id": 15,
            "name": "Juan Pérez",
            "email": "juan@email.com"
        },
        "token": "a1b2c3d4e5f6...",
        "expires_at": "2024-12-22 18:30:00"
    }
}
```

#### Login

```javascript
fetch('https://tuservidor.com/database/api/auth.php?domain=cliente.com', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        action: 'login',
        email: 'juan@email.com',
        password: 'mipassword123'
    })
})
```

#### Verificar Sesión

```javascript
fetch('https://tuservidor.com/database/api/auth.php?domain=cliente.com', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        action: 'verify',
        token: 'a1b2c3d4e5f6...'
    })
})
```

#### Logout

```javascript
fetch('https://tuservidor.com/database/api/auth.php?domain=cliente.com', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        action: 'logout',
        token: 'a1b2c3d4e5f6...'
    })
})
```

---

### 2. Perfil (`api/profile.php`)

#### Obtener Perfil

```javascript
fetch('https://tuservidor.com/database/api/profile.php?token=a1b2c3d4...', {
    method: 'GET'
})
```

#### Actualizar Perfil

```javascript
fetch('https://tuservidor.com/database/api/profile.php', {
    method: 'PUT',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        token: 'a1b2c3d4...',
        name: 'Juan Pérez Actualizado',
        phone: '+54 9 11 1234-5678',
        address: 'Calle Falsa 123, Buenos Aires'
    })
})
```

---

### 3. Pedidos (`api/orders.php`)

#### Crear Pedido

```javascript
fetch('https://tuservidor.com/database/api/orders.php?domain=cliente.com', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        token: 'a1b2c3d4...',  // Opcional si no está logueado
        items: [
            {
                id: 1,
                name: 'Producto A',
                price: 1500,
                quantity: 2
            },
            {
                id: 2,
                name: 'Producto B',
                price: 2500,
                quantity: 1
            }
        ],
        total: 5500,
        guest_name: 'Juan Pérez',
        guest_email: 'juan@email.com',
        guest_phone: '+54 9 11 1234-5678',
        shipping_address: 'Calle Falsa 123',
        shipping_city: 'Buenos Aires',
        customer_notes: 'Entregar en horario laboral'
    })
})
```

#### Listar Pedidos del Usuario

```javascript
fetch('https://tuservidor.com/database/api/orders.php?domain=cliente.com&token=a1b2c3d4...', {
    method: 'GET'
})
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "orders": [
            {
                "id": 15,
                "order_number": "1-20241122-A1B2C3",
                "total": 5500,
                "status": "pending",
                "created_at": "2024-11-22 18:30:00",
                "items": [
                    {
                        "product_name": "Producto A",
                        "product_price": 1500,
                        "quantity": 2,
                        "subtotal": 3000
                    }
                ]
            }
        ]
    }
}
```

---

## 🔐 Seguridad

### Contraseñas

```php
// Las contraseñas se guardan con bcrypt (costo 12)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verificación segura
password_verify($password_input, $hash_guardado);
```

**Nunca se guardan contraseñas en texto plano.**

### Sesiones

- Token único de 64 caracteres hexadecimales
- Expiración: 30 días por defecto
- Vinculado a IP y User-Agent (opcional)
- Limpieza automática de sesiones expiradas

### SQL Injection

- Uso de **PDO con prepared statements**
- Todos los inputs sanitizados
- Sin concatenación directa de SQL

---

## 💰 Costos

### Hosting con MySQL:

| Proveedor | Plan | Precio/Mes | Bases de Datos |
|-----------|------|------------|----------------|
| **DigitalOcean** | Droplet básico | $5 | Ilimitadas |
| **Vultr** | Cloud Compute | $5 | Ilimitadas |
| **Hostinger** | Business | $4 | 100 |
| **cPanel Shared** | - | $3-10 | 10-100 |

**Con $5/mes puedes hospedar decenas de clientes.**

### Cálculo de Rentabilidad:

```
Costo servidor: $5/mes
Cobras por cliente: $40/mes (hosting + BD)

Cliente 1: +$40
Cliente 2: +$40
Cliente 3: +$40
TOTAL: $120/mes

Ganancia neta: $120 - $5 = $115/mes
ROI: 2,300% 🚀
```

---

## 📈 Escalabilidad

### Capacidad por Servidor:

| Servidor | RAM | Clientes | Usuarios Totales |
|----------|-----|----------|------------------|
| Básico | 1GB | 20-30 | 500-1,000 |
| Medio | 2GB | 50-70 | 2,000-5,000 |
| Pro | 4GB | 100-150 | 10,000-20,000 |

### Cuando Escalar:

1. **10-20 clientes:** Servidor básico OK
2. **30-50 clientes:** Upgrade a 2GB RAM
3. **50+ clientes:** Considera servidor dedicado
4. **100+ clientes:** Multi-servidor con load balancer

---

## 🛠️ Mantenimiento

### Backups Automáticos:

```bash
#!/bin/bash
# backup_diario.sh

DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u usuario -p'password' sitios_clientes > backup_$DATE.sql
gzip backup_$DATE.sql

# Subir a cloud (opcional)
aws s3 cp backup_$DATE.sql.gz s3://mis-backups/
```

### Limpieza de Sesiones:

Ya está automatizada en el schema:
```sql
CREATE EVENT daily_cleanup
ON SCHEDULE EVERY 1 DAY
DO CALL cleanup_expired_sessions();
```

---

## 📊 Dashboard Administrativo (Futuro)

Puedes crear un panel para ti:

```
/admin/
├── dashboard.php        ← Estadísticas generales
├── clientes.php         ← Lista de sitios
├── usuarios.php         ← Usuarios por sitio
├── pedidos.php          ← Todos los pedidos
└── facturacion.php      ← Cobros mensuales
```

---

## ❓ Preguntas Frecuentes

### ¿Es seguro tener todo en una BD?

**Sí.** Los datos están **aislados lógicamente** por `site_id`. Un cliente nunca puede ver datos de otro porque las consultas siempre filtran por `site_id`.

### ¿Qué pasa si un cliente no paga?

```sql
-- Desactivar sitio
UPDATE sites SET active = 0 WHERE id = 5;

-- Todos los usuarios de ese sitio quedan inaccesibles
-- El sitio web puede mostrar: "Sitio en mantenimiento"
```

### ¿Puedo migrar un cliente a su propia BD después?

**Sí.** Exportar datos de un cliente:

```sql
-- Exportar sitio específico
SELECT * FROM users WHERE site_id = 5;
SELECT * FROM orders WHERE site_id = 5;
SELECT * FROM products WHERE site_id = 5;
```

### ¿Cuántos clientes soporta?

Una BD MySQL bien configurada puede manejar:
- **Miles de clientes**
- **Millones de usuarios finales**
- **Cientos de miles de pedidos**

---

## 🎯 Resumen

✅ **Una sola base de datos para todos**
✅ **Separación lógica por `site_id`**
✅ **APIs REST ya creadas**
✅ **Sistema de autenticación completo**
✅ **Backups centralizados**
✅ **Escalable a cientos de clientes**
✅ **Costo: $5-10/mes para 20-50 clientes**

**Resultado: Sistema profesional y escalable con mínimo esfuerzo de mantenimiento.** 🚀

---

## 📝 Próximo Paso

**¿Quieres que actualice el Template 3 para usar estas APIs en lugar de LocalStorage?**

Esto convertiría el Template 3 en un sistema 100% profesional con base de datos real.
