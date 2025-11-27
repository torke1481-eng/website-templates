# 🚀 AUDITORÍA #13 - GITHUB vs FILEZILLA vs HÍBRIDO

**Fecha:** 25 Nov 2025, 00:24 AM  
**Tipo:** Análisis de Métodos de Deployment  
**Estado:** 🔴 **DECISIÓN CRÍTICA PARA MIGRACIÓN**

---

## 🎯 OBJETIVO

Determinar el MEJOR método para organizar y migrar archivos a Hostinger:
1. **GitHub** (Git + deployment automático)
2. **FileZilla** (FTP/SFTP manual)
3. **Híbrido** (combinación de ambos)

---

## 📊 COMPARACIÓN RÁPIDA

| Aspecto | GitHub | FileZilla | Ganador |
|---------|--------|-----------|---------|
| **Velocidad** | ⚠️ Lento (CI/CD) | ✅ Rápido | FileZilla |
| **Control Versiones** | ✅ Completo | ❌ Ninguno | GitHub |
| **Archivos Grandes** | ❌ Límite 100MB | ✅ Sin límite | FileZilla |
| **Automatización** | ✅ CI/CD | ❌ Manual | GitHub |
| **Seguridad** | ⚠️ Público/Privado | ✅ Directo | FileZilla |
| **Rollback** | ✅ Fácil (git revert) | ❌ Difícil | GitHub |
| **Colaboración** | ✅ Excelente | ❌ Nula | GitHub |
| **Curva Aprendizaje** | 🟡 Media | ✅ Baja | FileZilla |
| **Costo** | ✅ Gratis | ✅ Gratis | Empate |

---

## 🟢 OPCIÓN 1: GITHUB

### **Cómo Funciona:**

```
Local → Git Commit → GitHub Repo → GitHub Actions → Hostinger
```

#### **Setup:**

1. **Crear repositorio:**
```bash
cd /Users/franc/OneDrive/Escritorio/public_html
git init
git remote add origin https://github.com/tu-usuario/hostinger-system.git
```

2. **GitHub Actions para auto-deploy:**
```yaml
# .github/workflows/deploy.yml
name: Deploy to Hostinger

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Deploy via FTP
      uses: SamKirkland/FTP-Deploy-Action@4.3.0
      with:
        server: ftp.hostinger.com
        username: ${{ secrets.FTP_USERNAME }}
        password: ${{ secrets.FTP_PASSWORD }}
        server-dir: /home/u123456/_system/
        exclude: |
          **/.git*
          **/.git*/**
          **/node_modules/**
          **/.env
```

3. **Configurar secrets en GitHub:**
```
Settings → Secrets → New repository secret
- FTP_USERNAME: u123456789
- FTP_PASSWORD: tu-password-ftp
```

### **✅ VENTAJAS:**

1. **Control de versiones completo:**
```bash
# Ver historial
git log

# Volver a versión anterior
git revert abc123

# Comparar cambios
git diff HEAD~1
```

2. **Colaboración en equipo:**
```bash
# Tu compañero puede clonar
git clone https://github.com/tu-usuario/repo.git

# Hacer cambios
git checkout -b feature/nuevo-template

# Pull request para revisar antes de mergear
```

3. **Backup automático:**
- Todo en GitHub = backup en la nube
- No pierdes código aunque se rompa tu PC

4. **CI/CD automatizado:**
- Push → Auto-deploy
- Tests automáticos antes de deploy
- Notificaciones si algo falla

5. **Documentation:**
- README.md para documentar
- Issues para tracking de bugs
- Wiki para documentación extensa

### **❌ DESVENTAJAS:**

#### **1. Límite de Tamaño de Archivos** 🔴

**Problema:**
```
GitHub limita archivos a 100 MB
Repositorio completo máximo: 5 GB (recomendado < 1 GB)

Tu caso:
/templates/landing-pro/images/hero-high-res.jpg  → 15 MB ✅
/templates/landing-pro/video-bg.mp4              → 150 MB ❌
/backups/cliente-full-backup.tar.gz              → 500 MB ❌
```

**Solución:**
```bash
# .gitignore (NO subir archivos pesados)
*.mp4
*.tar.gz
*.zip
backups/
/domains/*/backups/
/staging/
uploads/
```

#### **2. Archivos Sensibles Expuestos** 🔴

**Problema:**
```php
// config.php
$db_password = "password123";  // ← Si subes a GitHub público

→ Cualquiera ve tu password
→ Bot de GitHub detecta y reporta
→ Vulnerabilidad CRÍTICA
```

**Solución:**
```bash
# .gitignore
.env
config.php
*.key
*.pem
_system/config/domains.json  # Tiene datos de clientes

# Usar variables de entorno
# .env.example (SÍ subir)
DB_PASSWORD=your_password_here

# .env (NO subir)
DB_PASSWORD=real_password_123
```

#### **3. Deployment Lento** ⚠️

**Problema:**
```
Push → GitHub Actions inicia → Install dependencies → FTP upload
Tiempo: 2-5 minutos para deploy simple

FileZilla: 10 segundos para subir 1 archivo
```

**Cuándo es problema:**
- Hotfix urgente (sitio caído)
- Cambio rápido en producción
- Debugging en vivo

#### **4. Historial Público (si repo público)** ⚠️

**Problema:**
```
Commits antiguos pueden contener:
- Passwords que luego borraste
- Emails de clientes
- API keys antiguas

→ Siguen en historial de Git
→ Cualquiera puede verlos
```

**Solución:**
- Usar repositorio PRIVADO
- O limpiar historial con BFG Repo-Cleaner

#### **5. Conflictos de Merge** 🟡

**Problema:**
```bash
# Tú editas index.html localmente
# Tu compañero edita index.html en otro PC
# Ambos hacen push

→ Merge conflict
→ Necesitas resolver manualmente
```

#### **6. No Sirve para Sitios de Clientes** 🔴

**Problema:**
```
/domains/cliente1.com/public_html/
→ Esto se genera DINÁMICAMENTE en servidor
→ NO tiene sentido subirlo a Git
→ Cada cliente tendría que estar en repo

Imagina: 500 clientes × 10MB = 5GB en repo
→ GitHub se vuelve lento
→ Clones toman 20+ minutos
```

---

## 🔵 OPCIÓN 2: FILEZILLA (FTP/SFTP)

### **Cómo Funciona:**

```
Local → FileZilla → FTP/SFTP → Hostinger
```

#### **Setup:**

1. **Descargar FileZilla:**
   - https://filezilla-project.org/

2. **Configurar conexión:**
```
Host: ftp.hostinger.com
Username: u123456789
Password: tu-password
Port: 21 (FTP) o 22 (SFTP)
```

3. **Guardar como Site:**
   - File → Site Manager → New Site
   - Guardar credenciales

### **✅ VENTAJAS:**

#### **1. Velocidad EXTREMA** ⚡

**Benchmark:**
```
Cambio 1 archivo (10 KB):
FileZilla: 2 segundos
GitHub: 2-3 minutos

Upload carpeta completa (500 MB):
FileZilla: 5-10 minutos (depende internet)
GitHub: ❌ No permite archivos >100MB
```

#### **2. Sin Límites de Tamaño** ✅

```
Subir video de 2 GB: ✅ OK
Subir backup de 5 GB: ✅ OK
Subir lo que sea: ✅ OK (mientras Hostinger tenga espacio)
```

#### **3. Control Total** 💪

```
- Ves EXACTAMENTE qué hay en servidor
- Puedes eliminar archivos directamente
- Permisos (chmod) desde GUI
- Rename, move, todo en tiempo real
```

#### **4. Debugging en Vivo** 🔧

```
Escenario:
1. Sitio roto en producción
2. Abres FileZilla
3. Editas index.php directamente en servidor
4. Guardas
5. Refresh navegador
6. Fixed en 30 segundos

Con GitHub: 3+ minutos mínimo
```

#### **5. No Requiere Git** 🎓

```
Tu cliente/equipo no técnico puede usar FileZilla
- No necesita entender Git
- No necesita terminal
- Drag & drop visual
```

### **❌ DESVENTAJAS:**

#### **1. CERO Control de Versiones** 🔴

**Problema:**
```
Día 1: Subes index.html (versión buena)
Día 2: Editas y subes index.html (versión con bug)
Día 3: Quieres volver a versión del Día 1

→ ❌ NO PUEDES
→ No hay historial
→ Perdiste código
```

**Workaround:**
```bash
# Backup manual antes de cada cambio
cp index.html index.html.backup-20251125
```

#### **2. Sin Colaboración** 👥

**Problema:**
```
Tú y tu compañero editan MISMO archivo simultáneamente

Tú: Subes a las 10:00 AM (tus cambios)
Él: Sube a las 10:05 AM (sus cambios)

→ Tus cambios se pierden
→ Él sobrescribe sin saber
→ Conflicto silencioso
```

#### **3. Sin Automatización** 🤖

**Problema:**
```
Cada deploy:
1. Abrir FileZilla
2. Conectar
3. Navegar a carpeta
4. Seleccionar archivos
5. Upload
6. Esperar
7. Verificar

TODO MANUAL, CADA VEZ
```

#### **4. Errores Humanos** 💀

**Escenarios REALES:**

```bash
# Error 1: Eliminar carpeta incorrecta
Quieres: rm /staging/test/
Haces: rm /domains/  ← 💀 Eliminaste TODOS los clientes

# Error 2: Sobrescribir archivo crítico
Drag & drop equivocado
→ Sobrescribes config.php de producción
→ Sitio caído

# Error 3: Permisos incorrectos
Subes archivo con chmod 777
→ Vulnerabilidad de seguridad

# Error 4: Olvidar refrescar
Editas archivo localmente
Olvidas subirlo
Cliente dice "no funciona"
Tú dices "pero funcionaba en local" 🤦
```

#### **5. Sin Logs de Cambios** 📋

**Problema:**
```
Cliente: "El sitio estaba bien el martes, ¿qué pasó?"
Tú: "Umm... no sé qué archivos cambié esta semana"

Con Git: git log --since="1 week ago"
Con FileZilla: ¯\_(ツ)_/¯
```

#### **6. Credenciales en Texto Plano** 🔐

**Problema:**
```
FileZilla guarda passwords en:
C:\Users\franc\AppData\Roaming\FileZilla\sitemanager.xml

<Pass encoding="base64">cGFzc3dvcmQxMjM=</Pass>

→ Base64 NO es encriptación
→ Cualquiera con acceso a tu PC puede leer
→ Malware puede robar credenciales
```

---

## 🟣 OPCIÓN 3: HÍBRIDO (RECOMENDADO) ⭐

### **La Mejor de Ambos Mundos:**

```
┌─────────────────────────────────────────────┐
│  GITHUB: Sistema (_system/)                 │
│  - Scripts PHP                              │
│  - Templates                                │
│  - Configuración                            │
│  - Control de versiones ✅                  │
└─────────────────────────────────────────────┘
                    ↓
              Git Push
                    ↓
┌─────────────────────────────────────────────┐
│  FILEZILLA: Dinámico                        │
│  - /domains/ (sitios clientes)              │
│  - /staging/ (previews)                     │
│  - Backups                                  │
│  - Uploads                                  │
│  - Hotfixes urgentes ✅                     │
└─────────────────────────────────────────────┘
```

### **Estructura Recomendada:**

```
GITHUB REPO: "hostinger-system"
├── _system/
│   ├── generator/
│   │   ├── deploy-v4-mejorado.php    # ✅ Git
│   │   ├── create-domain.php         # ✅ Git
│   │   └── backup-client.php         # ✅ Git
│   ├── templates/
│   │   ├── landing-pro/              # ✅ Git
│   │   │   ├── index.html
│   │   │   ├── styles.css
│   │   │   └── script.js
│   │   └── componentes-globales/     # ✅ Git
│   └── config/
│       └── .env.example              # ✅ Git (sin datos reales)
│
├── public_html/
│   ├── index.html                    # ✅ Git
│   ├── download.php                  # ✅ Git
│   └── .htaccess                     # ✅ Git
│
└── .gitignore                        # ✅ Git

NO EN GITHUB (usar FileZilla):
├── /domains/                         # ❌ Git → FileZilla
│   └── cliente*.com/
├── /staging/                         # ❌ Git → FileZilla
├── /_system/config/domains.json     # ❌ Git → FileZilla (datos reales)
├── /_system/logs/                   # ❌ Git → FileZilla
└── backups/                         # ❌ Git → FileZilla
```

### **Workflow Híbrido:**

#### **Para Desarrollo del Sistema:**
```bash
# 1. Editar scripts localmente
nano _system/generator/deploy-v4-mejorado.php

# 2. Probar localmente

# 3. Commit a Git
git add _system/generator/deploy-v4-mejorado.php
git commit -m "fix: validación de slug mejorada"
git push origin main

# 4. GitHub Actions auto-deploya a Hostinger
# O deploy manual con:
git pull  # En servidor via SSH
```

#### **Para Sitios de Clientes:**
```bash
# 1. Make.com genera sitio en /staging/
# 2. Cliente aprueba
# 3. Ejecutar create-domain.php (crea en /domains/)
# 4. Configurar cPanel manualmente
# 5. Usar FileZilla para ajustes finales si es necesario
```

#### **Para Hotfixes Urgentes:**
```bash
# Opción A: Git (mejor práctica)
git add .
git commit -m "hotfix: error crítico corregido"
git push

# Opción B: FileZilla (si es URGENTE)
# Editar directamente en servidor
# Luego sincronizar con Git
```

---

## 🔴 SITUACIONES DE FALLO

### **FALLO #1: GitHub Actions Cae** 🔴

**Escenario:**
```
GitHub tiene outage (raro pero pasa)
→ Tu push queda en cola
→ No se deploya
→ Cliente urgente esperando
```

**Impacto:** Alto  
**Probabilidad:** Baja (1-2 veces al año)

**Solución:**
```bash
# Fallback a FileZilla
1. Abrir FileZilla
2. Subir archivos manualmente
3. Continuar trabajando
4. Cuando GitHub vuelva, hacer push para sincronizar
```

---

### **FALLO #2: Archivo >100MB Necesario** 🔴

**Escenario:**
```
Cliente quiere video de fondo de 200 MB
→ No puedes subirlo a Git
→ Git LFS requiere plan pago
```

**Impacto:** Alto  
**Probabilidad:** Media

**Solución:**
```bash
# Usar FileZilla para archivos grandes
# .gitignore
*.mp4
/videos/

# Documentar en README:
"Videos se manejan vía FTP, no Git"
```

---

### **FALLO #3: Credenciales Accidentalmente Comiteadas** 🔴

**Escenario:**
```bash
# Editas config.php con password real
git add config.php
git commit -m "update config"
git push

→ ❌ Password ahora público en GitHub
→ Bot de GitHub te alerta
→ CRISIS DE SEGURIDAD
```

**Impacto:** CRÍTICO  
**Probabilidad:** Alta (error humano común)

**Solución:**
```bash
# 1. Cambiar password INMEDIATAMENTE
# 2. Limpiar historial de Git
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch config.php" \
  --prune-empty --tag-name-filter cat -- --all

# 3. Force push
git push origin --force --all

# 4. Mejor: Usar .env desde el inicio
# .gitignore
.env
config.php
```

---

### **FALLO #4: Conflicto de Merge Complejo** 🟡

**Escenario:**
```
Tú: Editas deploy-v4.php líneas 1-100
Compañero: Edita deploy-v4.php líneas 90-200

→ Merge conflict en líneas 90-100
→ Git no sabe cómo resolver
→ Requiere resolución manual
```

**Impacto:** Medio  
**Probabilidad:** Alta (trabajo en equipo)

**Solución:**
```bash
# Pull primero, siempre
git pull origin main

# Si hay conflicto:
# 1. Abrir archivo con conflicto
# 2. Buscar markers:
<<<<<<< HEAD
tu código
=======
código de compañero
>>>>>>> branch

# 3. Resolver manualmente
# 4. Commit la resolución
git add .
git commit -m "merge: resuelto conflicto en deploy-v4.php"
```

---

### **FALLO #5: FileZilla Sobrescribe Sin Avisar** 🔴

**Escenario:**
```
Compañero edita index.html en servidor (vía FileZilla)
Tú subes index.html desde local (versión vieja)

→ Cambios de compañero se pierden
→ Él no sabe hasta que cliente reporta bug
```

**Impacto:** Alto  
**Probabilidad:** Alta (sin coordinación)

**Solución:**
```
# Regla de equipo:
"SIEMPRE usar Git para archivos de código"
"FileZilla SOLO para archivos dinámicos (/domains/, backups)"

# O: Activar "Ask before overwriting" en FileZilla
Edit → Settings → Transfers → File exists action → Ask
```

---

### **FALLO #6: Git Repo Crece Descontroladamente** 🟡

**Escenario:**
```
Alguien comitea backup de 500 MB
→ Git no puede eliminarlo fácilmente
→ Repo ahora es 500 MB
→ Todos los clones son lentos
```

**Impacto:** Medio  
**Probabilidad:** Media

**Solución:**
```bash
# .gitignore estricto DESDE EL INICIO
*.tar.gz
*.zip
*.sql
backups/
/domains/
/staging/
*.mp4
*.avi

# Si ya pasó: BFG Repo-Cleaner
bfg --strip-blobs-bigger-than 10M repo.git
```

---

### **FALLO #7: Deploy Automático Rompe Producción** 🔴

**Escenario:**
```
Push a main → GitHub Actions deploya automáticamente
→ Código tiene bug crítico
→ Todos los sitios caen
→ No hay rollback automático
```

**Impacto:** CRÍTICO  
**Probabilidad:** Media

**Solución:**
```yaml
# GitHub Actions: Deploy solo en branches específicos
on:
  push:
    branches: [ production ]  # NO main

# Workflow:
main → staging environment (auto)
staging → production (manual approval)
```

---

### **FALLO #8: FileZilla Credenciales Robadas** 🔴

**Escenario:**
```
Malware en tu PC
→ Lee sitemanager.xml
→ Roba credenciales FTP
→ Atacante sube malware a tu servidor
```

**Impacto:** CRÍTICO  
**Probabilidad:** Baja (pero posible)

**Solución:**
```
1. Usar Master Password en FileZilla
   Edit → Settings → Security → Use master password

2. O mejor: Usar SSH keys en vez de password
   
3. 2FA en Hostinger (si disponible)

4. Monitoring: Revisar logs de acceso FTP regularmente
```

---

### **FALLO #9: Permisos Incorrectos Post-Deploy** 🟡

**Escenario:**
```
GitHub Actions sube archivos
→ Permisos default: 644
→ Necesitas 755 para scripts PHP
→ Scripts no ejecutan
```

**Impacto:** Alto  
**Probabilidad:** Alta (primer deploy)

**Solución:**
```yaml
# GitHub Actions: Agregar step de permisos
- name: Set Permissions
  run: |
    chmod 755 _system/generator/*.php
    chmod 600 _system/config/*.json
```

---

### **FALLO #10: Cache de GitHub Actions Corrupto** 🟡

**Escenario:**
```
GitHub Actions usa cache para velocidad
→ Cache se corrompe
→ Deploy falla con error extraño
→ "Funcionaba ayer, hoy no"
```

**Impacto:** Medio  
**Probabilidad:** Baja

**Solución:**
```yaml
# Limpiar cache manualmente:
# GitHub Repo → Actions → Caches → Delete all

# O forzar rebuild sin cache
git commit --allow-empty -m "force rebuild"
git push
```

---

## 📊 RECOMENDACIÓN FINAL

### **Para TU Proyecto Específico:**

```
┌──────────────────────────────────────────────────┐
│  🟣 MÉTODO HÍBRIDO (80% GitHub + 20% FileZilla) │
└──────────────────────────────────────────────────┘

GITHUB (Control, Versiones, Calidad):
✅ _system/generator/*.php
✅ _system/templates/*
✅ public_html/index.html
✅ public_html/.htaccess
✅ Documentación

FILEZILLA (Dinámico, Rápido, Binarios):
✅ /domains/* (sitios clientes)
✅ /staging/* (previews)
✅ /_system/logs/*
✅ /_system/config/domains.json (datos reales)
✅ Backups (*.tar.gz)
✅ Hotfixes urgentes
```

### **Justificación:**

1. **Sistema = GitHub** ✅
   - Código cambia poco
   - Requiere versiones
   - Equipo colabora
   - Rollback crucial

2. **Clientes = FileZilla** ✅
   - Se generan dinámicamente
   - Son muchos (escalabilidad)
   - No necesitan versiones
   - Archivos pesados

3. **Hotfixes = FileZilla** ✅
   - Velocidad crítica
   - Luego sincronizar con Git

---

## ✅ SETUP RECOMENDADO

### **1. Inicializar Git (Local):**
```bash
cd "c:\Users\franc\OneDrive\Escritorio\public_html (3)"

# Inicializar
git init

# Crear .gitignore
cat > .gitignore << EOF
# Dinámico (usar FileZilla)
/domains/
/staging/
_system/logs/
_system/config/domains.json
_system/queue/

# Sensible
.env
*.key
*.pem

# Pesado
*.tar.gz
*.zip
*.mp4
*.avi
backups/

# Sistema
.DS_Store
Thumbs.db
EOF

# Primer commit
git add .
git commit -m "initial commit: sistema base"
```

### **2. Crear Repo en GitHub:**
```
1. Ir a github.com
2. New Repository
3. Nombre: hostinger-landing-system
4. Privado: ✅ (IMPORTANTE)
5. No inicializar con README (ya tienes local)
```

### **3. Conectar Local con GitHub:**
```bash
git remote add origin https://github.com/tu-usuario/hostinger-landing-system.git
git branch -M main
git push -u origin main
```

### **4. Configurar FileZilla:**
```
Site Manager → New Site
- Host: ftp.hostinger.com
- Port: 21
- Protocol: FTP
- Encryption: Use explicit FTP over TLS
- User: u123456789
- Password: [tu-password]

Bookmark folders:
- /domains/
- /staging/
- /_system/logs/
```

### **5. Workflow Diario:**
```bash
# Cambios en sistema
nano _system/generator/deploy-v4-mejorado.php
git add .
git commit -m "feat: mejora en validación"
git push

# Cambios en sitios clientes
# → Usar FileZilla

# Hotfix urgente
# → FileZilla primero
# → Luego sincronizar con Git
```

---

## 📋 COMPARACIÓN FINAL

| Criterio | GitHub | FileZilla | Híbrido |
|----------|--------|-----------|---------|
| **Tu caso de uso** | 6/10 | 7/10 | **10/10** ✅ |
| **Velocidad** | 4/10 | 10/10 | **9/10** ✅ |
| **Seguridad** | 9/10 | 6/10 | **9/10** ✅ |
| **Control** | 10/10 | 3/10 | **9/10** ✅ |
| **Escalabilidad** | 5/10 | 8/10 | **10/10** ✅ |
| **Facilidad** | 6/10 | 9/10 | **7/10** ✅ |

---

## 🎯 DECISIÓN

```
┌──────────────────────────────────────────┐
│  ✅ USAR MÉTODO HÍBRIDO                  │
│                                          │
│  - Sistema: GitHub                       │
│  - Clientes: FileZilla                   │
│  - Hotfixes: FileZilla → Git sync        │
│                                          │
│  RATIO: 80% Git / 20% FTP                │
└──────────────────────────────────────────┘
```

**Razones:**
1. ✅ Control de versiones del sistema
2. ✅ Velocidad para cambios dinámicos
3. ✅ Escalable a 500+ clientes
4. ✅ Colaboración en equipo
5. ✅ Rollback fácil del sistema
6. ✅ Sin límites de tamaño para backups

---

**Estado:** ✅ ANÁLISIS COMPLETO  
**Recomendación:** HÍBRIDO (GitHub + FileZilla)  
**Próximo paso:** Implementar setup híbrido
