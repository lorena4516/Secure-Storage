<?php
require_once 'app/config/Database.php';
require_once 'app/config/Config.php';
require_once 'app/models/User.php';
require_once 'app/models/File.php';
require_once 'app/utils/Auth.php';

Auth::requireAuth();

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->id = Auth::getUserId();

$usedStorage = $user->getUsedStorage();
$storageLimit = $user->getEffectiveStorageLimit();
$storagePercentage = $storageLimit > 0 ? round(($usedStorage / $storageLimit) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - Secure Storage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-shield-lock"></i> Secure Storage
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <?php if (Auth::isAdmin()): ?>
                    <a class="nav-link" href="admin/">
                        <i class="bi bi-speedometer2"></i> Panel Admin
                    </a>
                <?php endif; ?>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Subir Archivo</h4>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="fileInput" class="form-label">Seleccionar archivo</label>
                                <input class="form-control" type="file" id="fileInput" name="file" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Subir Archivo</button>
                        </form>
                        
                        <div id="uploadProgress" class="mt-3" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                            </div>
                            <small class="text-muted">Subiendo archivo...</small>
                        </div>
                        
                        <div id="uploadStatus" class="mt-3" style="display: none;"></div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Mis Archivos</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tamaño</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="filesList"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
                      
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Uso de Almacenamiento</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span id="storageUsed">Usado: <?php echo formatBytes($usedStorage); ?></span>
                                <span id="storageLimit">Límite: <?php echo formatBytes($storageLimit); ?></span>
                            </div>
                        </div>
                        <div class="progress">
                            <div id="storageProgress" 
                                class="progress-bar <?php echo $storagePercentage > 80 ? 'bg-danger' : 'bg-success'; ?>" 
                                role="progressbar" 
                                style="width: <?php echo $storagePercentage; ?>%"
                                aria-valuenow="<?php echo $storagePercentage; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                <?php echo $storagePercentage; ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="public/js/file-upload.js"></script>
</body>
</html>

<?php
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>