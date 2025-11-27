<?php
/**
 * DATABASE CONNECTION - Reemplazo de JSON files
 * 
 * IMPORTANTE: Ejecutar init-database.sql primero
 */

// ============================================
// CONFIGURACIÓN DATABASE
// ============================================
// IMPORTANTE: Antes de subir este archivo, configurar credenciales:
//
// 1. Ir a cPanel → MySQL Databases
// 2. Crear database: u253890393_webs (o el nombre que prefieras)
// 3. Crear usuario: u253890393_admin (o el nombre que prefieras)
// 4. Asignar password seguro
// 5. Dar ALL PRIVILEGES al usuario en la database
// 6. Reemplazar valores abajo con tus credenciales reales
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'u253890393_webs');      // ⚠️ CAMBIAR: Nombre de tu database
define('DB_USER', 'u253890393_admin');     // ⚠️ CAMBIAR: Usuario MySQL
define('DB_PASS', '5893674120Fr.');     // ⚠️ CAMBIAR: Password del usuario
define('DB_CHARSET', 'utf8mb4');

// Singleton Database Connection
class Database {
    private static $instance = null;
    private $pdo = null;
    private $lastError = null;
    
    private function __construct() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false, // Shared hosting puede limitar conexiones
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log('Database connection failed: ' . $e->getMessage());
            
            // En producción, no exponer detalles
            if ($_SERVER['SERVER_NAME'] !== 'localhost') {
                throw new Exception('Error de base de datos. Contacta soporte.');
            }
            throw $e;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function getLastError() {
        return $this->lastError;
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir unserialize
    public function __wakeup() {
        throw new Exception('Cannot unserialize singleton');
    }
}

/**
 * Helper functions para queries comunes
 */

// Obtener conexión
function getDB() {
    return Database::getInstance()->getConnection();
}

// Insertar website con manejo de errores
function insertWebsite($data) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO websites (
                domain, 
                business_name, 
                template, 
                status, 
                config,
                created_at
            ) VALUES (
                :domain,
                :business_name,
                :template,
                :status,
                :config,
                NOW()
            )
        ");
        
        $stmt->execute([
            ':domain' => $data['domain'],
            ':business_name' => $data['business_name'],
            ':template' => $data['template'] ?? 'landing-pro',
            ':status' => $data['status'] ?? 'generating',
            ':config' => json_encode($data['config'] ?? [])
        ]);
        
        return $db->lastInsertId();
        
    } catch (PDOException $e) {
        error_log('Error insertando website: ' . $e->getMessage());
        
        // Si es duplicate key
        if ($e->getCode() == 23000) {
            throw new Exception('El dominio ya existe: ' . $data['domain']);
        }
        
        throw new Exception('Error guardando website: ' . $e->getMessage());
    }
}

// Actualizar status de website
function updateWebsiteStatus($id, $status, $metadata = []) {
    try {
        $db = getDB();
        
        $updates = ['status = :status'];
        $params = [
            ':id' => $id,
            ':status' => $status
        ];
        
        // Agregar campos específicos según status
        if ($status === 'approved') {
            $updates[] = 'approved_at = NOW()';
        } elseif ($status === 'live') {
            $updates[] = 'deployed_at = NOW()';
        }
        
        // Actualizar metadata si se proporciona
        if (!empty($metadata)) {
            $updates[] = 'config = JSON_MERGE_PATCH(config, :metadata)';
            $params[':metadata'] = json_encode($metadata);
        }
        
        $sql = "UPDATE websites SET " . implode(', ', $updates) . " WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
        
    } catch (PDOException $e) {
        error_log('Error actualizando status: ' . $e->getMessage());
        throw new Exception('Error actualizando website');
    }
}

// Obtener website por domain
function getWebsiteByDomain($domain) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT * FROM websites 
            WHERE domain = :domain 
            LIMIT 1
        ");
        
        $stmt->execute([':domain' => $domain]);
        
        $result = $stmt->fetch();
        
        // Decodificar config JSON
        if ($result && isset($result['config'])) {
            $result['config'] = json_decode($result['config'], true);
        }
        
        return $result;
        
    } catch (PDOException $e) {
        error_log('Error obteniendo website: ' . $e->getMessage());
        return null;
    }
}

// Obtener websites pendientes de aprobación
function getPendingWebsites($limit = 20) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT 
                id,
                domain,
                business_name,
                template,
                status,
                created_at,
                JSON_EXTRACT(config, '$.preview_url') as preview_url
            FROM websites 
            WHERE status = 'staging'
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log('Error obteniendo websites pendientes: ' . $e->getMessage());
        return [];
    }
}

// Log generation event
function logGenerationEvent($websiteId, $step, $status, $duration = null, $cost = null, $error = null) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO generation_logs (
                website_id,
                step,
                status,
                duration_ms,
                cost_usd,
                error,
                created_at
            ) VALUES (
                :website_id,
                :step,
                :status,
                :duration_ms,
                :cost_usd,
                :error,
                NOW()
            )
        ");
        
        $stmt->execute([
            ':website_id' => $websiteId,
            ':step' => $step,
            ':status' => $status,
            ':duration_ms' => $duration,
            ':cost_usd' => $cost,
            ':error' => $error
        ]);
        
        return $db->lastInsertId();
        
    } catch (PDOException $e) {
        error_log('Error logging event: ' . $e->getMessage());
        // No lanzar excepción, logging no debe romper flujo
        return null;
    }
}

// Obtener analytics
function getAnalytics($startDate = null, $endDate = null) {
    try {
        $db = getDB();
        
        $where = [];
        $params = [];
        
        if ($startDate) {
            $where[] = 'created_at >= :start_date';
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $where[] = 'created_at <= :end_date';
            $params[':end_date'] = $endDate;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_websites,
                SUM(CASE WHEN status = 'live' THEN 1 ELSE 0 END) as live_count,
                SUM(CASE WHEN status = 'staging' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                AVG(
                    TIMESTAMPDIFF(SECOND, created_at, approved_at)
                ) as avg_approval_time_seconds
            FROM websites
            $whereClause
        ");
        
        $stmt->execute($params);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log('Error obteniendo analytics: ' . $e->getMessage());
        return null;
    }
}

// Health check de database
function checkDatabaseHealth() {
    try {
        $db = getDB();
        
        // Test connection
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!isset($result['test']) || $result['test'] != 1) {
            return ['healthy' => false, 'error' => 'Query test failed: unexpected result'];
        }
        
        // Check table exists
        $stmt = $db->query("SHOW TABLES LIKE 'websites'");
        if ($stmt->rowCount() === 0) {
            return ['healthy' => false, 'error' => 'Table websites no existe'];
        }
        
        // Count websites
        $stmt = $db->query("SELECT COUNT(*) as total FROM websites");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'healthy' => true,
            'server_version' => $db->getAttribute(PDO::ATTR_SERVER_VERSION),
            'connection_status' => $db->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            'tables_verified' => true,
            'websites_count' => (int)$count['total']
        ];
        
    } catch (Exception $e) {
        return [
            'healthy' => false,
            'error' => $e->getMessage()
        ];
    }
}

?>
