# ✅ CHECKLIST EJECUTABLE - MIGRACIÓN HOSTINGER

**Imprime esto y marca cada paso cuando lo completes**

---

## 📋 PREPARACIÓN LOCAL

- [ ] Proyecto descargado en PC
- [ ] `.env.example` copiado a `.env`
- [ ] MAKE_SECRET generado y guardado en `.env`
- [ ] ADMIN_EMAIL configurado en `.env`
- [ ] Carpeta `HOSTINGER_UPLOAD` creada
- [ ] Scripts copiados a `HOSTINGER_UPLOAD/_system/generator/`
- [ ] Templates copiados a `HOSTINGER_UPLOAD/_system/templates/`
- [ ] `.env` copiado a `HOSTINGER_UPLOAD/_system/config/`
- [ ] `deploy.php` (proxy) creado en `HOSTINGER_UPLOAD/public_html/generator/`

---

## 💾 BACKUP HOSTINGER

- [ ] Conectado a Hostinger via SSH o cPanel Terminal
- [ ] Backup completo creado: `tar -czf backup-$(date +%Y%m%d).tar.gz public_html/`
- [ ] Backup descargado a PC (seguridad)
- [ ] Crons actuales guardados: `crontab -l > crons-backup.txt`

---

## 📂 CREAR ESTRUCTURA EN HOSTINGER

```bash
mkdir -p /home/u123456789/_system/{generator,templates,logs/errors,logs/health,config,queue}
mkdir -p /home/u123456789/domains
mkdir -p /home/u123456789/staging
```

- [ ] `_system/` creado
- [ ] `_system/generator/` creado
- [ ] `_system/templates/` creado
- [ ] `_system/logs/errors/` creado
- [ ] `_system/logs/health/` creado
- [ ] `_system/config/` creado
- [ ] `_system/queue/` creado
- [ ] `domains/` creado
- [ ] `staging/` creado

---

## ⬆️ SUBIR ARCHIVOS (FileZilla)

- [ ] FileZilla conectado a Hostinger
- [ ] Templates subidos → `/_system/templates/`
- [ ] Scripts subidos → `/_system/generator/`
- [ ] `.env` subido → `/_system/config/.env`
- [ ] `deploy.php` subido → `/public_html/generator/deploy.php`

---

## 🔐 CONFIGURAR PERMISOS

```bash
chmod 700 /home/u123456789/_system
chmod 755 /home/u123456789/_system/generator
chmod 600 /home/u123456789/_system/config/.env
chmod 755 /home/u123456789/_system/generator/*.php
chmod 755 /home/u123456789/public_html/generator/deploy.php
```

- [ ] `_system/` → 700
- [ ] `.env` → 600
- [ ] Scripts → 755
- [ ] `deploy.php` → 755

---

## 📝 CREAR ARCHIVOS INICIALES

```bash
echo "[]" > /home/u123456789/_system/config/domains.json
chmod 644 /home/u123456789/_system/config/domains.json
```

- [ ] `domains.json` creado
- [ ] Permisos configurados

---

## ⚙️ CONFIGURAR CRON JOBS

**En cPanel → Cron Jobs, agregar 3 crons:**

### Cron 1: Backups (3 AM)
```
0 3 * * * /usr/bin/php /home/u123456789/_system/generator/backup-all.php >> /home/u123456789/_system/logs/backups.log 2>&1
```
- [ ] Cron backup configurado

### Cron 2: Health Checks (cada hora)
```
0 * * * * /usr/bin/php /home/u123456789/_system/generator/health-check.php >> /home/u123456789/_system/logs/health.log 2>&1
```
- [ ] Cron health check configurado

### Cron 3: Cleanup (4 AM)
```
0 4 * * * /usr/bin/php /home/u123456789/_system/generator/cleanup-old.php >> /home/u123456789/_system/logs/cleanup.log 2>&1
```
- [ ] Cron cleanup configurado

---

## 🔗 CONFIGURAR MAKE.COM

- [ ] Scenario abierto en Make.com
- [ ] Módulo HTTP encontrado
- [ ] URL actualizada: `https://otavafitness.com/generator/deploy.php`
- [ ] Header agregado: `X-Make-Secret: [TU_VALOR]`
- [ ] Scenario guardado
- [ ] Test ejecutado (Run this module only)
- [ ] Test exitoso (status 200)

---

## 🧪 TESTING

### Test 1: Health Check
```bash
php /home/u123456789/_system/generator/health-check.php
```
- [ ] Ejecutado sin errores
- [ ] Output muestra ✅

### Test 2: Crear Dominio Test
```bash
php /home/u123456789/_system/generator/create-domain.php test-migracion.com
```
- [ ] Carpeta creada en `/domains/test-migracion.com/`
- [ ] Instrucciones generadas

### Test 3: Verificar Estructura
```bash
ls -la /home/u123456789/domains/
ls -la /home/u123456789/_system/
```
- [ ] Estructura correcta visible

### Test 4: Proxy Deploy
```bash
curl -X POST https://otavafitness.com/generator/deploy.php \
  -H "X-Make-Secret: TU_SECRET" \
  -H "Content-Type: application/json" \
  -d '{"nombre_negocio":"Test"}'
```
- [ ] Respuesta 200
- [ ] JSON con `queue_id` recibido

### Test 5: Make.com End-to-End
- [ ] Formulario Google enviado
- [ ] Make.com ejecutó correctamente
- [ ] Sitio generado en `/staging/`
- [ ] Preview URL funciona

---

## ✅ VERIFICACIÓN FINAL

### Estructura:
- [ ] `/_system/generator/` ✅
- [ ] `/_system/templates/` ✅
- [ ] `/_system/config/.env` ✅
- [ ] `/domains/` ✅
- [ ] `/staging/` ✅
- [ ] `/public_html/generator/deploy.php` ✅

### Funcionalidad:
- [ ] Scripts ejecutan sin error ✅
- [ ] Crons configurados ✅
- [ ] Make.com conectado ✅
- [ ] Proxy funciona ✅
- [ ] Sitios se generan correctamente ✅

### Seguridad:
- [ ] `_system/` no accesible vía web ✅
- [ ] `.env` protegido (chmod 600) ✅
- [ ] Rate limiting activo ✅
- [ ] Logs generándose ✅

---

## 🎉 MIGRACIÓN COMPLETA

- [ ] **TODOS los checkboxes marcados**
- [ ] Sistema funcionando 24h sin errores
- [ ] Logs revisados
- [ ] Backups verificados

**¡FELICITACIONES! Tu Hostinger está perfectamente configurado** 🚀

---

## 📞 SOPORTE POST-MIGRACIÓN

**Si algo falla:**

1. Revisar logs:
   ```bash
   tail -f _system/logs/generator.log
   tail -f _system/logs/errors/*.log
   ```

2. Verificar permisos:
   ```bash
   ls -la _system/
   ```

3. Test manual:
   ```bash
   php _system/generator/health-check.php
   ```

4. Restaurar backup si necesario:
   ```bash
   tar -xzf backup-YYYYMMDD.tar.gz
   ```

---

**Fecha migración:** __________________  
**Hora inicio:** __________________  
**Hora fin:** __________________  
**Notas:** 
_____________________________________________
_____________________________________________
