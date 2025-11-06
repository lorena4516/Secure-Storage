<?php require_once 'header.php'; ?>

<?php
$settingsController = new SettingsController($db);

// Obtener configuración actual
$current_settings = $settingsController->getAllSettings();
$system_stats = $settingsController->getSystemStats();

$message = '';
$message_type = '';

// Procesar actualización de configuración
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'update_settings') {
    $settings = [
        'global_storage_limit' => $_POST['global_storage_limit'] * 1048576, // Convertir MB a bytes
        'banned_extensions' => $_POST['banned_extensions']
    ];

    // Validar extensiones prohibidas
    $valid_extensions = $settingsController->validateBannedExtensions($settings['banned_extensions']);
    $settings['banned_extensions'] = implode(',', $valid_extensions);

    $result = $settingsController->updateSettings($settings);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';

    // Recargar configuración actualizada
    $current_settings = $settingsController->getAllSettings();
}

// Valores por defecto si no existen en la base de datos
$global_storage_limit = isset($current_settings['global_storage_limit']) ? 
    round($current_settings['global_storage_limit'] / 1048576, 2) : 10;
$banned_extensions = $current_settings['banned_extensions'] ?? 'exe,bat,js,php,sh,com,pif,cmd,vbs,scr,msi';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Secure Storage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-shield-lock"></i> Secure Storage
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)
                </span>
                <a class="nav-link" href="../index.php">
                    <i class="bi bi-house"></i> Inicio
                </a>
                <a class="nav-link" href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Menú de Administración</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="index.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="users.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-people"></i> Gestión de Usuarios
                        </a>
                        <a href="groups.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-collection"></i> Gestión de Grupos
                        </a>
                        <a href="settings.php" class="list-group-item list-group-item-action active">
                            <i class="bi bi-gear"></i> Configuración
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Configuración del Sistema</h2>
                </div>

                <!-- Mensajes -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Configuración de Almacenamiento -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-hdd"></i> Configuración de Almacenamiento
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_settings">
                                    
                                    <div class="mb-3">
                                        <label for="global_storage_limit" class="form-label">
                                            Límite Global de Almacenamiento (MB)
                                        </label>
                                        <input type="number" class="form-control" id="global_storage_limit" 
                                               name="global_storage_limit" value="<?php echo $global_storage_limit; ?>" 
                                               min="1" required>
                                        <div class="form-text">
                                            Límite por defecto para usuarios que no tienen límite específico o de grupo.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="banned_extensions" class="form-label">
                                            Extensiones Prohibidas
                                        </label>
                                        <textarea class="form-control" id="banned_extensions" name="banned_extensions" 
                                                  rows="4" required><?php echo htmlspecialchars($banned_extensions); ?></textarea>
                                        <div class="form-text">
                                            Lista de extensiones separadas por comas (ej: exe,bat,js,php). Estas extensiones serán bloqueadas en las subidas.
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-lg"></i> Guardar Configuración
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas del Sistema -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-graph-up"></i> Estadísticas del Sistema
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-primary"><?php echo $system_stats['total_users']; ?></h3>
                                            <small class="text-muted">Usuarios</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-success"><?php echo $system_stats['total_files']; ?></h3>
                                            <small class="text-muted">Archivos</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-warning"><?php echo $system_stats['total_groups']; ?></h3>
                                            <small class="text-muted">Grupos</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-danger"><?php echo formatBytes($system_stats['total_storage']); ?></h3>
                                            <small class="text-muted">Almacenamiento</small>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <h6>Tipos de Archivo Más Comunes</h6>
                                <div class="list-group">
                                    <?php if (!empty($system_stats['file_types'])): ?>
                                        <?php foreach ($system_stats['file_types'] as $file_type): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span><?php echo htmlspecialchars($file_type['file_type'] ?: 'Sin tipo'); ?></span>
                                                <span class="badge bg-primary rounded-pill"><?php echo $file_type['count']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted text-center">No hay archivos subidos</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle"></i> Información del Sistema
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Versión PHP:</th>
                                                <td><?php echo phpversion(); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Servidor Web:</th>
                                                <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Base de Datos:</th>
                                                <td>MySQL/PostgreSQL</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Límite de Subida:</th>
                                                <td><?php echo ini_get('upload_max_filesize'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Límite de Post:</th>
                                                <td><?php echo ini_get('post_max_size'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Memoria Límite:</th>
                                                <td><?php echo ini_get('memory_limit'); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Extensiones Prohibidas Actuales -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0">
                                    <i class="bi bi-shield-exclamation"></i> Extensiones Actualmente Prohibidas
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $extensions = explode(',', $banned_extensions);
                                $chunked_extensions = array_chunk($extensions, 6);
                                ?>
                                <div class="row">
                                    <?php foreach ($chunked_extensions as $chunk): ?>
                                        <div class="col-md-2">
                                            <ul class="list-unstyled">
                                                <?php foreach ($chunk as $ext): ?>
                                                    <li>
                                                        <span class="badge bg-danger">.<?php echo htmlspecialchars(trim($ext)); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle"></i> 
                                    <strong>Nota:</strong> Estas extensiones están bloqueadas tanto en archivos individuales como dentro de archivos ZIP.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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