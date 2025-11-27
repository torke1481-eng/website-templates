# 📂 ESTRUCTURA HOSTINGER V2 - MULTI-DOMINIO

**Versión:** 2.0  
**Fecha:** 24 Nov 2025  
**Tipo:** Arquitectura Multi-Tenant para clientes con dominios propios

---

## 🗂️ NUEVA ESTRUCTURA COMPLETA

```
/home/u123456789/                          # Usuario Hostinger
│
├── public_html/                           # ⭐ TU SITIO PRINCIPAL
│   ├── index.html                         # Landing principal
│   ├── .htaccess                          # Config Apache
│   ├── download.php                       # Gestor de descargas temporales
│   ├── admin/                             # Panel admin (futuro)
│   │   ├── login.php
│   │   ├── dashboard.php
│   │   └── assets/
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── domains/                               # ⭐ SITIOS DE CLIENTES
│   │
│   ├── clientenegocio.com/               # Cliente 1
│   │   ├── public_html/                  # ← Dominio apunta AQUÍ
│   │   │   ├── index.html
│   │   │   ├── css/
│   │   │   │   ├── styles.css
│   │   │   │   ├── header-styles.css
│   │   │   │   ├── footer-styles.css
│   │   │   │   └── custom.css
│   │   │   ├── js/
│   │   │   │   ├── header.js
│   │   │   │   ├── main.js
│   │   │   │   └── chatbot.js
│   │   │   ├── images/
│   │   │   │   ├── hero.jpg
│   │   │   │   └── favicon.svg
│   │   │   └── .htaccess              # Security + redirects
│   │   │
│   │   ├── logs/                       # Logs del cliente
│   │   │   ├── access.log
│   │   │   └── error.log
│   │   │
│   │   ├── backups/                    # Backups automáticos
│   │   │   ├── backup-2025-11-24.tar.gz
│   │   │   ├── backup-2025-11-23.tar.gz
│   │   │   └── ... (últimos 7)
│   │   │
│   │   └── .metadata.json              # Info del cliente
│   │
│   ├── otroejemplo.com/                # Cliente 2
│   │   └── ... (misma estructura)
│   │
│   └── tercerocliente.com.ar/          # Cliente 3
│       └── ...
│
├── _system/                              # ⭐ SISTEMA INTERNO (PROTEGIDO)
│   │
│   ├── generator/                       # Scripts PHP generadores
│   │   ├── deploy-v4-mejorado.php      # Generador principal
│   │   ├── create-domain.php           # Nuevo dominio
│   │   ├── backup-client.php           # Backup individual
│   │   ├── backup-all.php              # Backup todos
│   │   ├── health-check.php            # Monitoreo
│   │   ├── export-client.php           # Export para migración
│   │   ├── verify-domain.php           # Verificar config
│   │   └── cleanup-old.php             # Limpiar staging
│   │
│   ├── templates/                       # Templates base
│   │   ├── landing-pro/
│   │   │   ├── index.html
│   │   │   ├── styles.css
│   │   │   ├── script.js
│   │   │   └── config.json
│   │   ├── landing-basica/
│   │   └── componentes-globales/
│   │       ├── header/
│   │       ├── footer/
│   │       └── chatbot/
│   │
│   ├── queue/                           # Cola procesamiento async
│   │   ├── queue-abc123.json
│   │   └── processed/
│   │
│   ├── logs/                            # Logs del sistema
│   │   ├── generator.log
│   │   ├── errors/
│   │   │   ├── 2025-11-24.log
│   │   │   └── 2025-11-23.log
│   │   ├── health/
│   │   │   └── health-2025-11-24.json
│   │   └── backups.log
│   │
│   ├── config/                          # Configuración
│   │   ├── clients.json                # Lista clientes
│   │   ├── domains.json                # Mapeo dominios
│   │   ├── downloads.json              # Links descarga temp
│   │   └── limits.json                 # Límites sistema
│   │
│   ├── backups/                         # Backups del sistema
│   │   ├── templates-2025-11-24.tar.gz
│   │   └── config-2025-11-24.tar.gz
│   │
│   └── exports/                         # Exports para clientes
│       ├── cliente1.com-export.tar.gz
│       └── ...
│
└── staging/                              # ⭐ STAGING TEMPORAL
    ├── preview-token123/                # Preview expira en 7 días
    │   ├── index.html
    │   └── ...
    └── preview-token456/
```

---

## 🔗 URLs Y MAPEO

### **Tu Sitio Principal:**
```
https://otavafitness.com/
→ /home/u123456/public_html/
```

### **Sitios de Clientes:**
```
https://clientenegocio.com/
→ /home/u123456/domains/clientenegocio.com/public_html/

https://otroejemplo.com/
→ /home/u123456/domains/otroejemplo.com/public_html/
```

### **Staging/Preview:**
```
https://otavafitness.com/staging/preview-token123/
→ /home/u123456/staging/preview-token123/
(Auto-elimina después de 7 días)
```

### **Sistema (Protegido):**
```
https://otavafitness.com/_system/
→ 403 Forbidden (bloqueado por .htaccess)
```

---

## 🔒 SEGURIDAD

### **1. Permisos de Carpetas:**
```bash
# Sitios clientes (público)
chmod 755 /domains/*/public_html/
chmod 644 /domains/*/public_html/*.html

# Sistema (privado)
chmod 700 /_system/
chmod 700 /_system/config/
chmod 600 /_system/config/*.json
chmod 700 /_system/generator/
chmod 700 /_system/logs/

# Backups (privado)
chmod 700 /domains/*/backups/
chmod 600 /domains/*/backups/*.tar.gz
```

### **2. .htaccess Principal:**
```apache
# /public_html/.htaccess

# Bloquear acceso a _system
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^_system/ - [F,L]
</IfModule>

# Bloquear archivos sensibles
<FilesMatch "\.(json|log|bak|sql|sh|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### **3. .htaccess Por Cliente:**
```apache
# /domains/clientenegocio.com/public_html/.htaccess

Options -Indexes
RewriteEngine On

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [L,R=301]

# Forzar WWW o sin WWW
RewriteCond %{HTTP_HOST} !^clientenegocio\.com$ [NC]
RewriteRule ^(.*)$ https://clientenegocio.com/$1 [R=301,L]

# Bloquear archivos
<FilesMatch "\.(json|log)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security headers
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## 📋 METADATA.JSON

```json
{
    "domain": "clientenegocio.com",
    "client": {
        "name": "Juan Pérez",
        "email": "juan@clientenegocio.com",
        "phone": "+54 11 1234-5678",
        "company": "Cliente Negocio SA"
    },
    "template": {
        "type": "landing-pro",
        "version": "2.0"
    },
    "created_at": "2025-11-24T10:30:00-03:00",
    "updated_at": "2025-11-24T10:30:00-03:00",
    "status": "active",
    "dns_status": "configured",
    "ssl_enabled": true,
    "ssl_expires": "2026-02-24",
    "backup_enabled": true,
    "last_backup": "2025-11-24T03:00:00-03:00",
    "disk_usage_mb": 8.5,
    "monthly_visits": 1250,
    "plan": "premium",
    "billing": {
        "amount": 15,
        "currency": "USD",
        "period": "monthly",
        "next_payment": "2025-12-24"
    }
}
```

---

## 🔄 FLUJO DE DEPLOYMENT

### **1. Cliente Envía Formulario:**
```
Google Forms → Make.com Webhook
```

### **2. Make.com Procesa:**
```
Set Variables → HTTP Get Image → GPT-4o Vision
→ Parse JSON → HTTP POST deploy-v4-mejorado.php
```

### **3. Sistema Genera (< 2s):**
```php
// Respuesta inmediata a Make.com
echo json_encode(['queue_id' => $queueId, 'status' => 'queued']);
fastcgi_finish_request();

// Procesamiento async
1. Generar HTML con placeholders reemplazados
2. Copiar a /staging/preview-token123/
3. Enviar email con preview link
```

### **4. Cliente Aprueba:**
```
Email: "Sitio listo: https://tudominio.com/staging/preview-token123/"
Cliente revisa y aprueba
```

### **5. Activación de Dominio:**
```
Manual:
1. Cliente compra dominio (ej: GoDaddy)
2. Tú ejecutas: php create-domain.php clientenegocio.com
3. Carpetas creadas en /domains/clientenegocio.com/
4. Sitio copiado de staging a domains
5. Manual cPanel: Agregar Addon Domain
6. Cliente configura DNS (A records)
7. 24-48hs: DNS propaga
8. Sistema verifica: php verify-domain.php clientenegocio.com
9. SSL auto-configura (Let's Encrypt)
10. Email: "Dominio activo!"
```

---

## ⚙️ CRON JOBS

```cron
# /etc/cron.d/website-system

# Backup diario de todos los clientes (3 AM)
0 3 * * * /usr/bin/php /_system/generator/backup-all.php >> /_system/logs/backups.log 2>&1

# Health check cada hora
0 * * * * /usr/bin/php /_system/generator/health-check.php >> /_system/logs/health.log 2>&1

# Limpiar staging viejo (diario a las 4 AM)
0 4 * * * /usr/bin/php /_system/generator/cleanup-old.php >> /_system/logs/cleanup.log 2>&1

# Verificar SSL vencimiento (semanal, lunes 2 AM)
0 2 * * 1 /usr/bin/php /_system/generator/check-ssl.php >> /_system/logs/ssl.log 2>&1
```

---

## 📊 LÍMITES Y MONITOREO

### **Límites Hostinger Business:**
```json
{
    "plan": "business",
    "limits": {
        "domains": "unlimited",
        "disk_space_gb": 100,
        "bandwidth": "unlimited",
        "inodes": 300000,
        "cpu": "2 cores shared",
        "ram_gb": 3
    },
    "our_limits": {
        "max_client_sites": 500,
        "max_disk_per_site_mb": 20,
        "max_total_disk_gb": 80,
        "alert_threshold_percent": 75
    }
}
```

### **Monitoreo Automático:**
```
Health Check (cada hora):
- DNS OK?
- HTTP 200?
- SSL válido?
- Archivos existen?
- Score: 0-100

Alertas:
- Score < 50: Email/Slack
- Disk > 75%: Email admin
- SSL expira < 30 días: Email
```

---

## ✅ CHECKLIST DE MIGRACIÓN

- [ ] Crear carpeta `/_system/`
- [ ] Mover `/generator/` → `/_system/generator/`
- [ ] Mover `/templates/` → `/_system/templates/`
- [ ] Crear carpeta `/domains/`
- [ ] Actualizar deploy-v4-mejorado.php con nuevas rutas
- [ ] Crear create-domain.php
- [ ] Crear backup-client.php y backup-all.php
- [ ] Crear health-check.php
- [ ] Crear cleanup-old.php
- [ ] Configurar cron jobs
- [ ] Crear .htaccess de seguridad
- [ ] Probar con 1 dominio test
- [ ] Documentar proceso para team
- [ ] Migrar sitios existentes de /staging/

---

**Última actualización:** 24 Nov 2025, 01:30 AM  
**Versión:** 2.0 Multi-Domain  
**Estado:** ⏳ PENDIENTE IMPLEMENTACIÓN
