# 🚀 PRÓXIMOS PASOS COMPLETOS

**Fecha:** 25 Nov 2025  
**Estado actual:** ✅ Sistema local 100% listo  
**Siguiente:** Migración a Hostinger

---

## 📊 ANÁLISIS FINAL DEL PROYECTO

### ✅ SISTEMA LOCAL PERFECTO

```
_system/
├── generator/          8/8 scripts PHP ✅
├── templates/          6 templates completos ✅
├── config/             domains.json + .env.example ✅
├── logs/               Vacío (listo) ✅
└── queue/              Vacío (listo) ✅

public_html/
└── generator/
    └── deploy.php      Proxy seguro ✅

domains/                Vacío (listo para clientes) ✅
staging/                Vacío (listo para previews) ✅
docs/                   27 archivos documentación ✅

Archivos raíz:
├── .gitignore          ✅
├── .env.example        ✅
├── README.md           ✅
├── .git/               ✅
└── BACKUP_*.zip        ✅ (seguridad)

Score: 100/100 ⭐⭐⭐⭐⭐
```

---

## 🎯 ROADMAP COMPLETO

### **FASE 1: PREPARACIÓN LOCAL (15 min)** ← ESTÁS AQUÍ
- [x] Limpieza completada
- [x] Scripts recuperados
- [x] Estructura verificada
- [ ] Configurar .env
- [ ] Limpiar TEMP_BACKUP

### **FASE 2: MIGRACIÓN A HOSTINGER (30 min)**
- [ ] Conectar FileZilla
- [ ] Subir archivos
- [ ] Configurar permisos
- [ ] Crear carpetas base

### **FASE 3: CONFIGURACIÓN SERVIDOR (20 min)**
- [ ] Configurar cron jobs
- [ ] Configurar .htaccess
- [ ] Verificar instalación

### **FASE 4: MAKE.COM SETUP (10 min)**
- [ ] Actualizar webhook URL
- [ ] Configurar headers
- [ ] Test de conexión

### **FASE 5: TESTING (15 min)**
- [ ] Health check
- [ ] Crear dominio test
- [ ] Verificar preview staging

### **FASE 6: PRODUCCIÓN (5 min)**
- [ ] Primer sitio real
- [ ] Monitoreo activo

---

## 📋 PASO A PASO DETALLADO

---

## FASE 1: PREPARACIÓN LOCAL (15 MIN)

### **1.1 Limpiar carpeta temporal (2 min)**

Quedó una carpeta del script de recuperación que debemos eliminar:

```powershell
# Eliminar carpeta temporal
Remove-Item -Path "TEMP_BACKUP" -Recurse -Force
```

**Verificar:**
```powershell
dir
# No debería aparecer TEMP_BACKUP
```

---

### **1.2 Configurar .env (10 min)** 🔐

Este es el paso MÁS IMPORTANTE antes de subir a Hostinger.

**Paso a paso:**

```powershell
# 1. Ir a config
cd _system\config

# 2. Copiar template
copy .env.example .env

# 3. Abrir para editar
notepad .env
```

**Configuración COMPLETA:**

```bash
# ============================================
# CONFIGURACIÓN DEL SISTEMA
# ============================================

# ============================================
# 1. MAKE.COM SECRET (CRÍTICO)
# ============================================
# Token secreto para validar requests de Make.com
# GENERAR UNO NUEVO (32+ caracteres aleatorios)
MAKE_SECRET=TU_TOKEN_SUPER_SECRETO_AQUI_32_CARACTERES_MINIMO

# ============================================
# 2. NOTIFICACIONES
# ============================================
# Email para recibir alertas del sistema
ADMIN_EMAIL=tu@email.com

# Webhook de Slack (opcional)
# SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/WEBHOOK/URL

# ============================================
# 3. URLS DEL SISTEMA
# ============================================
# URL base de tu dominio principal
BASE_URL=https://otavafitness.com

# URL del staging
STAGING_URL=https://otavafitness.com/staging

# ============================================
# 4. CONFIGURACIÓN DE BACKUPS
# ============================================
# Retención de backups (días)
BACKUP_RETENTION=7

# Límite de espacio para backups (MB)
BACKUP_MAX_SIZE=1000

# ============================================
# 5. CONFIGURACIÓN DE STAGING
# ============================================
# Edad máxima de previews en staging (días)
STAGING_MAX_AGE=7

# ============================================
# 6. LÍMITES DEL SISTEMA
# ============================================
# Rate limit para el proxy (requests por minuto)
RATE_LIMIT=10

# Tamaño máximo de payload (MB)
MAX_PAYLOAD_SIZE=5

# ============================================
# 7. DEBUGGING (PRODUCCIÓN = false)
# ============================================
# Activar logs detallados
DEBUG=false

# Nivel de log: error, warning, info, debug
LOG_LEVEL=warning
```

**Generar MAKE_SECRET seguro:**

```powershell
# Opción 1: PowerShell
$bytes = New-Object byte[] 32
[Security.Cryptography.RNGCryptoServiceProvider]::Create().GetBytes($bytes)
[Convert]::ToBase64String($bytes)

# Opción 2: Online (seguro)
# https://www.random.org/strings/?num=1&len=32&digits=on&upperalpha=on&loweralpha=on&unique=on&format=html&rnd=new
```

**Resultado esperado:**
```
Ejemplo: 7xK9mP2nQ4vR8sT1wY5zA3bC6dE0fG4h
```

**Copiar ese token a .env:**
```bash
MAKE_SECRET=7xK9mP2nQ4vR8sT1wY5zA3bC6dE0fG4h
```

**GUARDAR el archivo .env**

**⚠️ MUY IMPORTANTE:**
- Este token DEBE coincidir con el que configures en Make.com
- Guarda una copia segura del token (en tu gestor de contraseñas)
- NO compartas este token con nadie

---

### **1.3 Verificar .env (1 min)**

```powershell
# Ver contenido
type .env

# Verificar que tenga:
# - MAKE_SECRET con valor real (no el ejemplo)
# - ADMIN_EMAIL con tu email
# - BASE_URL correcta
```

---

### **1.4 Volver a raíz (1 min)**

```powershell
cd ..\..
# Ahora deberías estar en: public_html (3)\
```

---

## FASE 2: MIGRACIÓN A HOSTINGER (30 MIN)

### **2.1 Conectar FileZilla (5 min)**

**Datos de conexión (desde cPanel):**
```
Host:       ftp.otavafitness.com
Usuario:    u123456789
Contraseña: [tu contraseña cPanel]
Puerto:     21
```

**Conectar:**
1. Abrir FileZilla
2. Archivo → Gestor de sitios → Nuevo sitio
3. Ingresar datos
4. Conectar

**Verificar conexión:**
```
Debería mostrar: /home/u123456789/
```

---

### **2.2 Subir _system/ (10 min)**

**En FileZilla:**

**Panel izquierdo (local):**
```
Ir a: C:\Users\franc\OneDrive\Escritorio\public_html (3)\_system
```

**Panel derecho (servidor):**
```
Ir a: /home/u123456789/
```

**Acción:**
1. Seleccionar carpeta `_system` del panel izquierdo
2. Arrastrar al panel derecho
3. Esperar a que termine (puede tardar 5-10 min)

**Verificar en servidor:**
```
/home/u123456789/_system/
├── generator/      (8 archivos PHP)
├── templates/      (6 carpetas)
├── config/         (.env, domains.json)
├── logs/           (vacío)
└── queue/          (vacío)
```

---

### **2.3 Subir public_html/generator/ (5 min)**

**Panel izquierdo:**
```
Ir a: public_html (3)\public_html\generator
```

**Panel derecho:**
```
Ir a: /home/u123456789/public_html/
```

**Acción:**
1. Si no existe carpeta `generator/` en servidor, crearla
2. Arrastrar `deploy.php` a `/home/u123456789/public_html/generator/`

**Verificar:**
```
/home/u123456789/public_html/generator/deploy.php ✓
```

---

### **2.4 Crear carpetas domains/ y staging/ (2 min)**

**En FileZilla, panel derecho:**

```
Crear carpeta: /home/u123456789/domains
Crear carpeta: /home/u123456789/staging
```

**Clic derecho → Crear directorio**

---

### **2.5 Verificar estructura en servidor (2 min)**

**Estructura final en Hostinger:**

```
/home/u123456789/
├── _system/
│   ├── generator/
│   ├── templates/
│   ├── config/
│   ├── logs/
│   └── queue/
│
├── public_html/
│   └── generator/
│       └── deploy.php
│
├── domains/        (vacío)
└── staging/        (vacío)
```

---

## FASE 3: CONFIGURACIÓN SERVIDOR (20 MIN)

### **3.1 Configurar permisos (5 min)** 🔐

**Opción A: Terminal SSH (recomendado)**

```bash
# Conectar por SSH desde cPanel
# Terminal → SSH Access

# 1. Permisos de _system
chmod 700 /home/u123456789/_system
chmod 755 /home/u123456789/_system/generator
chmod 755 /home/u123456789/_system/generator/*.php

# 2. Permisos de config (CRÍTICO)
chmod 700 /home/u123456789/_system/config
chmod 600 /home/u123456789/_system/config/.env
chmod 644 /home/u123456789/_system/config/domains.json

# 3. Permisos de templates
chmod 755 /home/u123456789/_system/templates
find /home/u123456789/_system/templates -type d -exec chmod 755 {} \;
find /home/u123456789/_system/templates -type f -exec chmod 644 {} \;

# 4. Carpetas dinámicas
chmod 755 /home/u123456789/_system/logs
chmod 755 /home/u123456789/_system/queue
chmod 755 /home/u123456789/domains
chmod 755 /home/u123456789/staging

# 5. Proxy público
chmod 755 /home/u123456789/public_html/generator
chmod 644 /home/u123456789/public_html/generator/deploy.php
```

**Opción B: Desde FileZilla**

1. Clic derecho en carpeta/archivo → Permisos
2. Configurar según números de arriba

---

### **3.2 Proteger carpetas sensibles (5 min)**

**Crear .htaccess en _system/**

```bash
# SSH o usar editor de archivos de cPanel
nano /home/u123456789/_system/.htaccess
```

**Contenido:**
```apache
# Denegar acceso web a _system
Order deny,allow
Deny from all
```

**Guardar:** Ctrl+O, Enter, Ctrl+X

**Verificar:** Intentar acceder a `https://otavafitness.com/_system/`
→ Debería dar error 403 Forbidden ✓

---

### **3.3 Configurar cron jobs (10 min)** ⏰

**Desde cPanel:**
1. Ir a **Cron Jobs**
2. Agregar 3 cron jobs:

#### **Cron 1: Backup diario**
```
Minuto:     0
Hora:       2
Día:        *
Mes:        *
Día semana: *
Comando:    /usr/bin/php /home/u123456789/_system/generator/backup-all.php
```
**Descripción:** Backup de todos los clientes a las 2 AM

---

#### **Cron 2: Health check cada hora**
```
Minuto:     0
Hora:       *
Día:        *
Mes:        *
Día semana: *
Comando:    /usr/bin/php /home/u123456789/_system/generator/health-check.php
```
**Descripción:** Monitoreo de salud cada hora

---

#### **Cron 3: Limpieza de staging diaria**
```
Minuto:     30
Hora:       3
Día:        *
Mes:        *
Día semana: *
Comando:    /usr/bin/php /home/u123456789/_system/generator/cleanup-old.php
```
**Descripción:** Elimina previews >7 días a las 3:30 AM

---

**Guardar los 3 cron jobs**

---

## FASE 4: MAKE.COM SETUP (10 MIN)

### **4.1 Actualizar Scenario (5 min)**

**En Make.com:**
1. Abrir tu scenario actual
2. Buscar módulo HTTP → Make a request
3. Actualizar configuración:

**URL:**
```
https://otavafitness.com/generator/deploy.php
```

**Method:**
```
POST
```

**Headers:**
```
Content-Type: application/json
X-Make-Secret: [TU_MAKE_SECRET_DEL_ENV]
```

**⚠️ IMPORTANTE:**
El valor de `X-Make-Secret` DEBE ser exactamente el mismo que pusiste en `.env`

**Body:**
```json
{
  "domain": "{{DOMINIO}}",
  "business_name": "{{NOMBRE_NEGOCIO}}",
  "template": "landing-pro",
  "logo_url": "{{LOGO_URL}}",
  ...
}
```

---

### **4.2 Test de conexión (5 min)**

**Desde Make.com:**
1. Agregar un módulo de prueba al inicio
2. Datos de ejemplo:
```json
{
  "domain": "test-cliente.com",
  "business_name": "Test Cliente",
  "template": "landing-pro",
  "logo_url": "https://via.placeholder.com/200",
  "hero_title": "Bienvenido a Test",
  "hero_subtitle": "Esto es una prueba",
  "primary_color": "#007bff",
  "phone": "+593987654321",
  "email": "info@test-cliente.com",
  "whatsapp": "+593987654321"
}
```

3. **Run Once**
4. Verificar que no hay errores

**Verificar en servidor:**
```bash
# SSH
ls /home/u123456789/staging/

# Debería aparecer una carpeta con formato:
# test-cliente-com-YYYYMMDD-HHMMSS
```

---

## FASE 5: TESTING (15 MIN)

### **5.1 Verificar instalación (3 min)**

```bash
# SSH
php /home/u123456789/_system/generator/verify-installation.php
```

**Resultado esperado:**
```
[OK] Todas las verificaciones pasaron
Score: 100/100
Sistema listo para producción
```

---

### **5.2 Health check manual (2 min)**

```bash
# SSH
php /home/u123456789/_system/generator/health-check.php
```

**Resultado:**
```
[INFO] No hay dominios creados aún
Sistema operativo OK
```

---

### **5.3 Crear dominio de prueba (5 min)**

```bash
# SSH
php /home/u123456789/_system/generator/create-domain.php test-cliente.com
```

**Resultado esperado:**
```
[OK] Dominio test-cliente.com creado exitosamente
Carpeta: /home/u123456789/domains/test-cliente.com

Próximos pasos:
1. Configurar DNS en cPanel
2. Apuntar dominio a este servidor
3. Copiar contenido desde staging
```

**Verificar:**
```bash
ls /home/u123456789/domains/
# Debería aparecer: test-cliente.com/
```

---

### **5.4 Verificar dominio (2 min)**

```bash
# SSH
php /home/u123456789/_system/generator/verify-domain.php test-cliente.com
```

**Resultado:**
```
[OK] Estructura de dominio correcta
[WARN] DNS no resuelve (normal, dominio de prueba)
Score: 85/100
```

---

### **5.5 Verificar staging (3 min)**

**Abrir en navegador:**
```
https://otavafitness.com/staging/test-cliente-com-YYYYMMDD-HHMMSS/
```

**Debería mostrar:**
- Landing page con datos de prueba
- Header correcto
- Footer correcto
- Estilos aplicados
- Responsive

---

## FASE 6: PRODUCCIÓN (5 MIN)

### **6.1 Primer sitio real (3 min)**

**Desde Make.com:**
1. Crear nuevo scenario o usar el existente
2. Datos reales del primer cliente
3. **Run Once**
4. Verificar preview en staging

---

### **6.2 Mover a producción (2 min)**

```bash
# SSH
# Copiar desde staging a domains
cp -r /home/u123456789/staging/[SLUG-STAGING]/* /home/u123456789/domains/cliente-real.com/public_html/
```

**O usar el script automático:**
```bash
php /home/u123456789/_system/generator/create-domain.php cliente-real.com
```

---

## ✅ CHECKLIST FINAL

### **Preparación Local**
- [ ] TEMP_BACKUP eliminado
- [ ] .env configurado con valores reales
- [ ] MAKE_SECRET generado y guardado
- [ ] .env verificado

### **Migración**
- [ ] FileZilla conectado
- [ ] _system/ subido (8 scripts + templates)
- [ ] deploy.php subido a public_html/generator/
- [ ] Carpetas domains/ y staging/ creadas

### **Configuración Servidor**
- [ ] Permisos configurados (700, 755, 644, 600)
- [ ] .htaccess en _system/ creado
- [ ] 3 cron jobs configurados y activos

### **Make.com**
- [ ] URL actualizada
- [ ] Header X-Make-Secret configurado
- [ ] Test de conexión exitoso

### **Testing**
- [ ] verify-installation.php: 100/100
- [ ] health-check.php: OK
- [ ] Dominio de prueba creado
- [ ] Preview en staging funcional

### **Producción**
- [ ] Primer sitio real generado
- [ ] Cliente satisfecho
- [ ] Monitoreo activo

---

## 📞 SOPORTE POST-MIGRACIÓN

### **Comandos útiles:**

```bash
# Ver logs
tail -f /home/u123456789/_system/logs/*.log

# Ver dominios creados
ls -la /home/u123456789/domains/

# Ver staging
ls -la /home/u123456789/staging/

# Health check rápido
php /home/u123456789/_system/generator/health-check.php

# Verificar dominio
php /home/u123456789/_system/generator/verify-domain.php DOMINIO.COM

# Backup manual
php /home/u123456789/_system/generator/backup-client.php DOMINIO.COM

# Limpiar staging manualmente
php /home/u123456789/_system/generator/cleanup-old.php
```

---

## 🎯 RESUMEN EJECUTIVO

```
╔══════════════════════════════════════════════════╗
║  SISTEMA 100% LISTO                              ║
║                                                  ║
║  Fase actual: Preparación local completa ✅      ║
║  Siguiente:   Configurar .env                    ║
║  Luego:       Subir a Hostinger                  ║
║                                                  ║
║  Tiempo estimado total: 90 minutos              ║
║  Dificultad: Media                               ║
║                                                  ║
║  ¿Listo para empezar? 🚀                         ║
╚══════════════════════════════════════════════════╝
```

---

## 🚀 PRIMER PASO AHORA

```powershell
# 1. Limpiar temporal
Remove-Item -Path "TEMP_BACKUP" -Recurse -Force

# 2. Configurar .env
cd _system\config
copy .env.example .env
notepad .env

# 3. Generar MAKE_SECRET
$bytes = New-Object byte[] 32
[Security.Cryptography.RNGCryptoServiceProvider]::Create().GetBytes($bytes)
[Convert]::ToBase64String($bytes)

# 4. Copiar token al .env y guardar
```

**¡Empecemos!** 🎯
