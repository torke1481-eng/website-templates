<?php
/* ====================================
   API DE PEDIDOS
   Endpoints: create, list, get
   ==================================== */

require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Obtener dominio y site_id
$domain = $_GET['domain'] ?? $_SERVER['HTTP_HOST'] ?? null;
if (!$domain) {
    sendError('Dominio no especificado', 400);
}

$site_id = getSiteIdByDomain($domain);
if (!$site_id) {
    sendError('Sitio no encontrado', 404);
}

// Router
switch ($method) {
    case 'POST':
        createOrder($site_id, $input);
        break;
        
    case 'GET':
        if (isset($_GET['id'])) {
            getOrder($site_id, $_GET['id'], $_GET['token'] ?? null);
        } else {
            listOrders($site_id, $_GET['token'] ?? null);
        }
        break;
        
    default:
        sendError('Método no permitido', 405);
}

/* ====================================
   FUNCIÓN: Crear pedido
   ==================================== */
function createOrder($site_id, $data) {
    $token = $data['token'] ?? null;
    $items = $data['items'] ?? [];
    $total = floatval($data['total'] ?? 0);
    
    // Información de contacto (si no hay token)
    $guest_name = sanitizeInput($data['guest_name'] ?? '');
    $guest_email = sanitizeInput($data['guest_email'] ?? '');
    $guest_phone = sanitizeInput($data['guest_phone'] ?? '');
    
    // Dirección
    $shipping_address = sanitizeInput($data['shipping_address'] ?? '');
    $shipping_city = sanitizeInput($data['shipping_city'] ?? '');
    $shipping_state = sanitizeInput($data['shipping_state'] ?? '');
    $shipping_zip = sanitizeInput($data['shipping_zip'] ?? '');
    
    $customer_notes = sanitizeInput($data['customer_notes'] ?? '');
    
    // Validaciones
    if (empty($items) || count($items) === 0) {
        sendError('El pedido debe tener al menos un producto', 400);
    }
    
    if ($total <= 0) {
        sendError('Total inválido', 400);
    }
    
    // Obtener user_id si hay token
    $user_id = null;
    if ($token) {
        $user = getUserByToken($token);
        if ($user) {
            $user_id = $user['id'];
            // Si hay usuario logueado, usar su información
            if (empty($guest_name) && !empty($user['name'])) {
                $guest_name = $user['name'];
            }
            if (empty($guest_email) && !empty($user['email'])) {
                $guest_email = $user['email'];
            }
            if (empty($guest_phone) && !empty($user['phone'])) {
                $guest_phone = $user['phone'];
            }
        }
    }
    
    // Validar información de contacto
    if (empty($guest_name) || empty($guest_email)) {
        sendError('Nombre y email son requeridos', 400);
    }
    
    try {
        $db = getDB();
        $db->beginTransaction();
        
        // Generar número de orden único
        $order_number = generateOrderNumber($site_id);
        
        // Insertar orden
        $stmt = $db->prepare("
            INSERT INTO orders (
                site_id, user_id, order_number, total, status,
                guest_name, guest_email, guest_phone,
                shipping_address, shipping_city, shipping_state, shipping_zip,
                customer_notes, created_at
            ) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $site_id,
            $user_id,
            $order_number,
            $total,
            $guest_name,
            $guest_email,
            $guest_phone,
            $shipping_address,
            $shipping_city,
            $shipping_state,
            $shipping_zip,
            $customer_notes
        ]);
        
        $order_id = $db->lastInsertId();
        
        // Insertar items del pedido
        $stmt = $db->prepare("
            INSERT INTO order_items (
                order_id, product_id, product_name, product_price, quantity, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($items as $item) {
            $product_id = $item['id'] ?? null;
            $product_name = sanitizeInput($item['name'] ?? '');
            $product_price = floatval($item['price'] ?? 0);
            $quantity = intval($item['quantity'] ?? 1);
            $subtotal = $product_price * $quantity;
            
            $stmt->execute([
                $order_id,
                $product_id,
                $product_name,
                $product_price,
                $quantity,
                $subtotal
            ]);
        }
        
        $db->commit();
        
        sendSuccess([
            'order_id' => $order_id,
            'order_number' => $order_number,
            'total' => $total,
            'status' => 'pending'
        ], 'Pedido creado exitosamente');
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Create order error: " . $e->getMessage());
        sendError('Error al crear pedido', 500);
    }
}

/* ====================================
   FUNCIÓN: Listar pedidos del usuario
   ==================================== */
function listOrders($site_id, $token) {
    if (!$token) {
        sendError('Token requerido', 401);
    }
    
    $user = getUserByToken($token);
    if (!$user) {
        sendError('Sesión inválida', 401);
    }
    
    try {
        $db = getDB();
        
        // Obtener pedidos del usuario
        $stmt = $db->prepare("
            SELECT 
                id, order_number, total, status, 
                shipping_address, customer_notes,
                created_at, updated_at
            FROM orders 
            WHERE site_id = ? AND user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$site_id, $user['id']]);
        $orders = $stmt->fetchAll();
        
        // Para cada pedido, obtener sus items
        $stmt_items = $db->prepare("
            SELECT product_name, product_price, quantity, subtotal
            FROM order_items
            WHERE order_id = ?
        ");
        
        foreach ($orders as &$order) {
            $stmt_items->execute([$order['id']]);
            $order['items'] = $stmt_items->fetchAll();
        }
        
        sendSuccess(['orders' => $orders]);
        
    } catch (Exception $e) {
        error_log("List orders error: " . $e->getMessage());
        sendError('Error al obtener pedidos', 500);
    }
}

/* ====================================
   FUNCIÓN: Obtener un pedido específico
   ==================================== */
function getOrder($site_id, $order_id, $token) {
    if (!$token) {
        sendError('Token requerido', 401);
    }
    
    $user = getUserByToken($token);
    if (!$user) {
        sendError('Sesión inválida', 401);
    }
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT * FROM orders 
            WHERE id = ? AND site_id = ? AND user_id = ?
        ");
        $stmt->execute([$order_id, $site_id, $user['id']]);
        $order = $stmt->fetch();
        
        if (!$order) {
            sendError('Pedido no encontrado', 404);
        }
        
        // Obtener items
        $stmt = $db->prepare("
            SELECT * FROM order_items WHERE order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order['items'] = $stmt->fetchAll();
        
        sendSuccess(['order' => $order]);
        
    } catch (Exception $e) {
        error_log("Get order error: " . $e->getMessage());
        sendError('Error al obtener pedido', 500);
    }
}

/* ====================================
   FUNCIÓN: Generar número de orden único
   ==================================== */
function generateOrderNumber($site_id) {
    return strtoupper($site_id . '-' . date('Ymd') . '-' . substr(md5(uniqid(rand(), true)), 0, 6));
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
        return null;
    }
}
