-- ====================================
-- SISTEMA MULTI-TENANCY
-- Una sola base de datos para todos los clientes
-- ====================================

CREATE DATABASE IF NOT EXISTS sitios_clientes 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE sitios_clientes;

-- ====================================
-- TABLA: sites (TUS CLIENTES)
-- Cada sitio que vendes
-- ====================================

CREATE TABLE sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(100) NOT NULL,
    domain VARCHAR(150) NOT NULL UNIQUE,
    template_type ENUM('landing', 'ecommerce', 'ecommerce-auth') NOT NULL,
    
    -- Información del cliente (dueño del sitio)
    owner_name VARCHAR(100) NOT NULL,
    owner_email VARCHAR(150) NOT NULL,
    owner_phone VARCHAR(50),
    
    -- Configuración
    brand_name VARCHAR(100),
    logo_emoji VARCHAR(10),
    primary_color VARCHAR(7) DEFAULT '#667eea',
    secondary_color VARCHAR(7) DEFAULT '#764ba2',
    
    -- Estado y fechas
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATE,  -- Fecha de vencimiento del pago
    
    -- Configuración JSON (guarda todo el config.json)
    config_json TEXT,
    
    INDEX idx_domain (domain),
    INDEX idx_active (active)
) ENGINE=InnoDB;

-- ====================================
-- TABLA: users (USUARIOS FINALES)
-- Los usuarios que se registran en los sitios de tus clientes
-- ====================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    
    -- Credenciales
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    
    -- Información personal
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    
    -- Estado
    active BOOLEAN DEFAULT 1,
    email_verified BOOLEAN DEFAULT 0,
    
    -- Fechas
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    UNIQUE KEY unique_email_per_site (site_id, email),
    INDEX idx_email (email),
    INDEX idx_site_id (site_id)
) ENGINE=InnoDB;

-- ====================================
-- TABLA: products (PRODUCTOS)
-- Los productos de las tiendas e-commerce
-- ====================================

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    
    -- Información del producto
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(500),
    
    -- Categorización
    category VARCHAR(100),
    badge VARCHAR(50),  -- "Nuevo", "Oferta", etc.
    
    -- Estado
    active BOOLEAN DEFAULT 1,
    stock INT DEFAULT 0,  -- Para futuro: control de inventario
    
    -- Orden de visualización
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_site_id (site_id),
    INDEX idx_category (category),
    INDEX idx_active (active)
) ENGINE=InnoDB;

-- ====================================
-- TABLA: orders (PEDIDOS)
-- Los pedidos realizados en las tiendas
-- ====================================

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    user_id INT NULL,  -- NULL si compró sin registrarse
    
    -- Información del pedido
    order_number VARCHAR(50) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    
    -- Información de contacto (si no hay user_id)
    guest_name VARCHAR(100),
    guest_email VARCHAR(150),
    guest_phone VARCHAR(50),
    
    -- Dirección de envío
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_state VARCHAR(100),
    shipping_zip VARCHAR(20),
    
    -- Notas
    customer_notes TEXT,
    admin_notes TEXT,
    
    -- Fechas
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_order_number (site_id, order_number),
    INDEX idx_site_id (site_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- ====================================
-- TABLA: order_items (ITEMS DE PEDIDOS)
-- Detalle de productos en cada pedido
-- ====================================

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,  -- NULL si el producto fue eliminado
    
    -- Información del producto al momento de la compra
    product_name VARCHAR(200) NOT NULL,
    product_price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10, 2) NOT NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB;

-- ====================================
-- TABLA: sessions (SESIONES)
-- Para manejar sesiones de usuario de forma segura
-- ====================================

CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB;

-- ====================================
-- TABLA: password_resets (RECUPERACIÓN DE CONTRASEÑA)
-- Para el sistema de "olvidé mi contraseña"
-- ====================================

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- ====================================
-- TABLA: admin_users (TU Y TUS EMPLEADOS)
-- Para acceder al panel de administración
-- ====================================

CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'support') DEFAULT 'admin',
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ====================================
-- INSERTAR USUARIO ADMIN INICIAL
-- Usuario: admin / Password: admin123 (CAMBIAR INMEDIATAMENTE)
-- ====================================

INSERT INTO admin_users (username, email, password_hash, role) VALUES 
('admin', 'tu@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');
-- Password: admin123 (hash bcrypt) - CAMBIAR DESPUÉS DEL PRIMER LOGIN

-- ====================================
-- EJEMPLO DE DATOS DE PRUEBA
-- ====================================

-- Cliente de ejemplo
INSERT INTO sites (site_name, domain, template_type, owner_name, owner_email, brand_name, logo_emoji) VALUES 
('Tienda de Prueba', 'prueba.tudominio.com', 'ecommerce-auth', 'Juan Pérez', 'juan@email.com', 'Mi Tienda', '🛍️');

-- Usuario de ejemplo para la tienda
INSERT INTO users (site_id, email, password_hash, name, phone) VALUES 
(1, 'cliente@prueba.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María López', '+54 9 11 1234-5678');

-- Productos de ejemplo
INSERT INTO products (site_id, name, description, price, category, badge) VALUES 
(1, 'Producto 1', 'Descripción del producto 1', 1500.00, 'categoria1', 'Nuevo'),
(1, 'Producto 2', 'Descripción del producto 2', 2500.00, 'categoria1', NULL),
(1, 'Producto 3', 'Descripción del producto 3', 3500.00, 'categoria2', 'Oferta');

-- ====================================
-- VISTAS ÚTILES
-- ====================================

-- Vista: Estadísticas por sitio
CREATE VIEW site_stats AS
SELECT 
    s.id,
    s.site_name,
    s.domain,
    COUNT(DISTINCT u.id) as total_users,
    COUNT(DISTINCT o.id) as total_orders,
    COALESCE(SUM(o.total), 0) as total_revenue,
    COUNT(DISTINCT p.id) as total_products
FROM sites s
LEFT JOIN users u ON s.id = u.site_id
LEFT JOIN orders o ON s.id = o.site_id
LEFT JOIN products p ON s.id = p.site_id
GROUP BY s.id;

-- Vista: Pedidos recientes con detalles
CREATE VIEW recent_orders AS
SELECT 
    o.id,
    o.order_number,
    s.site_name,
    s.domain,
    COALESCE(u.name, o.guest_name) as customer_name,
    COALESCE(u.email, o.guest_email) as customer_email,
    o.total,
    o.status,
    o.created_at
FROM orders o
JOIN sites s ON o.site_id = s.id
LEFT JOIN users u ON o.user_id = u.id
ORDER BY o.created_at DESC;

-- ====================================
-- PROCEDIMIENTO: Limpiar sesiones expiradas
-- ====================================

DELIMITER //
CREATE PROCEDURE cleanup_expired_sessions()
BEGIN
    DELETE FROM sessions WHERE expires_at < NOW();
    DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1;
END //
DELIMITER ;

-- ====================================
-- TABLA: site_admin_tokens
-- Tokens para que los CLIENTES (dueños) gestionen su tienda
-- ====================================

CREATE TABLE site_admin_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    last_used TIMESTAMP NULL,
    
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_site_id (site_id)
) ENGINE=InnoDB;

-- ====================================
-- TABLA: shipping_tracking
-- Seguimiento de envíos
-- ====================================

CREATE TABLE shipping_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    
    -- Información del envío
    carrier VARCHAR(100),  -- "Correo Argentino", "Andreani", "OCA", etc.
    tracking_number VARCHAR(100),
    tracking_url VARCHAR(500),
    
    -- Estado del envío
    status ENUM('pending', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'failed') DEFAULT 'pending',
    
    -- Ubicación actual
    current_location VARCHAR(200),
    
    -- Fechas importantes
    shipped_at TIMESTAMP NULL,
    estimated_delivery TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    
    -- Notas
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_tracking_number (tracking_number),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ====================================
-- TABLA: shipping_events
-- Historial de eventos del envío
-- ====================================

CREATE TABLE shipping_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id INT NOT NULL,
    
    event_date TIMESTAMP NOT NULL,
    location VARCHAR(200),
    description TEXT NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tracking_id) REFERENCES shipping_tracking(id) ON DELETE CASCADE,
    INDEX idx_tracking_id (tracking_id),
    INDEX idx_event_date (event_date)
) ENGINE=InnoDB;

-- ====================================
-- PROCEDIMIENTO: Generar token de admin para cliente
-- ====================================

DELIMITER //
CREATE PROCEDURE generate_admin_token(IN p_site_id INT, OUT p_token VARCHAR(255))
BEGIN
    SET p_token = SHA2(CONCAT(p_site_id, UNIX_TIMESTAMP(), RAND()), 256);
    
    INSERT INTO site_admin_tokens (site_id, token, expires_at)
    VALUES (p_site_id, p_token, DATE_ADD(NOW(), INTERVAL 1 YEAR));
END //
DELIMITER ;

-- ====================================
-- GENERAR TOKENS PARA SITIOS EXISTENTES
-- ====================================

-- Esto se ejecutará solo si ya hay sitios creados
INSERT INTO site_admin_tokens (site_id, token, expires_at)
SELECT id, SHA2(CONCAT(id, UNIX_TIMESTAMP(), RAND()), 256), DATE_ADD(NOW(), INTERVAL 1 YEAR)
FROM sites
WHERE id NOT IN (SELECT site_id FROM site_admin_tokens);

-- ====================================
-- EVENTO: Limpieza automática diaria
-- ====================================

SET GLOBAL event_scheduler = ON;

CREATE EVENT daily_cleanup
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO CALL cleanup_expired_sessions();
