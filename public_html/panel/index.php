<?php
/**
 * PANEL ADMIN - Gestión de sitios web
 * URL: https://otavafitness.com/admin/
 */

// Contraseña simple para acceder (cambiar por una segura)
define('ADMIN_PASSWORD', '5893674120Fr.');

session_start();

// Verificar login
if (!isset($_SESSION['admin_logged'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === ADMIN_PASSWORD) {
            $_SESSION['admin_logged'] = true;
        } else {
            $error = 'Contraseña incorrecta';
        }
    }
    
    if (!isset($_SESSION['admin_logged'])) {
        // Mostrar login
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a2e, #16213e); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
                .login-box { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.3); width: 100%; max-width: 400px; }
                h1 { margin-bottom: 24px; color: #333; font-size: 24px; }
                input { width: 100%; padding: 14px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; margin-bottom: 16px; }
                input:focus { outline: none; border-color: #667eea; }
                button { width: 100%; padding: 14px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
                button:hover { opacity: 0.9; }
                .error { background: #fee; color: #c00; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h1>🔐 Admin Panel</h1>
                <?php if (isset($error)): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="password" name="password" placeholder="Contraseña" required autofocus>
                    <button type="submit">Entrar</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /admin/');
    exit;
}

// Cargar database
require_once dirname(__DIR__, 2) . '/_system/config/db.php';

// Obtener sitios
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM websites ORDER BY created_at DESC");
    $websites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $websites = [];
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sitios Web</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; min-height: 100vh; }
        
        .header { background: linear-gradient(135deg, #1a1a2e, #16213e); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; }
        .header a { color: rgba(255,255,255,.7); text-decoration: none; }
        .header a:hover { color: white; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.05); }
        .stat-card h3 { font-size: 32px; color: #333; }
        .stat-card p { color: #666; margin-top: 4px; }
        
        .section-title { font-size: 20px; font-weight: 600; margin-bottom: 20px; color: #333; }
        
        .sites-grid { display: grid; gap: 20px; }
        .site-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.05); overflow: hidden; }
        .site-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .site-name { font-size: 18px; font-weight: 600; color: #333; }
        .site-status { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-published, .status-live { background: #d4edda; color: #155724; }
        .status-staging, .status-pending_approval { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-generating { background: #cce5ff; color: #004085; }
        
        .site-body { padding: 20px; }
        .site-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px; }
        .site-info-item { font-size: 14px; }
        .site-info-item strong { color: #333; }
        .site-info-item span { color: #666; }
        
        .site-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; text-decoration: none; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-outline { background: white; border: 1px solid #ddd; color: #333; }
        .btn:hover { opacity: 0.9; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .empty-state h3 { margin-bottom: 10px; color: #333; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 32px; border-radius: 16px; max-width: 400px; width: 90%; text-align: center; }
        .modal-content h3 { margin-bottom: 16px; }
        .modal-content p { color: #666; margin-bottom: 24px; }
        .modal-actions { display: flex; gap: 12px; justify-content: center; }
        
        .toast { position: fixed; bottom: 20px; right: 20px; padding: 16px 24px; border-radius: 8px; color: white; font-weight: 500; transform: translateY(100px); opacity: 0; transition: all .3s; z-index: 1001; }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { background: #28a745; }
        .toast.error { background: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 Panel Admin</h1>
        <a href="?logout">Cerrar sesión</a>
    </div>
    
    <div class="container">
        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <h3><?= count($websites) ?></h3>
                <p>Total sitios</p>
            </div>
            <div class="stat-card">
                <h3><?= count(array_filter($websites, fn($w) => in_array($w['status'], ['published', 'live']))) ?></h3>
                <p>Publicados</p>
            </div>
            <div class="stat-card">
                <h3><?= count(array_filter($websites, fn($w) => $w['status'] === 'staging')) ?></h3>
                <p>En staging</p>
            </div>
        </div>
        
        <!-- Sites List -->
        <h2 class="section-title">Sitios Web</h2>
        
        <?php if (empty($websites)): ?>
            <div class="empty-state">
                <h3>No hay sitios todavía</h3>
                <p>Los sitios aparecerán aquí cuando los generes.</p>
            </div>
        <?php else: ?>
            <div class="sites-grid">
                <?php foreach ($websites as $site): ?>
                    <div class="site-card" data-slug="<?= htmlspecialchars($site['domain']) ?>">
                        <div class="site-header">
                            <span class="site-name"><?= htmlspecialchars($site['business_name']) ?></span>
                            <span class="site-status status-<?= $site['status'] ?>"><?= $site['status'] ?></span>
                        </div>
                        <div class="site-body">
                            <div class="site-info">
                                <div class="site-info-item">
                                    <strong>Slug:</strong>
                                    <span><?= htmlspecialchars($site['domain']) ?></span>
                                </div>
                                <div class="site-info-item">
                                    <strong>Template:</strong>
                                    <span><?= htmlspecialchars($site['template'] ?? 'N/A') ?></span>
                                </div>
                                <div class="site-info-item">
                                    <strong>Creado:</strong>
                                    <span><?= date('d/m/Y H:i', strtotime($site['created_at'])) ?></span>
                                </div>
                                <div class="site-info-item">
                                    <strong>ID:</strong>
                                    <span>#<?= $site['id'] ?></span>
                                </div>
                            </div>
                            <div class="site-actions">
                                <?php if ($site['production_url']): ?>
                                    <a href="<?= htmlspecialchars($site['production_url']) ?>" target="_blank" class="btn btn-success">🌐 Ver sitio</a>
                                <?php elseif ($site['staging_url']): ?>
                                    <a href="<?= htmlspecialchars($site['staging_url']) ?>" target="_blank" class="btn btn-primary">👁️ Ver staging</a>
                                <?php else: ?>
                                    <a href="https://otavafitness.com/domains/<?= htmlspecialchars($site['domain']) ?>/" target="_blank" class="btn btn-outline">🔗 Abrir</a>
                                <?php endif; ?>
                                <button class="btn btn-danger" onclick="confirmDelete('<?= htmlspecialchars($site['domain']) ?>', '<?= htmlspecialchars($site['business_name']) ?>')">🗑️ Eliminar</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal de confirmación -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h3>⚠️ Eliminar sitio</h3>
            <p>¿Estás seguro de eliminar <strong id="deleteName"></strong>?<br>Esta acción no se puede deshacer.</p>
            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeModal()">Cancelar</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Sí, eliminar</button>
            </div>
        </div>
    </div>
    
    <!-- Toast -->
    <div class="toast" id="toast"></div>
    
    <script>
        const API_KEY = '5893674120Fr.';
        let deleteSlug = '';
        
        function confirmDelete(slug, name) {
            deleteSlug = slug;
            document.getElementById('deleteName').textContent = name;
            document.getElementById('deleteModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('deleteModal').classList.remove('active');
            deleteSlug = '';
        }
        
        document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
            if (!deleteSlug) return;
            
            try {
                const response = await fetch('/api/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': API_KEY
                    },
                    body: JSON.stringify({ slug: deleteSlug })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Sitio eliminado correctamente', 'success');
                    // Remover card del DOM
                    document.querySelector(`[data-slug="${deleteSlug}"]`).remove();
                } else {
                    showToast('Error: ' + data.error, 'error');
                }
            } catch (e) {
                showToast('Error de conexión', 'error');
            }
            
            closeModal();
        });
        
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
        
        // Cerrar modal con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>
