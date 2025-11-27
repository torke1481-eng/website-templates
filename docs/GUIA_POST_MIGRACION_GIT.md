# 🔄 GUÍA POST-MIGRACIÓN - CONFIGURAR GIT

**Cuándo:** Después de que tu sistema funcione perfectamente en Hostinger  
**Tiempo:** 30 minutos  
**Objetivo:** Implementar workflow Git + FileZilla híbrido

---

## 🎯 POR QUÉ HACER ESTO

**Ventajas de agregar Git:**
- ✅ Control de versiones del código
- ✅ Rollback fácil si algo falla
- ✅ Colaboración en equipo
- ✅ Historial de cambios
- ✅ Deploy más rápido de código

**Lo que NO va a Git:**
- ❌ Sitios de clientes (/domains/)
- ❌ Staging (/staging/)
- ❌ Logs (_system/logs/)
- ❌ Backups (*.tar.gz)
- ❌ .env (secrets)

---

## 📋 PREPARACIÓN (10 min)

### **Paso 1: Verificar .gitignore**

Tu proyecto ya tiene `.gitignore` configurado:

```gitignore
# Ya configurado ✅
.env
_system/config/domains.json
_system/logs/
_system/queue/
/domains/
/staging/
*.tar.gz
backups/
```

---

### **Paso 2: Crear Repositorio en GitHub**

1. Ir a https://github.com/new
2. **Nombre:** `hostinger-landing-system`
3. **Visibilidad:** 🔒 **PRIVADO** (importante)
4. **NO inicializar** con README (ya tienes local)
5. Crear

**Copiar la URL:**
```
https://github.com/tu-usuario/hostinger-landing-system.git
```

---

## 🚀 CONFIGURAR GIT LOCAL (10 min)

### **Paso 1: Inicializar Git**

```bash
# En tu PC, ir a la carpeta del proyecto
cd "c:\Users\franc\OneDrive\Escritorio\public_html (3)"

# Inicializar Git
git init

# Configurar usuario (si es primera vez)
git config --global user.name "Tu Nombre"
git config --global user.email "tu@email.com"
```

---

### **Paso 2: Primer Commit**

```bash
# Ver qué se va a incluir (debe respetar .gitignore)
git status

# Agregar archivos (solo código, no datos)
git add .

# Ver qué quedó staged
git status

# Primer commit
git commit -m "feat: sistema completo v2.0.0 - arquitectura multi-dominio"
```

---

### **Paso 3: Conectar con GitHub**

```bash
# Agregar remoto
git remote add origin https://github.com/tu-usuario/hostinger-landing-system.git

# Verificar
git remote -v

# Push inicial
git branch -M main
git push -u origin main
```

**Resultado:** Tu código ahora está en GitHub ✅

---

## 🔗 CONFIGURAR GIT EN HOSTINGER (10 min)

### **Opción A: Via SSH (Recomendado)**

```bash
# Conectar a Hostinger
ssh u123456789@tudominio.com

# Ir a tu directorio
cd /home/u123456789

# Clonar tu repo en carpeta temporal
git clone https://github.com/tu-usuario/hostinger-landing-system.git sistema-git

# Configurar Git para usar tu repo
cd _system/generator
git init
git remote add origin https://github.com/tu-usuario/hostinger-landing-system.git
git fetch
git reset --hard origin/main
```

---

### **Opción B: Via cPanel Git Version Control**

1. **cPanel → Git Version Control**
2. **Clone a Repository:**
   - Repository URL: `https://github.com/tu-usuario/hostinger-landing-system.git`
   - Clone Path: `/home/u123456789/repo-temp`
3. **Clone**

**Luego conectar con tu estructura existente:**
```bash
cd /home/u123456789/_system
git init
git remote add origin https://github.com/tu-usuario/hostinger-landing-system.git
git fetch
```

---

## 📝 WORKFLOW DIARIO

### **Escenario 1: Cambiar un Script**

```bash
# En tu PC local

# 1. Editar archivo
nano _system/generator/create-domain.php

# 2. Commit
git add _system/generator/create-domain.php
git commit -m "fix: mejorar validación de dominios"
git push

# 3. En Hostinger, actualizar
ssh u123456789@tudominio.com
cd /home/u123456789/_system/generator
git pull
```

**Tiempo:** 2 minutos vs 5 minutos con FileZilla ✅

---

### **Escenario 2: Crear Nuevo Cliente**

```bash
# Esto NO usa Git, usar FileZilla como siempre

# O ejecutar directamente en Hostinger:
ssh u123456789@tudominio.com
php /home/u123456789/_system/generator/create-domain.php nuevo-cliente.com
```

---

### **Escenario 3: Actualizar Template**

```bash
# En tu PC

# 1. Editar template
nano _system/templates/landing-pro/index.html

# 2. Commit y push
git add _system/templates/landing-pro/
git commit -m "feat: agregar nueva sección de testimonios"
git push

# 3. En Hostinger
ssh u123456789@tudominio.com
cd /home/u123456789/_system/templates
git pull
```

---

### **Escenario 4: Hotfix Urgente**

```bash
# Si necesitas fix RÁPIDO:

# Opción A: FileZilla (más rápido)
# Editar directo en servidor con FileZilla
# Luego sincronizar con Git:

# En Hostinger
git add .
git commit -m "hotfix: corrección urgente"
git push

# En tu PC
git pull
```

---

## 🔄 RATIO RECOMENDADO

```
GITHUB (80%):
✓ Cambios en scripts
✓ Actualizaciones de templates
✓ Correcciones de bugs
✓ Nuevas features

FILEZILLA (20%):
✓ Nuevos clientes (create-domain.php)
✓ Revisar logs
✓ Backups manuales
✓ Ajustes de config no-código
✓ Hotfixes urgentes
```

---

## 📊 ANTES vs DESPUÉS

### **ANTES (Solo FileZilla):**
```
Cambiar 1 script:
1. Abrir FileZilla (30s)
2. Navegar a carpeta (20s)
3. Descargar archivo (10s)
4. Editar local (2min)
5. Subir archivo (15s)
6. Verificar (30s)
TOTAL: 4 minutos
```

### **DESPUÉS (Con Git):**
```
Cambiar 1 script:
1. Editar local (2min)
2. git commit + push (20s)
3. ssh + git pull (30s)
TOTAL: 3 minutos
+ Tienes historial de cambios ✅
```

---

## ✅ VENTAJAS ADICIONALES

### **1. Trabajo en Equipo**
```bash
# Tu compañero puede clonar
git clone https://github.com/tu-usuario/repo.git

# Hacer cambios en su branch
git checkout -b feature/nuevo-template
git commit -m "feat: template ecommerce"
git push origin feature/nuevo-template

# Tú revisas y merges
# Pull request en GitHub
```

### **2. Rollback Fácil**
```bash
# Si un cambio rompe algo
git log  # Ver commits
git revert abc123  # Deshacer commit específico
git push

# En Hostinger
git pull  # Se revierte automáticamente
```

### **3. Branches para Testing**
```bash
# Crear branch de desarrollo
git checkout -b development

# Probar cambios
git commit -m "test: nueva feature"
git push origin development

# En Hostinger crear staging que use esa branch
# Si funciona, merge a main
```

---

## 🎯 RECOMENDACIÓN FINAL

### **FASE 1 (Hoy): Solo FileZilla**
- Migrar sistema
- Verificar que funcione
- Familiarizarte con estructura

### **FASE 2 (Semana 1-2): Agregar Git**
- Inicializar repo
- Subir a GitHub
- Configurar en Hostinger

### **FASE 3 (Día a día): Híbrido**
- Código → Git (80%)
- Datos → FileZilla (20%)

---

## 📋 CHECKLIST POST-MIGRACIÓN

- [ ] Sistema funciona perfectamente en Hostinger
- [ ] Mínimo 1 semana sin problemas
- [ ] Familiarizado con estructura
- [ ] Entonces: Configurar Git
- [ ] Repo creado en GitHub (privado)
- [ ] Git inicializado local
- [ ] Primer commit y push
- [ ] Git configurado en Hostinger
- [ ] Workflow híbrido funcionando

---

## 💡 CONCLUSIÓN

**La guía de migración usa FileZilla porque:**
- Es más simple para primera vez
- Menos cosas que configurar
- Menor probabilidad de error

**Pero después SÍ deberías agregar Git porque:**
- Control de versiones
- Deploy más rápido
- Trabajo en equipo
- Rollback fácil

**Ambos métodos se complementan perfectamente** ✅

---

**Estado:** Guía lista para implementar después de migración  
**Tiempo:** 30 minutos de setup  
**Resultado:** Workflow profesional Git + FileZilla híbrido
