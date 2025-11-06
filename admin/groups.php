<?php
    require_once 'header.php'; 
    $groupController = new GroupController($db);
    $userController = new UserController($db);

    // Manejar acciones
    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? '';

    $message = '';
    $message_type = '';

    if ($_POST) {
        $data = $_POST;
        
        if ($_POST['action'] == 'create') {
            $result = $groupController->createGroup($data);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'danger';
        } 
        elseif ($_POST['action'] == 'edit') {
            $result = $groupController->updateGroup($data['id'], $data);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'danger';
        }
    }

    if ($action == 'delete' && $id) {
        $result = $groupController->deleteGroup($id);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
    }

    // Obtener lista de grupos
    $groups = $groupController->getAllGroups();

    // Obtener grupo para editar
    $edit_group = null;
    if ($action == 'edit' && $id) {
        $edit_group = $groupController->getGroupById($id);
    }

// Obtener usuarios del grupo para la vista de detalles
    $group_users = null;
    $group_storage_usage = null;
    if ($action == 'view' && $id) {
        $group_users = $groupController->getGroupUsers($id);
        $group_storage_usage = $groupController->getGroupStorageUsage($id);
        $edit_group = $groupController->getGroupById($id);
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grupos - Secure Storage</title>
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
                        <a href="groups.php" class="list-group-item list-group-item-action active">
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
                    <h2>Gestión de Grupos</h2>
                    <a href="groups.php?action=create" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nuevo Grupo
                    </a>
                </div>

                <!-- Mensajes -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulario de Crear/Editar -->
                <?php if ($action == 'create' || $action == 'edit'): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-collection-<?php echo $action == 'create' ? 'plus' : 'check'; ?>"></i>
                            <?php echo $action == 'create' ? 'Crear Nuevo Grupo' : 'Editar Grupo'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action == 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_group['id']; ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nombre del Grupo *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo $edit_group['name'] ?? ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="storage_limit" class="form-label">Límite de Almacenamiento (MB) *</label>
                                        <input type="number" class="form-control" id="storage_limit" name="storage_limit" 
                                               value="<?php echo $edit_group['storage_limit'] ? round($edit_group['storage_limit'] / 1048576, 2) : '10'; ?>"
                                               required min="1">
                                        <div class="form-text">Límite de almacenamiento para los usuarios del grupo</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-lg"></i> 
                                    <?php echo $action == 'create' ? 'Crear Grupo' : 'Actualizar Grupo'; ?>
                                </button>
                                <a href="groups.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Vista de Detalles del Grupo -->
                <?php if ($action == 'view' && $edit_group): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-eye"></i> Detalles del Grupo: <?php echo htmlspecialchars($edit_group['name']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información del Grupo</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Nombre:</th>
                                        <td><?php echo htmlspecialchars($edit_group['name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Límite de Almacenamiento:</th>
                                        <td><?php echo round($edit_group['storage_limit'] / 1048576, 2); ?> MB</td>
                                    </tr>
                                    <tr>
                                        <th>Uso Actual:</th>
                                        <td>
                                            <?php echo formatBytes($group_storage_usage); ?>
                                            <?php if ($edit_group['storage_limit'] > 0): ?>
                                                (<?php echo round(($group_storage_usage / $edit_group['storage_limit']) * 100, 2); ?>%)
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de Creación:</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($edit_group['created_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="progress mb-3" style="height: 20px;">
                                    <?php if ($edit_group['storage_limit'] > 0): ?>
                                        <?php $usage_percentage = min(100, ($group_storage_usage / $edit_group['storage_limit']) * 100); ?>
                                        <div class="progress-bar <?php echo $usage_percentage > 80 ? 'bg-danger' : 'bg-success'; ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $usage_percentage; ?>%"
                                             aria-valuenow="<?php echo $usage_percentage; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo round($usage_percentage, 1); ?>%
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center">
                                    <a href="groups.php?action=edit&id=<?php echo $edit_group['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Editar Grupo
                                    </a>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6>Usuarios en este Grupo (<?php echo $group_users->rowCount(); ?>)</h6>
                        <?php if ($group_users->rowCount() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = $group_users->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No hay usuarios en este grupo.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Lista de Grupos -->
                <?php if ($action != 'create' && $action != 'edit' && $action != 'view'): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Grupos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Límite</th>
                                        <th>Usuarios</th>
                                        <th>Uso</th>
                                        <th>Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($group = $groups->fetch(PDO::FETCH_ASSOC)): 
                                        $storage_usage = $groupController->getGroupStorageUsage($group['id']);
                                        $usage_percentage = $group['storage_limit'] > 0 ? ($storage_usage / $group['storage_limit']) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo $group['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($group['name']); ?></strong>
                                        </td>
                                        <td><?php echo round($group['storage_limit'] / 1048576, 2); ?> MB</td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $group['user_count']; ?> usuarios</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar <?php echo $usage_percentage > 80 ? 'bg-danger' : 'bg-success'; ?>" 
                                                         style="width: <?php echo min(100, $usage_percentage); ?>%">
                                                    </div>
                                                </div>
                                                <small><?php echo formatBytes($storage_usage); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($group['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="groups.php?action=view&id=<?php echo $group['id']; ?>" 
                                                   class="btn btn-outline-info" title="Ver detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="groups.php?action=edit&id=<?php echo $group['id']; ?>" 
                                                   class="btn btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($group['user_count'] == 0): ?>
                                                    <a href="groups.php?action=delete&id=<?php echo $group['id']; ?>" 
                                                       class="btn btn-outline-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar este grupo?')"
                                                       title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-secondary" disabled title="No se puede eliminar (tiene usuarios)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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