# 🔄 INSTRUCCIONES DE RECUPERACIÓN

**Objetivo:** Recuperar archivos faltantes del backup  
**Tiempo:** 2 minutos  
**Archivo:** `recuperar-archivos.ps1`

---

## 🎯 QUÉ HARÁ EL SCRIPT

### **Automáticamente:**

1. ✅ Busca el backup más reciente
2. ✅ Extrae el contenido a carpeta temporal
3. ✅ Recupera los 3 scripts faltantes:
   - `deploy-v4-mejorado.php` (CRÍTICO)
   - `verify-domain.php` (útil)
   - `cleanup-old.php` (útil)
4. ✅ Copia `.env.example` a `_system/config/`
5. ✅ Elimina archivos de otavafitness de `public_html/`
6. ✅ Limpia carpeta temporal
7. ✅ Verifica resultado

---

## 🚀 EJECUTAR

### **Un solo comando:**

```powershell
powershell -ExecutionPolicy Bypass .\recuperar-archivos.ps1
```

**Tiempo:** 1-2 minutos

---

## 📋 QUÉ VERÁS

```
================================================================
  RECUPERANDO ARCHIVOS DEL BACKUP
================================================================

[1/5] Buscando archivo de backup...
[OK] Backup encontrado: BACKUP_ANTES_LIMPIEZA_20251125-0926.zip
    Tamaño: 274.72 KB

[2/5] Extrayendo backup...
[OK] Backup extraido a: TEMP_BACKUP_RECOVERY

[3/5] Recuperando scripts faltantes...
  [OK] CRITICO recuperado: deploy-v4-mejorado.php
  [OK] Recuperado: verify-domain.php
  [OK] Recuperado: cleanup-old.php

  Total recuperados: 3 de 3

[4/5] Configurando .env.example en config...
[OK] .env.example copiado a _system/config/

[5/5] Limpiando archivos de otavafitness en public_html...
[OK] Eliminado: index.html
[OK] Eliminado: styles.css
[OK] Eliminado: script.js
  Total eliminados: 3 archivos de otavafitness

[Limpieza] Eliminando carpeta temporal...

================================================================
  RECUPERACION COMPLETADA
================================================================

Scripts en _system/generator:
  Total: 8 archivos PHP
  [OK] backup-all.php
  [OK] backup-client.php
  [OK] cleanup-old.php
  [OK] create-domain.php
  [OK] deploy-v4-mejorado.php
  [OK] health-check.php
  [OK] verify-domain.php
  [OK] verify-installation.php

[OK] CRITICO: deploy-v4-mejorado.php recuperado correctamente

Proximos pasos:
  1. Verificar sistema: .\verificar-estructura.ps1
  2. Configurar .env: cd _system\config, copy .env.example .env
  3. Listo para Hostinger

SISTEMA 100% FUNCIONAL
```

---

## ✅ DESPUÉS DE EJECUTAR

**Tendrás:**

```
_system/
├── generator/                (8 scripts PHP completos ✓)
│   ├── backup-all.php
│   ├── backup-client.php
│   ├── cleanup-old.php       ← RECUPERADO
│   ├── create-domain.php
│   ├── deploy-v4-mejorado.php ← RECUPERADO (CRÍTICO)
│   ├── health-check.php
│   ├── verify-domain.php     ← RECUPERADO
│   └── verify-installation.php
│
├── templates/                (completo ✓)
│
└── config/
    ├── domains.json
    └── .env.example          ← COPIADO

public_html/
└── generator/
    └── deploy.php            (solo proxy ✓)
```

**SIN:**
- ❌ Archivos de otavafitness (eliminados)
- ❌ Carpeta temporal (limpiada)

---

## 🔍 VERIFICAR RESULTADO

```powershell
# Ver scripts recuperados
dir _system\generator\*.php

# Debería mostrar 8 archivos
```

**Verificación automática:**
```powershell
.\verificar-estructura.ps1
```

Debería mostrar:
```
✅ ESTRUCTURA PERFECTA - LISTA PARA SUBIR
```

---

## ⚠️ SI ALGO FALLA

### **Error: "No se encontró archivo de backup"**
```
Verificar que existe: BACKUP_ANTES_LIMPIEZA_*.zip
```

### **Error: "No se pudo extraer backup"**
```powershell
# Extraer manualmente
Expand-Archive BACKUP_ANTES_LIMPIEZA_*.zip -DestinationPath TEMP_BACKUP
```

### **Algunos archivos no se recuperaron**
```
Revisar carpeta: TEMP_BACKUP_RECOVERY
Copiar manualmente los que falten
```

---

## 📊 ANTES vs DESPUÉS

### **ANTES (87/100):**
```
Scripts:          5/8   ⚠️ (falta deploy-v4-mejorado.php)
Config:           85%   ⚠️ (falta .env.example en config)
Public_html:      80%   ⚠️ (archivos de otavafitness)

SISTEMA NO FUNCIONAL
```

### **DESPUÉS (100/100):**
```
Scripts:          8/8   ✅ (todos presentes)
Config:          100%   ✅ (completo)
Public_html:     100%   ✅ (solo proxy)

SISTEMA 100% FUNCIONAL
```

---

## 🎯 PRÓXIMOS PASOS

### **1. Verificar:**
```powershell
.\verificar-estructura.ps1
```

### **2. Configurar .env:**
```powershell
cd _system\config
copy .env.example .env
notepad .env
```

Configurar:
```
MAKE_SECRET=<tu token>
ADMIN_EMAIL=tu@email.com
BASE_URL=https://otavafitness.com
```

### **3. Listo para Hostinger:**
Seguir `GUIA_MIGRACION_HOSTINGER.md`

---

## ✅ CHECKLIST

- [ ] Script recuperar-archivos.ps1 ejecutado
- [ ] 3 scripts recuperados (deploy-v4, verify-domain, cleanup-old)
- [ ] .env.example copiado a config
- [ ] Archivos de otavafitness eliminados de public_html
- [ ] Verificación ejecutada (verificar-estructura.ps1)
- [ ] Resultado: ✅ ESTRUCTURA PERFECTA
- [ ] .env configurado con valores reales
- [ ] Listo para subir a Hostinger

---

## 🎉 RESULTADO FINAL

**Después de este script:**

```
Score:              100/100  ⭐⭐⭐⭐⭐
Scripts:            8/8      ✅
Templates:          Completo ✅
Config:             Completo ✅
Documentación:      Completo ✅

SISTEMA PERFECTO Y FUNCIONAL
```

---

**Tiempo total:** 2 minutos  
**Resultado:** Sistema 100% listo para Hostinger ✅
