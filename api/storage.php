<?php
require_once '../app/config/Database.php';
require_once '../app/models/User.php';
require_once '../app/utils/Auth.php';
require_once '../app/config/Config.php';

// Configurar headers primero
header('Content-Type: application/json');

// Manejar errores
try {
    Auth::startSession();
    
    if (!Auth::isLoggedIn()) {
        throw new Exception('No autorizado');
    }

    $database = new Database();
    $db = $database->getConnection();

    $user = new User($db);
    $user->id = Auth::getUserId();

    $usedStorage = $user->getUsedStorage();
    $storageLimit = $user->getEffectiveStorageLimit();
    $percentage = $storageLimit > 0 ? round(($usedStorage / $storageLimit) * 100, 2) : 0;
    
    // Función para formatear bytes
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    echo json_encode([
        'success' => true,
        'used' => formatBytes($usedStorage),
        'limit' => formatBytes($storageLimit),
        'percentage' => $percentage,
        'used_bytes' => $usedStorage,
        'limit_bytes' => $storageLimit
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>