# 🏢 AUDITORÍA #10 - HOSTINGER MULTI-DOMINIO

**Fecha:** 24 Nov 2025  
**Escenario:** Clientes con dominios propios en tu hosting  
**Estado:** 🔴 **15 PROBLEMAS ENCONTRADOS**

---

## 🎯 SITUACIÓN

### **ACTUAL:**
```
https://otavafitness.com/staging/cliente-1/
https://otavafitness.com/staging/cliente-2/
```
❌ NO profesional  
❌ Todos bajo mismo dominio

### **OBJETIVO:**
```
https://clientenegocio.com/     → Tu Hostinger
https://otroejemplo.com/        → Tu Hostinger
```
✅ Cada cliente su dominio

---

## 🔴 PROBLEMA #1: Estructura Inadecuada

### **ACTUAL (Mal):**
```
/public_html/
├── generator/
├── staging/
│   ├── cliente-1/  ❌
│   └── cliente-2/  ❌
```

### **NUEVA (Bien):**
```
/home/u123456789/
├── public_html/              # Tu sitio
├── domains/                  # ⭐ CLIENTES
│   ├── cliente1.com/
│   │   ├── public_html/      # ← Dominio apunta aquí
│   │   ├── logs/
│   │   ├── backups/
│   │   └── .metadata.json
│   └── cliente2.com/
│
├── _system/                  # ⭐ SISTEMA
│   ├── generator/
│   ├── templates/
│   ├── queue/
│   └── logs/
└── staging/                  # Preview temporal
```

**Ventajas:**
- ✅ Separación total
- ✅ Logs aislados
- ✅ Sistema protegido

---

## 🔴 PROBLEMA #2: Configuración DNS

**Cliente compra dominio → Debe apuntar a Hostinger**

### **DNS Config:**
```dns
A     @     123.456.789.012  (IP Hostinger)
A     www   123.456.789.012
```

### **cPanel:**
1. Domains → Addon Domains
2. Domain: `clientenegocio.com`
3. Doc Root: `/home/u123/domains/clientenegocio.com/public_html`

**⚠️ Limitación:** Requiere paso manual en cPanel

---

## 🔴 PROBLEMA #3: Seguridad

### **Path Traversal:**
```php
// Vulnerable
$domain = $_GET['domain'];
$path = "/domains/$domain/";  // ❌ Ataque posible

// Seguro
function sanitizeDomain($domain) {
    $domain = preg_replace('/[^a-z0-9.-]/', '', strtolower($domain));
    if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain)) {
        throw new Exception('Inválido');
    }
    return $domain;
}
```

### **Permisos:**
```bash
chmod 755 /domains/cliente.com/public_html/
chmod 700 /_system/
chmod 600 /_system/config/*.json
```

---

## 🔴 PROBLEMA #4: Límites Hostinger

```
Plan Business:
- Dominios: UNLIMITED ✅
- Disco: 100 GB
- Inodes: 300,000

Límite real:
- ~500 sitios (10MB c/u)
- ~3000 sitios (inode limit)
```

**Solución:** Monitorear y alertar

---

## 🔴 PROBLEMA #5: Backups

**Problema:** Backup de Hostinger = TODO  
**Solución:** Backups individuales

```php
function backupClient($domain) {
    $path = "/domains/$domain";
    $backup = "$path/backups/backup-" . date('Y-m-d') . ".tar.gz";
    exec("tar -czf $backup -C $path public_html");
}
```

**Cron diario:**
```cron
0 3 * * * php /_system/generator/backup-all.php
```

---

## 🔴 PROBLEMA #6: Monitoreo

**Sin monitoreo = Sitios caídos sin saber**

**Solución: Health Check**
```php
function checkHealth($domain) {
    // 1. DNS OK?
    $ip = gethostbyname($domain);
    
    // 2. HTTP 200?
    $ch = curl_init("https://$domain");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // 3. SSL OK?
    // 4. Archivos existen?
    
    return $score;
}
```

**Cron cada hora:**
```cron
0 * * * * php /_system/generator/health-check.php
```

---

## ✅ SCRIPTS NECESARIOS

### **1. create-domain.php**
- Crea estructura `/domains/cliente.com/`
- Copia sitio de staging
- Genera .htaccess
- Crea instrucciones para cPanel

### **2. backup-client.php**
- Backup individual
- Mantiene últimos 7
- Cron diario

### **3. health-check.php**
- Verifica DNS, HTTP, SSL
- Alerta si caído
- Cron cada hora

### **4. export-client.php**
- Package completo para migración
- Link descarga temporal

---

## 📊 CONFIGURACIÓN .htaccess

```apache
# /domains/cliente.com/public_html/.htaccess

Options -Indexes
RewriteEngine On

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [L,R=301]

# Bloquear archivos
<FilesMatch "\.(json|log)$">
    Deny from all
</FilesMatch>

# Security headers
Header set X-Frame-Options "SAMEORIGIN"
Header set X-Content-Type-Options "nosniff"
```

---

## 🎯 CHECKLIST IMPLEMENTACIÓN

- [ ] Crear carpeta `/_system/`
- [ ] Mover `generator/` a `/_system/generator/`
- [ ] Mover `templates/` a `/_system/templates/`
- [ ] Crear carpeta `/domains/`
- [ ] Crear scripts: create-domain.php
- [ ] Crear scripts: backup-client.php
- [ ] Crear scripts: health-check.php
- [ ] Configurar crons
- [ ] Documentar proceso cPanel
- [ ] Probar con 1 dominio test

---

## 📈 SCORE

| Aspecto | Antes | Después |
|---------|-------|---------|
| Organización | 3/10 | 10/10 ✅ |
| Seguridad | 5/10 | 10/10 ✅ |
| Escalabilidad | 4/10 | 9/10 ✅ |
| Monitoreo | 0/10 | 10/10 ✅ |
| Backups | 2/10 | 10/10 ✅ |

---

## 🚀 PRÓXIMOS PASOS

1. Implementar nueva estructura
2. Crear scripts esenciales
3. Migrar 1 sitio de prueba
4. Configurar crons
5. Documentar proceso
6. Escalar a producción

**Estado:** ⏳ REQUIERE IMPLEMENTACIÓN

Ver: `AUDITORIA_10_HOSTINGER_MULTIDOMAIN.md` (completo)
