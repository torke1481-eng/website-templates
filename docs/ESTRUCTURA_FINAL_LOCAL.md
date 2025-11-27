# 📁 ESTRUCTURA FINAL LOCAL = HOSTINGER

**Esta será tu estructura local EXACTA a Hostinger**

---

## 🎯 ESTRUCTURA COMPLETA

```
c:\Users\franc\OneDrive\Escritorio\public_html (3)\
│
├── _system/                          ← Sistema protegido (NO web)
│   ├── generator/                    ← Scripts PHP
│   │   ├── create-domain.php         ✅ Crear nuevos dominios
│   │   ├── backup-client.php         ✅ Backup individual
│   │   ├── backup-all.php            ✅ Backup masivo
│   │   ├── health-check.php          ✅ Monitoreo
│   │   ├── verify-domain.php         ✅ Verificación
│   │   ├── cleanup-old.php           ✅ Limpieza
│   │   ├── deploy-v4-mejorado.php    ✅ Deploy real
│   │   └── verify-installation.php   ✅ Test instalación
│   │
│   ├── templates/                    ← Templates base
│   │   ├── landing-pro/
│   │   │   ├── index.html
│   │   │   ├── styles.css
│   │   │   ├── script.js
│   │   │   └── images/
│   │   │
│   │   ├── landing-basica/
│   │   │   ├── index.html
│   │   │   ├── styles.css
│   │   │   └── script.js
│   │   │
│   │   └── componentes-globales/
│   │       ├── header/
│   │       │   ├── header.html
│   │       │   ├── header-styles.css
│   │       │   └── header-script.js
│   │       │
│   │       ├── footer/
│   │       │   ├── footer.html
│   │       │   └── footer-styles.css
│   │       │
│   │       └── chatbot/
│   │           ├── chatbot-script.js
│   │           └── chatbot-styles.css
│   │
│   ├── config/                       ← Configuración
│   │   ├── .env                      ✅ Secrets (NO Git)
│   │   ├── .env.example              ✅ Template
│   │   └── domains.json              ✅ Lista dominios
│   │
│   ├── logs/                         ← Logs del sistema
│   │   ├── errors/
│   │   │   └── YYYY-MM-DD.log
│   │   ├── health/
│   │   │   └── health-YYYY-MM-DD.log
│   │   ├── generator.log
│   │   ├── backups.log
│   │   └── make-access.log
│   │
│   └── queue/                        ← Cola async
│       └── [archivos JSON temporales]
│
├── public_html/                      ← Tu sitio principal (WEB)
│   ├── index.html                    ✅ Home principal
│   ├── styles.css                    ✅ CSS principal
│   ├── script.js                     ✅ JS principal
│   │
│   └── generator/                    ← Accesible por Make.com
│       └── deploy.php                ✅ Proxy seguro
│
├── domains/                          ← Sitios de clientes
│   ├── cliente1.com/
│   │   ├── public_html/              ← Document root
│   │   │   ├── index.html
│   │   │   ├── css/
│   │   │   ├── js/
│   │   │   └── images/
│   │   ├── logs/
│   │   ├── backups/
│   │   └── .metadata.json
│   │
│   └── cliente2.com/
│       └── [misma estructura]
│
├── staging/                          ← Previews temporales
│   ├── preview-abc123/
│   │   ├── index.html
│   │   ├── css/
│   │   └── js/
│   │
│   └── preview-xyz789/
│       └── [archivos preview]
│
├── docs/                             ← Documentación (NO subir)
│   ├── AUDITORIA_*.md
│   ├── GUIA_*.md
│   ├── RESUMEN_*.md
│   └── README.md
│
├── .env.example                      ✅ Config template
├── .gitignore                        ✅ Git exclusions
├── README.md                         ✅ Docs principal
└── reorganizar-estructura.ps1        ✅ Script de setup

```

---

## 🔄 MAPEO LOCAL → HOSTINGER

| Local | Hostinger | Descripción |
|-------|-----------|-------------|
| `_system/` | `/home/u123456789/_system/` | Sistema protegido |
| `public_html/` | `/home/u123456789/public_html/` | Sitio principal |
| `domains/` | `/home/u123456789/domains/` | Sitios clientes |
| `staging/` | `/home/u123456789/staging/` | Previews |
| `docs/` | ❌ NO SUBIR | Solo local |

---

## 📝 QUÉ VA DONDE

### **_system/** (NO accesible vía web)
```
Contiene: Todo el sistema interno
Acceso: Solo vía PHP/SSH
Subir: Sí, completo
```

### **public_html/** (SÍ accesible vía web)
```
Contiene: Tu sitio + proxy
Acceso: https://otavafitness.com/
Subir: Sí, completo
```

### **domains/** (Subdominios/Addon domains)
```
Contiene: Sitios de clientes
Acceso: https://cliente.com/
Crear: Con create-domain.php
```

### **staging/** (Previews temporales)
```
Contiene: Previews de Make.com
Acceso: https://otavafitness.com/staging/slug/
Auto-limpieza: >7 días
```

### **docs/** (Solo documentación)
```
Contiene: Auditorías, guías
Acceso: Solo local
Subir: NO
```

---

## ✅ ARCHIVOS IMPORTANTES

### **Configuración:**
```
_system/config/.env              → Secrets reales (NO Git)
_system/config/.env.example      → Template
_system/config/domains.json      → Lista de dominios
```

### **Scripts críticos:**
```
_system/generator/create-domain.php
_system/generator/health-check.php
_system/generator/backup-all.php
public_html/generator/deploy.php  → Proxy Make.com
```

### **Templates:**
```
_system/templates/landing-pro/
_system/templates/landing-basica/
_system/templates/componentes-globales/
```

---

## 🚀 CÓMO USAR ESTA ESTRUCTURA

### **1. Reorganizar local:**
```powershell
# Ejecutar script
powershell -ExecutionPolicy Bypass .\reorganizar-estructura.ps1

# Revisar resultado
dir _system_nuevo
dir public_html_nuevo

# Si OK, renombrar
ren _system_nuevo _system
ren public_html_nuevo public_html
```

### **2. Configurar .env:**
```bash
cd _system\config
copy .env.example .env
notepad .env  # Editar con valores reales
```

### **3. Subir a Hostinger:**
```
FileZilla:
  Local: _system\       → Remote: /home/u123/_system/
  Local: public_html\   → Remote: /home/u123/public_html/
  Local: domains\       → Remote: /home/u123/domains/
  Local: staging\       → Remote: /home/u123/staging/
```

---

## 🔐 PERMISOS EN HOSTINGER

```bash
# Después de subir, configurar:
chmod 700 /home/u123456789/_system
chmod 755 /home/u123456789/_system/generator
chmod 600 /home/u123456789/_system/config/.env
chmod 644 /home/u123456789/_system/config/domains.json
chmod 755 /home/u123456789/_system/generator/*.php
chmod 755 /home/u123456789/public_html/generator/deploy.php
```

---

## 📊 TAMAÑOS APROXIMADOS

```
_system/                  ~50 MB
  ├── generator/          ~500 KB
  ├── templates/          ~45 MB
  ├── config/             ~10 KB
  ├── logs/               ~1 MB (crece)
  └── queue/              ~100 KB (temporal)

public_html/              ~5 MB
domains/                  variable (por cliente)
staging/                  variable (temporal)
docs/                     ~5 MB (NO subir)
```

---

## ✅ VERIFICACIÓN

Después de reorganizar, verificar:

```powershell
# Estructura correcta
Test-Path _system\generator\create-domain.php
Test-Path _system\config\.env.example
Test-Path public_html\generator\deploy.php

# Templates presentes
Test-Path _system\templates\landing-pro\index.html
Test-Path _system\templates\componentes-globales\header\header.html

# Configuración
Test-Path _system\config\domains.json
```

Todo debe ser `True` ✅

---

## 🎯 ESTADO FINAL

**Local:**
```
✅ Estructura organizada
✅ Scripts en _system/generator/
✅ Templates en _system/templates/
✅ Proxy en public_html/generator/
✅ .env.example listo
✅ domains.json vacío creado
✅ Documentación en /docs/
```

**Listo para:**
```
1. Configurar .env con valores reales
2. Subir a Hostinger con FileZilla
3. Configurar permisos
4. Configurar cron jobs
5. Testing
6. Producción
```

---

**Esta estructura ES la que tendrás en Hostinger** 🚀
