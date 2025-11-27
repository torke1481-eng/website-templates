# 📤 INSTRUCCIONES DE UPLOAD - PASO A PASO

## 🎯 ARCHIVOS A SUBIR POR FILEZILLA

### **PASO 1: ANTES DE SUBIR - CONFIGURAR DB.PHP**

**[PowerShell LOCAL]** - Editar archivo:

```powershell
notepad "_system\config\db.php"
```

**Cambiar líneas 22-24:**

```php
// ANTES:
define('DB_NAME', 'u253890393_webs');      // ⚠️ CAMBIAR
define('DB_USER', 'u253890393_admin');     // ⚠️ CAMBIAR
define('DB_PASS', 'TU_PASSWORD_AQUI');     // ⚠️ CAMBIAR

// DESPUÉS (tus credenciales reales de cPanel):
define('DB_NAME', 'el_nombre_real_de_tu_database');
define('DB_USER', 'el_usuario_real_que_creaste');
define('DB_PASS', 'el_password_real_que_pusiste');
```

**Guardar y cerrar.**

---

### **PASO 2: CREAR DATABASE EN CPANEL**

**[cPanel]** - Antes de subir archivos:

```
1. Login a cPanel Hostinger
   → https://hpanel.hostinger.com/

2. Buscar "MySQL Databases"

3. CREATE NEW DATABASE:
   - Database Name: webs
   - Click "Create Database"
   - Anota el nombre completo: u253890393_webs

4. CREATE NEW USER:
   - Username: admin
   - Password: [genera uno seguro, guárdalo]
   - Click "Create User"
   - Anota usuario completo: u253890393_admin

5. ADD USER TO DATABASE:
   - User: u253890393_admin
   - Database: u253890393_webs
   - Privileges: ALL PRIVILEGES ✓
   - Click "Make Changes"
```

**✅ Ahora tienes:**
- Database: `u253890393_webs`
- Usuario: `u253890393_admin`
- Password: `[el que generaste]`

---

### **PASO 3: EJECUTAR SQL SCHEMA**

**[cPanel]** - Crear estructura de tablas:

```
1. En cPanel → Buscar "phpMyAdmin"

2. Click para abrir phpMyAdmin

3. En panel izquierdo:
   - Click en "u253890393_webs" (tu database)

4. Click en tab "SQL" arriba

5. [PowerShell LOCAL] - Abrir archivo:
   notepad "_system\config\schema.sql.txt"

6. Copiar TODO el contenido (Ctrl+A, Ctrl+C)

7. Volver a phpMyAdmin:
   - Pegar en el área de texto (Ctrl+V)
   - Click botón "Go" abajo a la derecha

8. Verificar resultado:
   - Debe decir "Query OK" varias veces
   - Panel izquierdo debe mostrar 4 tablas:
     • websites
     • generation_logs
     • analytics
     • approvals
```

**✅ Database lista.**

---

### **PASO 4: SUBIR ARCHIVOS VIA FILEZILLA**

**[FileZilla]** - Conectar y subir:

#### **Archivo 1 de 2:**

```
ARCHIVO LOCAL:
c:\Users\franc\OneDrive\Escritorio\public_html (3)\_system\config\db.php

DESTINO SERVIDOR:
/home/u253890393/domains/otavafitness.com/_system/config/db.php

ACCIÓN:
- Arrastrar archivo desde panel izquierdo a panel derecho
- Si pregunta "File already exists": seleccionar "Overwrite"
```

#### **Archivo 2 de 2:**

```
ARCHIVO LOCAL:
c:\Users\franc\OneDrive\Escritorio\public_html (3)\_system\generator\deploy-v4-mejorado.php

DESTINO SERVIDOR:
/home/u253890393/domains/otavafitness.com/_system/generator/deploy-v4-mejorado.php

ACCIÓN:
- Arrastrar archivo desde panel izquierdo a panel derecho
- Si pregunta "File already exists": seleccionar "Overwrite"
```

**✅ Archivos subidos.**

---

### **PASO 5: VERIFICAR EN SERVIDOR**

**[SSH/PuTTY]** - Conectar y verificar:

```bash
# Verificar que db.php existe
ls -lh /home/u253890393/domains/otavafitness.com/_system/config/db.php

# Verificar que deploy-v4-mejorado.php está actualizado
ls -lh /home/u253890393/domains/otavafitness.com/_system/generator/deploy-v4-mejorado.php

# Test de sintaxis PHP
php -l /home/u253890393/domains/otavafitness.com/_system/config/db.php

# Debe retornar: "No syntax errors detected"
```

**Test de conexión database:**

```bash
php -r "
require '/home/u253890393/domains/otavafitness.com/_system/config/db.php';
\$health = checkDatabaseHealth();
echo json_encode(\$health, JSON_PRETTY_PRINT);
"
```

**Debe mostrar:**
```json
{
    "healthy": true,
    "server_version": "5.7.x",
    "connection_status": "localhost via TCP/IP"
}
```

**✅ Si ves `"healthy": true` → TODO FUNCIONA**

---

## 🎯 RESUMEN RÁPIDO

```
TOTAL ARCHIVOS A SUBIR: 2

1. _system/config/db.php
   └─ (después de configurar credenciales)

2. _system/generator/deploy-v4-mejorado.php
   └─ (tal como está)

TIEMPO TOTAL: 10-15 minutos
```

---

## ⚠️ SI ALGO FALLA

### **Error: "Access denied for user"**

```bash
# Verificar credenciales en db.php
cat /home/u253890393/domains/otavafitness.com/_system/config/db.php | grep "DB_PASS"

# Debe mostrar tu password real, no "TU_PASSWORD_AQUI"
```

**Solución:** Re-editar db.php con password correcto y subir de nuevo.

---

### **Error: "Unknown database"**

```bash
# Ver databases disponibles en cPanel → phpMyAdmin
# Panel izquierdo debe mostrar: u253890393_webs
```

**Solución:** Verificar que creaste la database en cPanel paso 2.

---

### **Error: "Table 'websites' doesn't exist"**

**Solución:** Ejecutar schema.sql.txt en phpMyAdmin (paso 3).

---

## ✅ CHECKLIST FINAL

Antes de continuar con Make.com, verificar:

```
[ ] Database creada en cPanel
[ ] Usuario MySQL creado con privileges
[ ] Schema SQL ejecutado (4 tablas creadas)
[ ] db.php configurado con credenciales reales
[ ] db.php subido a servidor
[ ] deploy-v4-mejorado.php subido a servidor
[ ] Test conexión database exitoso (healthy: true)
[ ] No hay errores de sintaxis PHP
```

**Si todos están ✓ → LISTO PARA CONTINUAR** 🚀

---

## 🎯 SIGUIENTE PASO

Una vez verificado todo:
1. Terminar agente prospector
2. Configurar Make.com
3. Generar primera web de prueba
4. ¡Empezar a vender!
