<?php
/* ====================================
   API DE AUTENTICACIÓN
   Endpoints: register, login, logout, verify
   ==================================== */

require_once '../config.php';

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener el dominio desde el header o parámetro
$domain = $_GET['domain'] ?? $_SERVER['HTTP_HOST'] ?? null;

if (!$domain) {
    sendError('Dominio no especificado', 400);
}

// Obtener el site_id
$site_id = getSiteIdByDomain($domain);

if (!$site_id) {
    sendError('Sitio no encontrado o inactivo', 404);
}

// Obtener datos del body
$input = json_decode(file_get_contents('php://input'), true);

// Router
switch ($method) {
    case 'POST':
        $action = $input['action'] ?? $_GET['action'] ?? null;
        
        switch ($action) {
            case 'register':
                register($site_id, $input);
                break;
            case 'login':
                login($site_id, $input);
                break;
            case 'logout':
                logout($input);
                break;
            case 'verify':
                verifySession($input);
                break;
            default:
                sendError('Acción no válida', 400);
        }
        break;
        
    default:
        sendError('Método no permitido', 405);
}

/* ====================================
   FUNCIÓN: REGISTRO
   ==================================== */
function register($site_id, $data) {
    $name = sanitizeInput($data['name'] ?? '');
    $email = sanitizeInput($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    // Validaciones
    if (empty($name) || empty($email) || empty($password)) {
        sendError('Todos los campos son requeridos', 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError('Email inválido', 400);
    }
    
    if (strlen($password) < 6) {
        sendError('La contraseña debe tener al menos 6 caracteres', 400);
    }
    
    try {
        $db = getDB();
        
        // Verificar si el email ya existe en este sitio
        $stmt = $db->prepare("SELECT id FROM users WHERE site_id = ? AND email = ?");
        $stmt->execute([$site_id, $email]);
        
        if ($stmt->fetch()) {
            sendError('Este email ya está registrado', 409);
        }
        
        // Hash de la contraseña
        $password_hash = hashPassword($password);
        
        // Insertar usuario
        $stmt = $db->prepare("
            INSERT INTO users (site_id, email, password_hash, name, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$site_id, $email, $password_hash, $name]);
        
        $user_id = $db->lastInsertId();
        
        // Crear sesión
        $token = generateSecureToken();
        $expires_at = date('Y-m-d H:i:s', time() + SESSION_DURATION);
        
        $stmt = $db->prepare("
            INSERT INTO sessions (user_id, token, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $token,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $expires_at
        ]);
        
        // Actualizar last_login
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user_id]);
        
        sendSuccess([
            'user' => [
                'id' => $user_id,
                'name' => $name,
                'email' => $email
            ],
            'token' => $token,
            'expires_at' => $expires_at
        ], 'Usuario registrado exitosamente');
        
    } catch (Exception $e) {
        error_log("Register error: " . $e->getMessage());
        sendError('Error al registrar usuario', 500);
    }
}

/* ====================================
   FUNCIÓN: LOGIN
   ==================================== */
function login($site_id, $data) {
    $email = sanitizeInput($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        sendError('Email y contraseña son requeridos', 400);
    }
    
    try {
        $db = getDB();
        
        // Buscar usuario
        $stmt = $db->prepare("
            SELECT id, email, password_hash, name, phone, address, active 
            FROM users 
            WHERE site_id = ? AND email = ?
        ");
        $stmt->execute([$site_id, $email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            sendError('Email o contraseña incorrectos', 401);
        }
        
        if (!$user['active']) {
            sendError('Usuario desactivado', 403);
        }
        
        // Verificar contraseña
        if (!verifyPassword($password, $user['password_hash'])) {
            sendError('Email o contraseña incorrectos', 401);
        }
        
        // Crear sesión
        $token = generateSecureToken();
        $expires_at = date('Y-m-d H:i:s', time() + SESSION_DURATION);
        
        $stmt = $db->prepare("
            INSERT INTO sessions (user_id, token, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user['id'],
            $token,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $expires_at
        ]);
        
        // Actualizar last_login
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        sendSuccess([
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'address' => $user['address']
            ],
            'token' => $token,
            'expires_at' => $expires_at
        ], 'Login exitoso');
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        sendError('Error al iniciar sesión', 500);
    }
}

/* ====================================
   FUNCIÓN: LOGOUT
   ==================================== */
function logout($data) {
    $token = $data['token'] ?? '';
    
    if (empty($token)) {
        sendError('Token requerido', 400);
    }
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("DELETE FROM sessions WHERE token = ?");
        $stmt->execute([$token]);
        
        sendSuccess([], 'Sesión cerrada correctamente');
        
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
        sendError('Error al cerrar sesión', 500);
    }
}

/* ====================================
   FUNCIÓN: VERIFICAR SESIÓN
   ==================================== */
function verifySession($data) {
    $token = $data['token'] ?? '';
    
    if (empty($token)) {
        sendError('Token requerido', 400);
    }
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT s.*, u.id as user_id, u.name, u.email, u.phone, u.address, u.active
            FROM sessions s
            JOIN users u ON s.user_id = u.id
            WHERE s.token = ? AND s.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $session = $stmt->fetch();
        
        if (!$session) {
            sendError('Sesión inválida o expirada', 401);
        }
        
        if (!$session['active']) {
            sendError('Usuario desactivado', 403);
        }
        
        sendSuccess([
            'user' => [
                'id' => $session['user_id'],
                'name' => $session['name'],
                'email' => $session['email'],
                'phone' => $session['phone'],
                'address' => $session['address']
            ]
        ], 'Sesión válida');
        
    } catch (Exception $e) {
        error_log("Verify session error: " . $e->getMessage());
        sendError('Error al verificar sesión', 500);
    }
}
