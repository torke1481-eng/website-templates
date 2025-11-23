<?php
/* ====================================
   API DE PRODUCTOS
   Para que LOS CLIENTES gestionen su inventario
   Endpoints: list, create, update, delete
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
    case 'GET':
        if (isset($_GET['id'])) {
            getProduct($site_id, $_GET['id']);
        } else {
            listProducts($site_id, $_GET);
        }
        break;
        
    case 'POST':
        createProduct($site_id, $input);
        break;
        
    case 'PUT':
        if (!isset($_GET['id'])) {
            sendError('ID de producto requerido', 400);
        }
        updateProduct($site_id, $_GET['id'], $input);
        break;
        
    case 'DELETE':
        if (!isset($_GET['id'])) {
            sendError('ID de producto requerido', 400);
        }
        deleteProduct($site_id, $_GET['id'], $input);
        break;
        
    default:
        sendError('Método no permitido', 405);
}

/* ====================================
   FUNCIÓN: Listar productos
   ==================================== */
function listProducts($site_id, $params) {
    $category = $params['category'] ?? null;
    $active_only = isset($params['active']) ? (bool)$params['active'] : true;
    
    try {
        $db = getDB();
        
        $sql = "SELECT * FROM products WHERE site_id = ?";
        $params_array = [$site_id];
        
        if ($category) {
            $sql .= " AND category = ?";
            $params_array[] = $category;
        }
        
        if ($active_only) {
            $sql .= " AND active = 1";
        }
        
        $sql .= " ORDER BY sort_order ASC, created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params_array);
        $products = $stmt->fetchAll();
        
        sendSuccess(['products' => $products]);
        
    } catch (Exception $e) {
        error_log("List products error: " . $e->getMessage());
        sendError('Error al obtener productos', 500);
    }
}

/* ====================================
   FUNCIÓN: Obtener un producto
   ==================================== */
function getProduct($site_id, $product_id) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND site_id = ?");
        $stmt->execute([$product_id, $site_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            sendError('Producto no encontrado', 404);
        }
        
        sendSuccess(['product' => $product]);
        
    } catch (Exception $e) {
        error_log("Get product error: " . $e->getMessage());
        sendError('Error al obtener producto', 500);
    }
}

/* ====================================
   FUNCIÓN: Crear producto (ADMIN)
   ==================================== */
function createProduct($site_id, $data) {
    // Verificar que es el dueño del sitio (admin_token)
    $admin_token = $data['admin_token'] ?? null;
    if (!verifyAdminAccess($site_id, $admin_token)) {
        sendError('Acceso denegado', 403);
    }
    
    $name = sanitizeInput($data['name'] ?? '');
    $description = sanitizeInput($data['description'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $image_url = sanitizeInput($data['image_url'] ?? '');
    $category = sanitizeInput($data['category'] ?? '');
    $badge = sanitizeInput($data['badge'] ?? '');
    $stock = intval($data['stock'] ?? 0);
    $sort_order = intval($data['sort_order'] ?? 0);
    
    // Validaciones
    if (empty($name)) {
        sendError('El nombre del producto es requerido', 400);
    }
    
    if ($price < 0) {
        sendError('El precio debe ser mayor o igual a 0', 400);
    }
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO products (
                site_id, name, description, price, image_url, 
                category, badge, stock, sort_order, active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([
            $site_id,
            $name,
            $description,
            $price,
            $image_url,
            $category,
            $badge,
            $stock,
            $sort_order
        ]);
        
        $product_id = $db->lastInsertId();
        
        sendSuccess([
            'product_id' => $product_id,
            'message' => 'Producto creado exitosamente'
        ]);
        
    } catch (Exception $e) {
        error_log("Create product error: " . $e->getMessage());
        sendError('Error al crear producto', 500);
    }
}

/* ====================================
   FUNCIÓN: Actualizar producto (ADMIN)
   ==================================== */
function updateProduct($site_id, $product_id, $data) {
    // Verificar acceso de administrador
    $admin_token = $data['admin_token'] ?? null;
    if (!verifyAdminAccess($site_id, $admin_token)) {
        sendError('Acceso denegado', 403);
    }
    
    try {
        $db = getDB();
        
        // Verificar que el producto existe y pertenece al sitio
        $stmt = $db->prepare("SELECT id FROM products WHERE id = ? AND site_id = ?");
        $stmt->execute([$product_id, $site_id]);
        if (!$stmt->fetch()) {
            sendError('Producto no encontrado', 404);
        }
        
        // Construir query dinámicamente según campos presentes
        $fields = [];
        $values = [];
        
        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $values[] = sanitizeInput($data['name']);
        }
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $values[] = sanitizeInput($data['description']);
        }
        if (isset($data['price'])) {
            $fields[] = "price = ?";
            $values[] = floatval($data['price']);
        }
        if (isset($data['image_url'])) {
            $fields[] = "image_url = ?";
            $values[] = sanitizeInput($data['image_url']);
        }
        if (isset($data['category'])) {
            $fields[] = "category = ?";
            $values[] = sanitizeInput($data['category']);
        }
        if (isset($data['badge'])) {
            $fields[] = "badge = ?";
            $values[] = sanitizeInput($data['badge']);
        }
        if (isset($data['stock'])) {
            $fields[] = "stock = ?";
            $values[] = intval($data['stock']);
        }
        if (isset($data['sort_order'])) {
            $fields[] = "sort_order = ?";
            $values[] = intval($data['sort_order']);
        }
        if (isset($data['active'])) {
            $fields[] = "active = ?";
            $values[] = (bool)$data['active'] ? 1 : 0;
        }
        
        if (empty($fields)) {
            sendError('No hay campos para actualizar', 400);
        }
        
        $fields[] = "updated_at = NOW()";
        $values[] = $product_id;
        $values[] = $site_id;
        
        $sql = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = ? AND site_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        sendSuccess([], 'Producto actualizado exitosamente');
        
    } catch (Exception $e) {
        error_log("Update product error: " . $e->getMessage());
        sendError('Error al actualizar producto', 500);
    }
}

/* ====================================
   FUNCIÓN: Eliminar producto (ADMIN)
   ==================================== */
function deleteProduct($site_id, $product_id, $data) {
    // Verificar acceso de administrador
    $admin_token = $data['admin_token'] ?? $_GET['admin_token'] ?? null;
    if (!verifyAdminAccess($site_id, $admin_token)) {
        sendError('Acceso denegado', 403);
    }
    
    try {
        $db = getDB();
        
        // Soft delete (marcar como inactivo en lugar de eliminar)
        $stmt = $db->prepare("
            UPDATE products 
            SET active = 0, updated_at = NOW() 
            WHERE id = ? AND site_id = ?
        ");
        $stmt->execute([$product_id, $site_id]);
        
        if ($stmt->rowCount() === 0) {
            sendError('Producto no encontrado', 404);
        }
        
        sendSuccess([], 'Producto eliminado exitosamente');
        
    } catch (Exception $e) {
        error_log("Delete product error: " . $e->getMessage());
        sendError('Error al eliminar producto', 500);
    }
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
        
        // Verificar que el token pertenece al dueño del sitio
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
