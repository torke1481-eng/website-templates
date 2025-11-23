<?php
/* ====================================
   API DE ENVÍOS (SHIPPING)
   Endpoints: create, list, update, add_event
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

// Verificar token de admin
$admin_token = $input['admin_token'] ?? $_GET['admin_token'] ?? null;
if (!verifyAdminAccess($site_id, $admin_token)) {
    sendError('Acceso denegado', 403);
}

// Router
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getShipment($site_id, $_GET['id']);
        } else {
            listShipments($site_id);
        }
        break;
        
    case 'POST':
        $action = $input['action'] ?? 'create';
        if ($action === 'add_event') {
            addShippingEvent($input);
        } else {
            createShipment($site_id, $input);
        }
        break;
        
    case 'PUT':
        if (!isset($_GET['id'])) {
            sendError('ID de envío requerido', 400);
        }
        updateShipment($_GET['id'], $input);
        break;
        
    default:
        sendError('Método no permitido', 405);
}

/* ====================================
   FUNCIÓN: Crear seguimiento de envío
   ==================================== */
function createShipment($site_id, $data) {
    $order_id = intval($data['order_id'] ?? 0);
    $carrier = sanitizeInput($data['carrier'] ?? '');
    $tracking_number = sanitizeInput($data['tracking_number'] ?? '');
    $tracking_url = sanitizeInput($data['tracking_url'] ?? '');
    $estimated_delivery = $data['estimated_delivery'] ?? null;
    $notes = sanitizeInput($data['notes'] ?? '');
    
    // Validaciones
    if (!$order_id || !$carrier || !$tracking_number) {
        sendError('Datos incompletos', 400);
    }
    
    try {
        $db = getDB();
        
        // Verificar que el pedido existe y pertenece al sitio
        $stmt = $db->prepare("SELECT id, user_id, guest_email FROM orders WHERE id = ? AND site_id = ?");
        $stmt->execute([$order_id, $site_id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            sendError('Pedido no encontrado', 404);
        }
        
        $db->beginTransaction();
        
        // Crear registro de tracking
        $stmt = $db->prepare("
            INSERT INTO shipping_tracking (
                order_id, carrier, tracking_number, tracking_url,
                status, estimated_delivery, notes, shipped_at
            ) VALUES (?, ?, ?, ?, 'in_transit', ?, ?, NOW())
        ");
        
        $stmt->execute([
            $order_id,
            $carrier,
            $tracking_number,
            $tracking_url,
            $estimated_delivery,
            $notes
        ]);
        
        $tracking_id = $db->lastInsertId();
        
        // Actualizar estado del pedido
        $stmt = $db->prepare("UPDATE orders SET status = 'shipped' WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // Agregar primer evento
        $stmt = $db->prepare("
            INSERT INTO shipping_events (tracking_id, event_date, description, location)
            VALUES (?, NOW(), 'Paquete despachado', ?)
        ");
        $stmt->execute([$tracking_id, $carrier]);
        
        $db->commit();
        
        // Enviar notificación por email al cliente
        sendShippingNotification($order, $carrier, $tracking_number, $tracking_url);
        
        sendSuccess([
            'tracking_id' => $tracking_id,
            'message' => 'Envío registrado y cliente notificado'
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Create shipment error: " . $e->getMessage());
        sendError('Error al crear seguimiento de envío', 500);
    }
}

/* ====================================
   FUNCIÓN: Listar envíos
   ==================================== */
function listShipments($site_id) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT 
                st.*,
                o.order_number,
                COALESCE(u.name, o.guest_name) as customer_name,
                COALESCE(u.email, o.guest_email) as customer_email
            FROM shipping_tracking st
            JOIN orders o ON st.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.site_id = ?
            ORDER BY st.created_at DESC
        ");
        $stmt->execute([$site_id]);
        $shipments = $stmt->fetchAll();
        
        // Obtener eventos para cada envío
        $stmt_events = $db->prepare("
            SELECT * FROM shipping_events 
            WHERE tracking_id = ? 
            ORDER BY event_date DESC
        ");
        
        foreach ($shipments as &$shipment) {
            $stmt_events->execute([$shipment['id']]);
            $shipment['events'] = $stmt_events->fetchAll();
        }
        
        sendSuccess(['shipments' => $shipments]);
        
    } catch (Exception $e) {
        error_log("List shipments error: " . $e->getMessage());
        sendError('Error al obtener envíos', 500);
    }
}

/* ====================================
   FUNCIÓN: Obtener un envío específico
   ==================================== */
function getShipment($site_id, $tracking_id) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT 
                st.*,
                o.order_number,
                o.site_id,
                COALESCE(u.name, o.guest_name) as customer_name,
                COALESCE(u.email, o.guest_email) as customer_email
            FROM shipping_tracking st
            JOIN orders o ON st.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            WHERE st.id = ?
        ");
        $stmt->execute([$tracking_id]);
        $shipment = $stmt->fetch();
        
        if (!$shipment || $shipment['site_id'] != $site_id) {
            sendError('Envío no encontrado', 404);
        }
        
        // Obtener eventos
        $stmt = $db->prepare("
            SELECT * FROM shipping_events 
            WHERE tracking_id = ? 
            ORDER BY event_date DESC
        ");
        $stmt->execute([$tracking_id]);
        $shipment['events'] = $stmt->fetchAll();
        
        sendSuccess(['shipment' => $shipment]);
        
    } catch (Exception $e) {
        error_log("Get shipment error: " . $e->getMessage());
        sendError('Error al obtener envío', 500);
    }
}

/* ====================================
   FUNCIÓN: Actualizar estado de envío
   ==================================== */
function updateShipment($tracking_id, $data) {
    try {
        $db = getDB();
        
        $fields = [];
        $values = [];
        
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $values[] = $data['status'];
            
            // Si se marca como entregado, guardar fecha
            if ($data['status'] === 'delivered') {
                $fields[] = "delivered_at = NOW()";
            }
        }
        
        if (isset($data['current_location'])) {
            $fields[] = "current_location = ?";
            $values[] = sanitizeInput($data['current_location']);
        }
        
        if (isset($data['notes'])) {
            $fields[] = "notes = ?";
            $values[] = sanitizeInput($data['notes']);
        }
        
        if (empty($fields)) {
            sendError('No hay campos para actualizar', 400);
        }
        
        $fields[] = "updated_at = NOW()";
        $values[] = $tracking_id;
        
        $sql = "UPDATE shipping_tracking SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        sendSuccess([], 'Envío actualizado');
        
    } catch (Exception $e) {
        error_log("Update shipment error: " . $e->getMessage());
        sendError('Error al actualizar envío', 500);
    }
}

/* ====================================
   FUNCIÓN: Agregar evento al timeline
   ==================================== */
function addShippingEvent($data) {
    $tracking_id = intval($data['tracking_id'] ?? 0);
    $description = sanitizeInput($data['description'] ?? '');
    $location = sanitizeInput($data['location'] ?? '');
    $event_date = $data['event_date'] ?? date('Y-m-d H:i:s');
    
    if (!$tracking_id || !$description) {
        sendError('Datos incompletos', 400);
    }
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO shipping_events (tracking_id, event_date, location, description)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$tracking_id, $event_date, $location, $description]);
        
        sendSuccess([], 'Evento agregado');
        
    } catch (Exception $e) {
        error_log("Add shipping event error: " . $e->getMessage());
        sendError('Error al agregar evento', 500);
    }
}

/* ====================================
   FUNCIÓN: Enviar notificación de envío
   ==================================== */
function sendShippingNotification($order, $carrier, $tracking_number, $tracking_url) {
    $email = $order['guest_email'];
    if (!$email) return;
    
    $subject = "¡Tu pedido está en camino! 📦";
    
    $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f7fafc; padding: 30px; border-radius: 0 0 10px 10px; }
                .tracking-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #718096; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📦 Tu pedido está en camino</h1>
                </div>
                <div class='content'>
                    <p>¡Hola!</p>
                    <p>Te informamos que tu pedido ha sido despachado y está en camino.</p>
                    
                    <div class='tracking-box'>
                        <p><strong>Empresa de envío:</strong> {$carrier}</p>
                        <p><strong>Número de seguimiento:</strong> {$tracking_number}</p>
                    </div>
                    
                    " . ($tracking_url ? "<a href='{$tracking_url}' class='button'>🔍 Rastrear mi envío</a>" : "") . "
                    
                    <p>Recibirás tu pedido en los próximos días. Si tienes alguna consulta, no dudes en contactarnos.</p>
                    
                    <div class='footer'>
                        <p>Gracias por tu compra</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
    
    // Enviar email
    @mail($email, $subject, $message, $headers);
}

/* ====================================
   FUNCIÓN: Verificar acceso de administrador
   ==================================== */
function verifyAdminAccess($site_id, $admin_token) {
    if (!$admin_token) {
        return false;
    }
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT s.id 
            FROM sites s
            JOIN site_admin_tokens sat ON s.id = sat.site_id
            WHERE s.id = ? AND sat.token = ? AND sat.expires_at > NOW()
        ");
        $stmt->execute([$site_id, $admin_token]);
        
        return $stmt->fetch() !== false;
        
    } catch (Exception $e) {
        error_log("Verify admin access error: " . $e->getMessage());
        return false;
    }
}
