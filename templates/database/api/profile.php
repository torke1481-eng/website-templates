<?php
/* ====================================
   API DE PERFIL DE USUARIO
   Endpoints: get, update
   ==================================== */

require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? $_GET['token'] ?? null;

// Verificar token
if (!$token) {
    sendError('Token requerido', 401);
}

$user = getUserByToken($token);

if (!$user) {
    sendError('Sesión inválida o expirada', 401);
}

// Router
switch ($method) {
    case 'GET':
        getProfile($user);
        break;
        
    case 'PUT':
    case 'POST':
        updateProfile($user, $input);
        break;
        
    default:
        sendError('Método no permitido', 405);
}

/* ====================================
   FUNCIÓN: Obtener usuario por token
   ==================================== */
function getUserByToken($token) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT u.* 
            FROM users u
            JOIN sessions s ON u.id = s.user_id
            WHERE s.token = ? AND s.expires_at > NOW() AND u.active = 1
        ");
        $stmt->execute([$token]);
        
        return $stmt->fetch();
        
    } catch (Exception $e) {
        error_log("getUserByToken error: " . $e->getMessage());
        return null;
    }
}

/* ====================================
   FUNCIÓN: Obtener perfil
   ==================================== */
function getProfile($user) {
    sendSuccess([
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'address' => $user['address'],
        'created_at' => $user['created_at'],
        'last_login' => $user['last_login']
    ]);
}

/* ====================================
   FUNCIÓN: Actualizar perfil
   ==================================== */
function updateProfile($user, $data) {
    $name = sanitizeInput($data['name'] ?? $user['name']);
    $phone = sanitizeInput($data['phone'] ?? $user['phone']);
    $address = sanitizeInput($data['address'] ?? $user['address']);
    
    if (empty($name)) {
        sendError('El nombre es requerido', 400);
    }
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            UPDATE users 
            SET name = ?, phone = ?, address = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$name, $phone, $address, $user['id']]);
        
        sendSuccess([
            'id' => $user['id'],
            'name' => $name,
            'email' => $user['email'],
            'phone' => $phone,
            'address' => $address
        ], 'Perfil actualizado correctamente');
        
    } catch (Exception $e) {
        error_log("Update profile error: " . $e->getMessage());
        sendError('Error al actualizar perfil', 500);
    }
}
