# 🗑️ AUDITORÍA #12 - LIMPIEZA Y MIGRACIÓN HOSTINGER

**Fecha:** 25 Nov 2025, 00:17 AM  
**Tipo:** Análisis de Migración + Limpieza + Situaciones Críticas  
**Estado:** 🔴 **ALTO RIESGO SI NO SE PLANEA**

---

## 🎯 OBJETIVO

Identificar:
1. Archivos/carpetas a ELIMINAR
2. Archivos a MOVER
3. Archivos a MANTENER
4. Situaciones de riesgo
5. Plan de migración seguro

---

## 📂 ESTRUCTURA ACTUAL vs NUEVA

### **ACTUAL (Hostinger HOY):**
```
/home/u123456789/
└── public_html/
    ├── index.html                    # Tu sitio
    ├── generator/                    # ⚠️ Scripts PHP
    │   ├── deploy-v2.php
    │   ├── deploy-v3.php
    │   └── deploy-v4-mejorado.php   # Nuevo (no subido aún)
    │
    ├── staging/                      # ⚠️ Sitios de clientes
    │   ├── cliente-1/
    │   ├── cliente-2/
    │   ├── cliente-3/
    │   └── ... (varios más)
    │
    ├── templates/                    # ⚠️ Templates
    │   ├── landing-pro/
    │   ├── landing-basica/
    │   └── componentes-globales/
    │
    └── ... (otros archivos)
```

### **NUEVA (Target):**
```
/home/u123456789/
├── public_html/                      # Tu sitio LIMPIO
│   ├── index.html
│   ├── download.php                 # Nuevo
│   ├── .htaccess                    # Actualizado
│   └── admin/                       # Nuevo (futuro)
│
├── domains/                          # ⭐ NUEVO
│   ├── cliente1.com/
│   └── cliente2.com/
│
├── _system/                          # ⭐ NUEVO
│   ├── generator/
│   ├── templates/
│   ├── queue/
│   ├── logs/
│   └── config/
│
└── staging/                          # Temporal (7 días)
    └── preview-token/
```

---

## 🗑️ ARCHIVOS A ELIMINAR

### **🔴 CRÍTICO - ELIMINAR INMEDIATAMENTE:**

#### **1. Scripts PHP en /public_html/generator/**
```
❌ /public_html/generator/deploy-v2.php
❌ /public_html/generator/deploy-v3.php

RAZÓN: Accesibles públicamente en:
https://otavafitness.com/generator/deploy-v2.php

RIESGO: 🔴 CRÍTICO
- Cualquiera puede ejecutarlos
- Expone código fuente
- Vulnerabilidad de seguridad
- Puede generar sitios sin autorización

ACCIÓN: Mover a /_system/generator/
```

#### **2. Archivos de Testing/Debug:**
```
❌ /public_html/test.php
❌ /public_html/phpinfo.php
❌ /public_html/debug.log
❌ /public_html/*.bak
❌ /public_html/*.tmp

RAZÓN: Exponen información sensible

RIESGO: 🔴 CRÍTICO
- phpinfo.php revela configuración del servidor
- *.bak pueden contener credenciales
- debug.log puede tener datos personales

ACCIÓN: Eliminar TODOS
```

#### **3. Templates en /public_html/templates/**
```
⚠️ /public_html/templates/

RAZÓN: No deberían estar en carpeta pública

RIESGO: 🟡 MEDIO
- Código fuente accesible
- Alguien puede copiar tus templates
- No es crítico pero no profesional

ACCIÓN: Mover a /_system/templates/
```

---

### **🟡 IMPORTANTE - LIMPIAR DESPUÉS DE MIGRAR:**

#### **4. Staging Sites en /public_html/staging/**
```
⚠️ /public_html/staging/cliente-1/
⚠️ /public_html/staging/cliente-2/
⚠️ /public_html/staging/cliente-3/
... (todos)

RAZÓN: Usar SOLO para previews temporales

ACCIÓN:
1. Migrar sitios activos a /domains/
2. Eliminar carpetas staging viejas
3. Mantener solo previews recientes (< 7 días)
```

#### **5. Archivos de Documentación en /public_html/**
```
⚠️ /public_html/README.md
⚠️ /public_html/ESTRUCTURA_HOSTINGER.md
⚠️ /public_html/AUDITORIA_*.md

RAZÓN: No deben estar accesibles públicamente

ACCIÓN: Mover a /_system/docs/ o eliminar
```

---

### **🟢 OPCIONAL - Revisar y Decidir:**

#### **6. Archivos Antiguos/No Usados:**
```
? /public_html/old/
? /public_html/backup/
? /public_html/temp/
? /public_html/cache/

ACCIÓN: Revisar contenido y eliminar si no se usa
```

#### **7. Imágenes/Assets No Usados:**
```
? /public_html/images/old-logo.png
? /public_html/css/old-styles.css
? /public_html/js/jquery-1.8.3.min.js (obsoleto)

ACCIÓN: Limpiar assets antiguos
```

---

## 📦 ARCHIVOS A MOVER (NO ELIMINAR)

### **1. Generator Scripts:**
```
ORIGEN: /public_html/generator/
DESTINO: /_system/generator/

MOVER:
✓ deploy-v2.php → /_system/generator/deploy-v2-legacy.php (backup)
✓ deploy-v3.php → /_system/generator/deploy-v3-legacy.php (backup)
✓ deploy-v4-mejorado.php → /_system/generator/deploy-v4-mejorado.php (activo)

ACCIÓN:
mv /public_html/generator/* /_system/generator/
```

### **2. Templates:**
```
ORIGEN: /public_html/templates/
DESTINO: /_system/templates/

MOVER:
✓ landing-pro/
✓ landing-basica/
✓ componentes-globales/

ACCIÓN:
mv /public_html/templates/* /_system/templates/
```

### **3. Staging Sites ACTIVOS:**
```
ORIGEN: /public_html/staging/cliente-x/
DESTINO: /domains/cliente.com/public_html/

PROCESO:
1. Identificar qué clientes YA tienen dominio
2. Ejecutar create-domain.php para cada uno
3. Verificar que el sitio funcione
4. Eliminar carpeta staging original
```

---

## ⚠️ SITUACIONES CRÍTICAS A ANALIZAR

### **SITUACIÓN #1: Make.com Apuntando a URL Vieja** 🔴

**Problema:**
```
Make.com actualmente apunta a:
https://otavafitness.com/generator/deploy-v2.php

Después de mover:
/_system/generator/ NO es accesible vía web
→ Make.com empezará a fallar
→ Todos los sitios nuevos fallarán
```

**Solución:**

#### **Opción A: Proxy en public_html (RECOMENDADO)**
```php
// /public_html/generator/deploy.php (nuevo proxy)
<?php
// Proxy seguro para Make.com
// Valida origen y redirige a _system

// 1. Validar que viene de Make.com
$validOrigins = ['hook.make.com', 'hook.integromat.com'];
$origin = $_SERVER['HTTP_REFERER'] ?? '';
$isValidOrigin = false;

foreach ($validOrigins as $valid) {
    if (strpos($origin, $valid) !== false) {
        $isValidOrigin = true;
        break;
    }
}

// 2. Validar IP (opcional, más seguro)
$allowedIPs = [
    '54.243.200.113',  // Make.com IPs (verificar actuales)
    '3.225.112.0/20'
];

// 3. Validar token secreto
$secret = $_SERVER['HTTP_X_MAKE_SECRET'] ?? '';
if ($secret !== 'tu-token-super-secreto-aqui') {
    http_response_code(403);
    die('Forbidden');
}

// 4. Incluir script real
chdir(dirname(__DIR__) . '/_system/generator');
require_once dirname(__DIR__) . '/_system/generator/deploy-v4-mejorado.php';
?>
```

**En Make.com:**
```
HTTP Module → Headers:
X-Make-Secret: tu-token-super-secreto-aqui
```

#### **Opción B: Subdomain API**
```
api.otavafitness.com → apunta a /api/
/api/deploy.php → proxy a /_system/generator/

Ventaja: Más profesional
Desventaja: Requiere configurar subdomain
```

---

### **SITUACIÓN #2: Sitios en Staging con URLs Hardcodeadas** 🔴

**Problema:**
```html
<!-- En /staging/cliente-1/index.html -->
<img src="/staging/cliente-1/images/logo.png">
<link href="/staging/cliente-1/css/styles.css">

Después de mover a /domains/cliente.com/:
→ Imágenes y CSS rotos
→ Sitio aparece sin estilos
```

**Solución:**
```bash
# Script para reemplazar URLs antes de migrar
find /staging/cliente-1/ -type f -name "*.html" -exec sed -i 's|/staging/cliente-1/||g' {} \;
```

---

### **SITUACIÓN #3: Dominios Aún No Configurados** 🟡

**Problema:**
```
Tienes 20 sitios en /staging/
Solo 5 clientes tienen dominio propio
→ ¿Qué hacer con los otros 15?
```

**Opciones:**

#### **A. Mantener en Staging Temporal**
```
/staging/
├── preview-abc123/  (cliente sin dominio, keep 30 días)
├── preview-def456/
└── ...

Pro: Simple
Con: Cliente ve URL fea
```

#### **B. Subdominios Temporales**
```
cliente1.otavafitness.com
cliente2.otavafitness.com

Pro: URL más profesional
Con: Requiere config DNS
```

#### **C. Path-based Temporal**
```
otavafitness.com/sites/cliente-1/

Pro: Sin config DNS
Con: No tan profesional
```

**RECOMENDACIÓN:** Opción A (staging temporal) hasta que cliente compre dominio

---

### **SITUACIÓN #4: Backups Existentes Mezclados** 🟡

**Problema:**
```
/public_html/backups/
├── cliente-1-backup.tar.gz
├── cliente-2-backup.tar.gz
├── full-backup-2024-11-01.tar.gz
└── ... (mezclados)

Nueva estructura:
/domains/cliente1.com/backups/
→ ¿Cómo migrar backups antiguos?
```

**Solución:**
```bash
# Script para organizar backups
for backup in /public_html/backups/*; do
    cliente=$(echo $backup | grep -oP 'cliente-\K[0-9]+')
    if [ ! -z "$cliente" ]; then
        # Identificar dominio del cliente
        domain=$(php -r "/* query database */")
        mkdir -p /domains/$domain/backups/
        mv $backup /domains/$domain/backups/legacy-$(basename $backup)
    fi
done
```

---

### **SITUACIÓN #5: Logs Dispersos** 🟢

**Problema:**
```
/public_html/error.log
/public_html/generator/deploy.log
/public_html/staging/*/logs/
→ Logs mezclados, difícil de analizar
```

**Solución:**
```bash
# Centralizar logs históricos
mkdir -p /_system/logs/legacy/
mv /public_html/*.log /_system/logs/legacy/
mv /public_html/generator/*.log /_system/logs/legacy/

# Logs nuevos:
/_system/logs/
├── generator.log
├── backups.log
├── health.log
└── legacy/
    └── ... (archivos viejos)
```

---

### **SITUACIÓN #6: Database Credentials Hardcoded** 🔴

**Problema:**
```php
// En algún script viejo
$db_host = 'localhost';
$db_user = 'u123456_admin';
$db_pass = 'password123';  // ← 🔴 CRÍTICO

Si este archivo está en /public_html/:
→ Cualquiera puede descargar el .php
→ Expone credenciales
```

**Solución:**
```bash
# 1. Buscar archivos con credenciales
grep -r "db_pass\|password\|mysql" /public_html/ --include="*.php"

# 2. Mover a /_system/ o usar .env
# 3. Cambiar passwords si fueron expuestos
```

---

### **SITUACIÓN #7: Cron Jobs con Rutas Viejas** 🟡

**Problema:**
```cron
# Cron actual
0 3 * * * php /home/u123/public_html/generator/backup.php

Después de mover:
→ Archivo no existe
→ Backup falla silenciosamente
→ No te das cuenta hasta que pierdes datos
```

**Solución:**
```bash
# ANTES de migrar, listar crons
crontab -l > crons-backup.txt

# DESPUÉS de migrar, actualizar
crontab -e

# Cambiar:
0 3 * * * php /_system/generator/backup-all.php
```

---

### **SITUACIÓN #8: .htaccess Conflictivos** 🟡

**Problema:**
```apache
# /public_html/.htaccess actual
RewriteRule ^staging/(.*)$ /staging/$1 [L]

# Si mueves /staging/ fuera de public_html:
→ Regla sigue activa
→ 404 errors
```

**Solución:**
```apache
# Revisar y limpiar /public_html/.htaccess
# Eliminar reglas obsoletas
# Mantener solo reglas para tu sitio principal
```

---

### **SITUACIÓN #9: Symlinks Rotos** 🟢

**Problema:**
```bash
# Si usabas symlinks
/public_html/assets -> /public_html/templates/assets

Después de mover templates:
→ Symlink roto
→ Assets no cargan
```

**Solución:**
```bash
# Encontrar symlinks
find /public_html/ -type l

# Verificar si están rotos
find /public_html/ -xtype l

# Actualizar o eliminar
```

---

### **SITUACIÓN #10: Procesos PHP en Ejecución** 🔴

**Problema:**
```
deploy-v2.php está procesando un sitio
→ Tú eliminas /public_html/generator/
→ Proceso muere a mitad de ejecución
→ Sitio queda corrupto
```

**Solución:**
```bash
# ANTES de migrar, verificar procesos
ps aux | grep "deploy"

# Si hay procesos activos:
# 1. Esperar a que terminen
# 2. O detener gracefully
# 3. Nunca hacer kill -9
```

---

## 📋 PLAN DE MIGRACIÓN SEGURO

### **FASE 1: PREPARACIÓN** (1 hora)

```bash
# 1. Backup COMPLETO
cd /home/u123456
tar -czf backup-pre-migration-$(date +%Y%m%d).tar.gz public_html/

# 2. Listar crons
crontab -l > crons-backup.txt

# 3. Listar procesos
ps aux > processes-backup.txt

# 4. Documentar dominios activos
ls -la public_html/staging/ > staging-inventory.txt

# 5. Verificar espacio
df -h > disk-space-before.txt
```

### **FASE 2: CREAR NUEVA ESTRUCTURA** (30 min)

```bash
# 1. Crear carpetas fuera de public_html
mkdir -p /home/u123456/_system/{generator,templates,queue,logs,config}
mkdir -p /home/u123456/domains
mkdir -p /home/u123456/staging

# 2. Configurar permisos
chmod 700 /home/u123456/_system
chmod 755 /home/u123456/domains
chmod 755 /home/u123456/staging
```

### **FASE 3: MOVER ARCHIVOS** (1 hora)

```bash
# 1. Mover generator (PRIMERO hacer backup)
cp -r public_html/generator _system/generator-backup
mv public_html/generator/* _system/generator/

# 2. Mover templates
mv public_html/templates/* _system/templates/

# 3. NO ELIMINAR aún, solo mover
```

### **FASE 4: CREAR PROXY PARA MAKE.COM** (15 min)

```bash
# 1. Crear carpeta generator vacía en public_html
mkdir public_html/generator

# 2. Crear proxy deploy.php (ver código arriba)
nano public_html/generator/deploy.php

# 3. Configurar token secreto
```

### **FASE 5: ACTUALIZAR MAKE.COM** (15 min)

```
1. Ir a Make.com → Scenario
2. Módulo HTTP:
   - URL: https://otavafitness.com/generator/deploy.php
   - Headers: X-Make-Secret: tu-token
3. Test connection
4. Verificar que funcione
```

### **FASE 6: MIGRAR 1 SITIO DE PRUEBA** (30 min)

```bash
# 1. Elegir un sitio no crítico
php _system/generator/create-domain.php test-cliente.com cliente-test

# 2. Configurar dominio en cPanel
# (seguir instrucciones generadas)

# 3. Verificar que funcione
curl -I https://test-cliente.com

# 4. Si OK, continuar. Si FAIL, investigar.
```

### **FASE 7: MIGRAR RESTO DE SITIOS** (2-4 horas)

```bash
# Script para migrar múltiples sitios
for sitio in /public_html/staging/*; do
    slug=$(basename $sitio)
    echo "Migrando: $slug"
    
    # Preguntar por dominio
    read -p "Dominio para $slug (o SKIP): " domain
    
    if [ "$domain" != "SKIP" ]; then
        php _system/generator/create-domain.php $domain $slug
        echo "Configurar en cPanel y presionar ENTER"
        read
    fi
done
```

### **FASE 8: ACTUALIZAR CRONS** (15 min)

```bash
crontab -e

# Actualizar rutas:
0 3 * * * /usr/bin/php /home/u123/_system/generator/backup-all.php
0 * * * * /usr/bin/php /home/u123/_system/generator/health-check.php
```

### **FASE 9: LIMPIAR ARCHIVOS VIEJOS** (30 min)

```bash
# SOLO después de verificar que todo funciona

# 1. Eliminar generator viejo (ya está en _system/)
rm -rf public_html/generator-backup

# 2. Eliminar templates viejos
rm -rf public_html/templates

# 3. Limpiar staging (mantener solo < 7 días)
find public_html/staging/ -mtime +7 -type d -exec rm -rf {} \;

# 4. Eliminar logs viejos
rm -f public_html/*.log

# 5. Eliminar archivos de testing
rm -f public_html/test*.php
rm -f public_html/phpinfo.php
```

### **FASE 10: VERIFICACIÓN FINAL** (30 min)

```bash
# 1. Health check de todos los dominios
php _system/generator/health-check.php

# 2. Verificar Make.com
# Enviar formulario de prueba

# 3. Verificar backups
php _system/generator/backup-all.php

# 4. Verificar espacio
df -h

# 5. Revisar logs
tail -f _system/logs/*.log
```

---

## ⏱️ TIEMPO ESTIMADO

```
FASE 1: Preparación        1h
FASE 2: Nueva estructura   0.5h
FASE 3: Mover archivos     1h
FASE 4: Proxy Make.com     0.25h
FASE 5: Actualizar Make    0.25h
FASE 6: Sitio prueba       0.5h
FASE 7: Migrar resto       3h (depende cantidad)
FASE 8: Crons              0.25h
FASE 9: Limpieza           0.5h
FASE 10: Verificación      0.5h

TOTAL: 7-8 horas
```

**RECOMENDACIÓN:** Hacerlo un fin de semana con tráfico bajo

---

## 🚨 CHECKLIST PRE-MIGRACIÓN

- [ ] Backup completo hecho
- [ ] Crons documentados
- [ ] Procesos activos verificados
- [ ] Make.com en modo pausa (opcional)
- [ ] Cliente notificado de mantenimiento
- [ ] Plan de rollback listo
- [ ] Espacio en disco suficiente (>20GB libre)
- [ ] Acceso SSH activo
- [ ] Acceso cPanel activo
- [ ] Teléfono de soporte Hostinger a mano

---

## 🔙 PLAN DE ROLLBACK

Si algo sale mal:

```bash
# 1. Restaurar backup
cd /home/u123456
tar -xzf backup-pre-migration-YYYYMMDD.tar.gz

# 2. Restaurar crons
crontab crons-backup.txt

# 3. Reiniciar Apache (si es necesario)
# Contactar soporte Hostinger

# 4. Notificar a clientes
```

---

## 📊 RESUMEN

**Archivos a ELIMINAR:**
- 🔴 Scripts PHP en /public_html/generator/
- 🔴 test.php, phpinfo.php, debug.log
- 🔴 *.bak, *.tmp
- 🟡 README.md, AUDITORIA_*.md en public_html
- 🟡 Staging sites viejos (> 7 días)
- 🟢 Assets no usados

**Archivos a MOVER:**
- ✅ generator/ → _system/generator/
- ✅ templates/ → _system/templates/
- ✅ staging/cliente-x/ → domains/cliente.com/

**Situaciones Críticas:**
- 🔴 Make.com apuntando a URL vieja
- 🔴 Credenciales hardcoded
- 🔴 Procesos en ejecución
- 🟡 URLs hardcodeadas en HTML
- 🟡 Crons con rutas viejas
- 🟡 .htaccess obsoleto

---

**Estado:** ⏳ REQUIERE EJECUCIÓN  
**Riesgo:** 🔴 ALTO si no se planea  
**Tiempo:** 7-8 horas  
**Ventana:** Fin de semana con bajo tráfico
