<?php require_once 'header.php'; ?>

<?php
$userController = new UserController($db);
$groupModel = new Group($db);
$groups = $groupModel->getAll();

// Manejar acciones
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

$message = '';
$message_type = '';

if ($_POST) {
    $data = $_POST;
    
    if ($_POST['action'] == 'create') {
        $result = $userController->createUser($data);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
    } 
    elseif ($_POST['action'] == 'edit') {
        $result = $userController->updateUser($data['id'], $data);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
    }
}

if ($action == 'delete' && $id) {
    $result = $userController->deleteUser($id);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

// Obtener lista de usuarios
$users = $userController->getAllUsers();

// Obtener usuario para editar
$edit_user = null;
if ($action == 'edit' && $id) {
    $edit_user = $userController->getUserById($id);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Secure Storage</title>
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
                        <a href="users.php" class="list-group-item list-group-item-action active">
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
                    <h2>Gestión de Usuarios</h2>
                    <a href="users.php?action=create" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Nuevo Usuario
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
                            <i class="bi bi-person-<?php echo $action == 'create' ? 'plus' : 'check'; ?>"></i>
                            <?php echo $action == 'create' ? 'Crear Nuevo Usuario' : 'Editar Usuario'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action == 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Usuario *</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?php echo $edit_user['username'] ?? ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo $edit_user['email'] ?? ''; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            Contraseña <?php echo $action == 'create' ? '*' : '(dejar vacío para no cambiar)'; ?>
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               <?php echo $action == 'create' ? 'required' : ''; ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Rol *</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="user" <?php echo ($edit_user['role'] ?? '') == 'user' ? 'selected' : ''; ?>>Usuario</option>
                                            <option value="admin" <?php echo ($edit_user['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="group_id" class="form-label">Grupo</label>
                                        <select class="form-select" id="group_id" name="group_id">
                                            <option value="">Sin grupo</option>
                                            <?php while ($group = $groups->fetch(PDO::FETCH_ASSOC)): ?>
                                                <option value="<?php echo $group['id']; ?>" 
                                                    <?php echo ($edit_user['group_id'] ?? '') == $group['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($group['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="storage_limit" class="form-label">Límite de Almacenamiento (MB)</label>
                                        <input type="number" class="form-control" id="storage_limit" name="storage_limit" 
                                               value="<?php echo $edit_user['storage_limit'] ? round($edit_user['storage_limit'] / 1048576, 2) : ''; ?>"
                                               placeholder="Dejar vacío para usar límite por defecto">
                                        <div class="form-text">En megabytes. Vacío = usar límite del grupo o global</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-lg"></i> 
                                    <?php echo $action == 'create' ? 'Crear Usuario' : 'Actualizar Usuario'; ?>
                                </button>
                                <a href="users.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Lista de Usuarios -->
                <?php if ($action != 'create' && $action != 'edit'): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Usuarios</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Grupo</th>
                                        <th>Límite</th>
                                        <th>Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $users->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                            <?php if ($user['id'] == 1): ?>
                                                <span class="badge bg-warning">Principal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['group_name']): ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($user['group_name']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin grupo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['storage_limit']): ?>
                                                <?php echo round($user['storage_limit'] / 1048576, 2); ?> MB
                                            <?php else: ?>
                                                <span class="text-muted">Por defecto</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($user['id'] != 1): ?>
                                                    <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-outline-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
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