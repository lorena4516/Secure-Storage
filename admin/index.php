<?php
require_once 'header.php'; 

// Estadísticas para el dashboard
$user = new User($db);
$file = new File($db);
$group = new Group($db);

// Total de usuarios
$totalUsers = $db->query("SELECT COUNT(*) as total FROM users")->fetch(PDO::FETCH_ASSOC)['total'];

// Total de archivos
$totalFiles = $db->query("SELECT COUNT(*) as total FROM files")->fetch(PDO::FETCH_ASSOC)['total'];

// Total de grupos
$totalGroups = $db->query("SELECT COUNT(*) as total FROM m_groups")->fetch(PDO::FETCH_ASSOC)['total'];

// Uso total de almacenamiento
$totalStorage = $db->query("SELECT COALESCE(SUM(file_size), 0) as total FROM files")->fetch(PDO::FETCH_ASSOC)['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Secure Storage</title>
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
                        <a href="index.php" class="list-group-item list-group-item-action active">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="users.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-people"></i> Gestión de Usuarios
                        </a>
                        <a href="groups.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-collection"></i> Gestión de Grupos
                        </a>
                        <a href="settings.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-gear"></i> Configuración
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard de Administración</h2>
                </div>

                <!-- Estadísticas -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $totalUsers; ?></h4>
                                        <p class="mb-0">Usuarios</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-people fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $totalFiles; ?></h4>
                                        <p class="mb-0">Archivos</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-file-earmark fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $totalGroups; ?></h4>
                                        <p class="mb-0">Grupos</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-collection fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo formatBytes($totalStorage); ?></h4>
                                        <p class="mb-0">Almacenamiento</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-hdd fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Acciones Rápidas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="users.php?action=create" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-person-plus"></i><br>
                                            Nuevo Usuario
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="groups.php?action=create" class="btn btn-outline-success w-100">
                                            <i class="bi bi-plus-circle"></i><br>
                                            Nuevo Grupo
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="settings.php" class="btn btn-outline-warning w-100">
                                            <i class="bi bi-shield-check"></i><br>
                                            Seguridad
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="../index.php" class="btn btn-outline-info w-100">
                                            <i class="bi bi-arrow-left"></i><br>
                                            Volver al Sitio
                                        </a>
                                    </div>
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