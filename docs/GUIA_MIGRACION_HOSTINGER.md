# 🚀 GUÍA COMPLETA - MIGRACIÓN A HOSTINGER

**Objetivo:** Dejar tu Hostinger perfectamente configurado con la nueva arquitectura  
**Duración:** 2-3 horas  
**Dificultad:** Media  
**Riesgo:** Bajo (con backups)

---

## 📋 PRE-REQUISITOS

### **Lo que necesitas tener:**
- ✅ Acceso a cPanel de Hostinger
- ✅ Acceso FTP/SFTP (FileZilla configurado)
- ✅ Acceso SSH (opcional pero recomendado)
- ✅ Cuenta Make.com activa
- ✅ Este proyecto en tu PC local

### **Información que necesitas:**
```
Usuario Hostinger: u123456789
IP Hostinger: (ver en cPanel)
FTP Host: ftp.tudominio.com
Dominio principal: otavafitness.com
```

---

## ⏱️ PLAN DE EJECUCIÓN

```
FASE 1: Preparación Local        (30 min)
FASE 2: Backup Hostinger Actual   (15 min)
FASE 3: Subir Nueva Estructura    (45 min)
FASE 4: Configuración Hostinger   (30 min)
FASE 5: Configurar Make.com       (15 min)
FASE 6: Testing                   (30 min)
FASE 7: Activación Final          (15 min)

TOTAL: 3 horas
```

**Mejor momento:** Fin de semana con bajo tráfico

---

## 🔧 FASE 1: PREPARACIÓN LOCAL (30 min)

### **Paso 1.1: Verificar Archivos Locales**

```bash
# En tu PC, ir a la carpeta del proyecto
cd "c:\Users\franc\OneDrive\Escritorio\public_html (3)"

# Verificar estructura
dir /B
```

**Deberías ver:**
```
_system/
generator/
staging/
templates/
.env.example
.gitignore
README.md
```

---

### **Paso 1.2: Crear .env con Valores Reales**

```bash
# Copiar ejemplo
copy .env.example .env

# Editar con Notepad++
notepad++ .env
```

**Configurar valores REALES:**

```bash
# Make.com Integration
MAKE_SECRET=<generar token seguro>

# Admin Notifications
ADMIN_EMAIL=tu@email.com

# URLs
BASE_URL=https://otavafitness.com
STAGING_URL=https://otavafitness.com/staging
```

**Generar MAKE_SECRET seguro:**
- Opción 1: https://randomkeygen.com/ (Fort Knox Passwords)
- Opción 2: En PowerShell: `[Convert]::ToBase64String([System.Security.Cryptography.RandomNumberGenerator]::GetBytes(32))`

---

### **Paso 1.3: Reorganizar Archivos Localmente**

**Crear nueva estructura:**

```
📁 HOSTINGER_UPLOAD/
├── _system/
│   ├── generator/
│   │   ├── create-domain.php
│   │   ├── backup-client.php
│   │   ├── backup-all.php
│   │   ├── health-check.php
│   │   ├── verify-domain.php
│   │   ├── cleanup-old.php
│   │   └── deploy-v4-mejorado.php
│   │
│   ├── templates/
│   │   ├── landing-pro/
│   │   ├── landing-basica/
│   │   └── componentes-globales/
│   │
│   └── config/
│       └── .env
│
├── public_html/
│   └── generator/
│       └── deploy.php  (proxy)
│
└── README_INSTALACION.txt
```

**En PowerShell:**

```powershell
# Crear estructura temporal
New-Item -ItemType Directory -Path "HOSTINGER_UPLOAD\_system\generator" -Force
New-Item -ItemType Directory -Path "HOSTINGER_UPLOAD\_system\templates" -Force
New-Item -ItemType Directory -Path "HOSTINGER_UPLOAD\_system\config" -Force
New-Item -ItemType Directory -Path "HOSTINGER_UPLOAD\public_html\generator" -Force

# Copiar scripts
Copy-Item "_system\generator\*.php" "HOSTINGER_UPLOAD\_system\generator\"

# Copiar templates
Copy-Item "templates\*" "HOSTINGER_UPLOAD\_system\templates\" -Recurse

# Copiar .env (CON VALORES REALES)
Copy-Item ".env" "HOSTINGER_UPLOAD\_system\config\"
```

---

### **Paso 1.4: Crear Proxy deploy.php**

```powershell
# Crear archivo
New-Item -Path "HOSTINGER_UPLOAD\public_html\generator\deploy.php" -ItemType File
```

**Contenido de `deploy.php`:**

```php
<?php
/**
 * PROXY SEGURO PARA MAKE.COM
 * Redirige requests validados a sistema protegido
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

// 1. Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method Not Allowed']));
}

// 2. Cargar configuración
$envFile = dirname(dirname(__DIR__)) . '/_system/config/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// 3. Validar token secreto
$secret = $_SERVER['HTTP_X_MAKE_SECRET'] ?? '';
$expectedSecret = getenv('MAKE_SECRET');

if (!$expectedSecret) {
    error_log('⚠️ MAKE_SECRET no configurado');
    http_response_code(500);
    die(json_encode(['error' => 'Server misconfigured']));
}

if (!hash_equals($expectedSecret, $secret)) {
    error_log('⚠️ Token inválido desde: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    http_response_code(403);
    die(json_encode(['error' => 'Forbidden']));
}

// 4. Rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateFile = sys_get_temp_dir() . '/rate-' . md5($ip) . '.txt';

if (file_exists($rateFile)) {
    $requests = file($rateFile, FILE_IGNORE_NEW_LINES);
    $now = time();
    
    $requests = array_filter($requests, function($ts) use ($now) {
        return ($now - (int)$ts) < 60;
    });
    
    if (count($requests) >= 10) {
        http_response_code(429);
        die(json_encode(['error' => 'Rate limit exceeded']));
    }
    
    $requests[] = $now;
} else {
    $requests = [time()];
}

file_put_contents($rateFile, implode("\n", $requests));

// 5. Log de acceso
$logDir = dirname(dirname(__DIR__)) . '/_system/logs';
if (!file_exists($logDir)) {
    @mkdir($logDir, 0755, true);
}

$logEntry = [
    'timestamp' => date('c'),
    'ip' => $ip,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
];

file_put_contents(
    $logDir . '/make-access.log',
    json_encode($logEntry) . "\n",
    FILE_APPEND
);

// 6. Incluir script real
chdir(dirname(dirname(__DIR__)) . '/_system/generator');
require_once dirname(dirname(__DIR__)) . '/_system/generator/deploy-v4-mejorado.php';
?>
```

---

### **Paso 1.5: Crear Instrucciones de Instalación**

Crear `HOSTINGER_UPLOAD/README_INSTALACION.txt`:

```
═══════════════════════════════════════════════════════════════
  INSTRUCCIONES DE INSTALACIÓN - HOSTINGER
═══════════════════════════════════════════════════════════════

1. ESTRUCTURA DE CARPETAS:

   En Hostinger, crear:
   - /home/u123456789/_system/
   - /home/u123456789/domains/
   - /home/u123456789/staging/

2. SUBIR ARCHIVOS:

   _system/          → /home/u123456789/_system/
   public_html/      → /home/u123456789/public_html/

3. PERMISOS:

   chmod 700 /home/u123456789/_system
   chmod 755 /home/u123456789/domains
   chmod 755 /home/u123456789/staging

4. CREAR CARPETAS DE LOGS:

   mkdir -p /home/u123456789/_system/logs/errors
   mkdir -p /home/u123456789/_system/logs/health
   mkdir -p /home/u123456789/_system/queue
   mkdir -p /home/u123456789/_system/config

5. MOVER .env:

   mv _system/config/.env /home/u123456789/_system/config/.env
   chmod 600 /home/u123456789/_system/config/.env

6. CONFIGURAR CRONS (ver FASE 4)

7. PROBAR: php _system/generator/health-check.php
```

---

## 💾 FASE 2: BACKUP HOSTINGER ACTUAL (15 min)

### **Paso 2.1: Conectar vía SSH (recomendado)**

```bash
# En PowerShell o CMD
ssh u123456789@tudominio.com
```

O usar Terminal de cPanel.

---

### **Paso 2.2: Crear Backup Completo**

```bash
# Conectado en Hostinger vía SSH

# Ir a home
cd /home/u123456789

# Crear backup de TODO
tar -czf backup-pre-migration-$(date +%Y%m%d-%H%M).tar.gz public_html/

# Verificar tamaño
ls -lh backup-pre-migration-*.tar.gz

# Opcional: Descargar backup a tu PC con FileZilla
```

**Resultado:** `backup-pre-migration-20251125-0900.tar.gz`

---

### **Paso 2.3: Documentar Estado Actual**

```bash
# Listar crons actuales
crontab -l > crons-backup.txt

# Listar estructura actual
find public_html -type d > estructura-actual.txt

# Listar dominios (si tienes)
ls -la public_html/staging/ > staging-actual.txt
```

---

## ⬆️ FASE 3: SUBIR NUEVA ESTRUCTURA (45 min)

### **Paso 3.1: Conectar con FileZilla**

**Configuración:**
```
Host: ftp.otavafitness.com
Username: u123456789
Password: [tu password]
Port: 21
```

**Modo:** Usar SFTP (puerto 22) si está disponible, más seguro.

---

### **Paso 3.2: Crear Carpetas Base**

**En Hostinger (via SSH o File Manager de cPanel):**

```bash
# Crear estructura base
mkdir -p /home/u123456789/_system/{generator,templates,logs,config,queue}
mkdir -p /home/u123456789/_system/logs/{errors,health}
mkdir -p /home/u123456789/domains
mkdir -p /home/u123456789/staging

# Verificar
ls -la /home/u123456789/
```

---

### **Paso 3.3: Subir Archivos con FileZilla**

**Orden de subida:**

1. **Templates primero** (tarda más):
   ```
   Local: HOSTINGER_UPLOAD\_system\templates\
   Remote: /home/u123456789/_system/templates/
   ```

2. **Scripts después**:
   ```
   Local: HOSTINGER_UPLOAD\_system\generator\
   Remote: /home/u123456789/_system/generator/
   ```

3. **.env (IMPORTANTE)**:
   ```
   Local: HOSTINGER_UPLOAD\_system\config\.env
   Remote: /home/u123456789/_system/config/.env
   ```

4. **Proxy deploy.php**:
   ```
   Local: HOSTINGER_UPLOAD\public_html\generator\deploy.php
   Remote: /home/u123456789/public_html/generator/deploy.php
   ```

**Progreso esperado:**
- Templates: ~10 min (varios archivos)
- Scripts: ~2 min
- Config: ~1 min

---

### **Paso 3.4: Configurar Permisos**

**Via SSH:**

```bash
# Permisos del sistema (restringido)
chmod 700 /home/u123456789/_system
chmod 755 /home/u123456789/_system/generator
chmod 755 /home/u123456789/_system/logs
chmod 600 /home/u123456789/_system/config/.env

# Permisos de dominios (accesible)
chmod 755 /home/u123456789/domains
chmod 755 /home/u123456789/staging

# Scripts ejecutables
chmod 755 /home/u123456789/_system/generator/*.php
chmod 755 /home/u123456789/public_html/generator/deploy.php

# Verificar
ls -la /home/u123456789/_system/
```

**Via cPanel File Manager:**
- Click derecho en carpeta → Change Permissions
- `_system/`: 700
- `_system/config/.env`: 600
- Scripts `*.php`: 755

---

## ⚙️ FASE 4: CONFIGURACIÓN HOSTINGER (30 min)

### **Paso 4.1: Verificar PHP**

```bash
# Via SSH
php -v

# Debería mostrar: PHP 7.4 o superior
```

**Si es versión vieja:**
- cPanel → Select PHP Version
- Elegir 7.4 o 8.0

---

### **Paso 4.2: Verificar Extensiones PHP**

```bash
php -m | grep -E "(curl|json|mbstring|openssl)"
```

**Deberías ver:**
```
curl
json
mbstring
openssl
```

**Si falta alguna:**
- cPanel → Select PHP Version → Extensions
- Activar las necesarias

---

### **Paso 4.3: Configurar Cron Jobs**

**En cPanel → Cron Jobs:**

#### **Cron 1: Backups Diarios**
```
Minuto: 0
Hora: 3
Día: *
Mes: *
Día semana: *

Comando:
/usr/bin/php /home/u123456789/_system/generator/backup-all.php >> /home/u123456789/_system/logs/backups.log 2>&1
```

#### **Cron 2: Health Checks**
```
Minuto: 0
Hora: *
Día: *
Mes: *
Día semana: *

Comando:
/usr/bin/php /home/u123456789/_system/generator/health-check.php >> /home/u123456789/_system/logs/health.log 2>&1
```

#### **Cron 3: Cleanup Staging**
```
Minuto: 0
Hora: 4
Día: *
Mes: *
Día semana: *

Comando:
/usr/bin/php /home/u123456789/_system/generator/cleanup-old.php >> /home/u123456789/_system/logs/cleanup.log 2>&1
```

**Nota:** Cambiar `u123456789` por tu usuario real de Hostinger.

---

### **Paso 4.4: Crear domains.json Inicial**

```bash
# Via SSH
cd /home/u123456789/_system/config

# Crear archivo vacío
echo "[]" > domains.json

# Permisos
chmod 644 domains.json
```

---

### **Paso 4.5: Configurar .htaccess Principal**

**Editar `/home/u123456789/public_html/.htaccess`:**

```apache
# Bloquear acceso a _system
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^_system/ - [F,L]
</IfModule>

# Bloquear archivos sensibles
<FilesMatch "\.(env|json|log|bak|sql|sh|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Permitir deploy.php
<Files "deploy.php">
    Order allow,deny
    Allow from all
</Files>

# Security headers
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

---

## 🔗 FASE 5: CONFIGURAR MAKE.COM (15 min)

### **Paso 5.1: Obtener tu MAKE_SECRET**

```bash
# Via SSH en Hostinger
cat /home/u123456789/_system/config/.env | grep MAKE_SECRET
```

**Copiar el valor** (ej: `abc123def456...`)

---

### **Paso 5.2: Actualizar Scenario Make.com**

1. Ir a Make.com → Tu Scenario
2. Click en módulo **HTTP** (el que llama a deploy)
3. **Actualizar URL:**
   ```
   https://otavafitness.com/generator/deploy.php
   ```

4. **Agregar Header:**
   - Click en "Show advanced settings"
   - Headers → Add item
   ```
   Name: X-Make-Secret
   Value: [pegar tu MAKE_SECRET]
   ```

5. **Guardar** scenario

---

### **Paso 5.3: Test de Conexión**

**En Make.com:**
1. Click derecho en módulo HTTP
2. "Run this module only"
3. Usar datos de prueba

**Verificar:**
- Status 200 OK
- No error de "Forbidden"

**Si da error 403:**
- Verificar que MAKE_SECRET sea correcto
- Verificar header `X-Make-Secret` esté presente

---

## 🧪 FASE 6: TESTING (30 min)

### **Paso 6.1: Test de Scripts Individuales**

```bash
# Via SSH en Hostinger
cd /home/u123456789/_system/generator

# Test 1: Health check
php health-check.php

# Debe mostrar:
# ✅ DNS... OK
# ✅ HTTP... OK
# etc.

# Test 2: Crear dominio test
php create-domain.php test-migracion.com

# Debe crear carpetas en /domains/test-migracion.com/

# Test 3: Verificar
ls -la /home/u123456789/domains/

# Deberías ver: test-migracion.com/
```

---

### **Paso 6.2: Test de Proxy deploy.php**

```bash
# Desde tu PC local, test con curl

# Test 1: Sin token (debe fallar)
curl -X POST https://otavafitness.com/generator/deploy.php

# Resultado esperado: {"error":"Forbidden"}

# Test 2: Con token correcto
curl -X POST https://otavafitness.com/generator/deploy.php \
  -H "X-Make-Secret: TU_MAKE_SECRET_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"nombre_negocio":"Test"}'

# Resultado esperado: {"queue_id":"...","status":"queued"}
```

---

### **Paso 6.3: Test End-to-End con Make.com**

1. **Enviar formulario Google** (o trigger Make.com manualmente)

2. **Verificar en Hostinger:**
   ```bash
   # Ver logs
   tail -f /home/u123456789/_system/logs/generator.log
   
   # Ver si se creó sitio
   ls -la /home/u123456789/staging/
   ```

3. **Verificar preview URL:**
   ```
   https://otavafitness.com/staging/[slug-generado]/
   ```

---

### **Paso 6.4: Test de Cron Jobs**

```bash
# Ejecutar manualmente para probar

# Test backup
php /home/u123456789/_system/generator/backup-all.php

# Ver resultado
ls -la /home/u123456789/domains/*/backups/

# Test health check
php /home/u123456789/_system/generator/health-check.php

# Ver log
cat /home/u123456789/_system/logs/health.log
```

---

## ✅ FASE 7: ACTIVACIÓN FINAL (15 min)

### **Paso 7.1: Verificación Checklist**

```bash
# En Hostinger, ejecutar:
cd /home/u123456789

# Verificar estructura
[ -d "_system/generator" ] && echo "✅ Generator OK" || echo "❌ Generator FAIL"
[ -d "_system/templates" ] && echo "✅ Templates OK" || echo "❌ Templates FAIL"
[ -d "domains" ] && echo "✅ Domains OK" || echo "❌ Domains FAIL"
[ -d "staging" ] && echo "✅ Staging OK" || echo "❌ Staging FAIL"

# Verificar .env
[ -f "_system/config/.env" ] && echo "✅ .env OK" || echo "❌ .env FAIL"

# Verificar permisos
ls -ld _system | grep "drwx------" && echo "✅ Permisos OK" || echo "⚠️ Verificar permisos"

# Verificar crons
crontab -l | grep -c "backup-all" && echo "✅ Crons OK" || echo "❌ Crons FAIL"
```

**Todos deben ser ✅**

---

### **Paso 7.2: Crear Sitio de Prueba Real**

```bash
# Crear dominio de prueba
php _system/generator/create-domain.php prueba-final.com

# Configurar en cPanel:
# Domains → Addon Domains
# Domain: prueba-final.com
# Document Root: /home/u123456789/domains/prueba-final.com/public_html

# Verificar
php _system/generator/verify-domain.php prueba-final.com
```

---

### **Paso 7.3: Monitoreo Post-Migración**

**Primeras 24 horas, revisar:**

```bash
# Ver logs en tiempo real
tail -f _system/logs/generator.log
tail -f _system/logs/make-access.log
tail -f _system/logs/errors/*.log

# Verificar health
php _system/generator/health-check.php

# Verificar backups
ls -la domains/*/backups/
```

---

### **Paso 7.4: Limpiar Archivos Viejos (Opcional)**

**SOLO si todo funciona perfecto:**

```bash
# Mover estructura vieja
mkdir /home/u123456789/OLD_STRUCTURE
mv /home/u123456789/public_html/generator/deploy-v2.php /home/u123456789/OLD_STRUCTURE/
mv /home/u123456789/public_html/generator/deploy-v3.php /home/u123456789/OLD_STRUCTURE/
mv /home/u123456789/public_html/templates /home/u123456789/OLD_STRUCTURE/

# Mantener OLD_STRUCTURE por 1 semana, luego eliminar
```

---

## 📊 VERIFICACIÓN FINAL

### **Checklist Completo:**

```
ESTRUCTURA:
✅ /_system/generator/ existe
✅ /_system/templates/ existe
✅ /_system/config/.env existe
✅ /domains/ existe
✅ /staging/ existe
✅ /public_html/generator/deploy.php existe

PERMISOS:
✅ _system/ es 700
✅ .env es 600
✅ Scripts son 755

CONFIGURACIÓN:
✅ .env tiene MAKE_SECRET
✅ domains.json existe
✅ .htaccess bloquea _system

CRON JOBS:
✅ backup-all.php configurado
✅ health-check.php configurado
✅ cleanup-old.php configurado

MAKE.COM:
✅ URL actualizada a /generator/deploy.php
✅ Header X-Make-Secret configurado
✅ Test exitoso

TESTING:
✅ create-domain.php funciona
✅ health-check.php funciona
✅ Proxy deploy.php funciona
✅ Make.com genera sitios correctamente

MONITOREO:
✅ Logs se están generando
✅ Backups funcionan
✅ Health checks se ejecutan
```

---

## 🚨 TROUBLESHOOTING

### **Problema: deploy.php da 403**
```bash
# Verificar .env
cat _system/config/.env | grep MAKE_SECRET

# Verificar que Make.com envíe el header correcto
tail -f _system/logs/make-access.log
```

### **Problema: Crons no se ejecutan**
```bash
# Verificar ruta de PHP
which php

# Actualizar cron con ruta correcta
crontab -e
```

### **Problema: Permisos denegados**
```bash
# Reconf
