<?php
require_once '../app/autoload.php';
Auth::requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Inicializar controladores
$userController = new UserController($db);
$fileController = new FileController($db);
$groupController = new GroupController($db);
$settingsController = new SettingsController($db);
?>