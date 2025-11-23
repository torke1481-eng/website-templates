<?php
/* ====================================
   CONFIGURACIÓN DE BASE DE DATOS
   Conexión centralizada para todos los sitios
   ==================================== */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sitios_clientes');
define('DB_USER', 'tu_usuario_mysql');
define('DB_PASS', 'tu_password_mysql');
define('DB_CHARSET', 'utf8mb4');

// Configuración de seguridad
define('JWT_SECRET', 'tu_clave_secreta_super_segura_cambiar_esto'); // CAMBIAR en producción
define('PASSWORD_SALT', 'otro_salt_seguro_cambiar'); // CAMBIAR en producción

// Configuración del sistema
define('SESSION_DURATION', 30 * 24 * 60 * 60); // 30 días en segundos
define('PASSWORD_RESET_DURATION', 60 * 60); // 1 hora en segundos

// Clase de conexión PDO
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir deserialización
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Función helper para obtener la conexión
function getDB() {
    return Database::getInstance()->getConnection();
}

// Función para obtener el site_id basado en el dominio
function getSiteIdByDomain($domain) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM sites WHERE domain = ? AND active = 1");
    $stmt->execute([$domain]);
    $result = $stmt->fetch();
    
    return $result ? $result['id'] : null;
}

// Función para hashear contraseñas de forma segura
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Función para verificar contraseñas
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Función para generar token seguro
function generateSecureToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

// Función para sanitizar input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para enviar respuesta JSON
function sendJSON($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para manejar errores
function sendError($message, $status = 400) {
    sendJSON([
        'success' => false,
        'error' => $message
    ], $status);
}

// Función para manejar éxito
function sendSuccess($data = [], $message = 'Operación exitosa') {
    sendJSON([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], 200);
}

// Headers CORS (ajustar según necesites)
header('Access-Control-Allow-Origin: *'); // En producción, especificar dominios
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
