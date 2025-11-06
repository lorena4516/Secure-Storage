<?php
require_once '../app/config/Database.php';
require_once '../app/models/File.php';
require_once '../app/models/User.php';
require_once '../app/controllers/FileController.php';
require_once '../app/utils/Auth.php';
require_once '../app/config/Config.php';

Auth::startSession();
header('Content-Type: application/json');

if (!Auth::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$fileController = new FileController($db);
$user_id = Auth::getUserId();

try {
    if ($_GET['action'] ?? '' === 'list') {
        $stmt = $fileController->getUserFiles($user_id);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($files);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'upload':
                if (isset($_FILES['file'])) {
                    $result = $fileController->uploadFile($user_id, $_FILES['file']);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo']);
                }
                break;
                
            case 'delete':
                $file_id = $_POST['file_id'] ?? '';
                if ($file_id) {
                    $result = $fileController->deleteFile($file_id, $user_id);
                    echo json_encode(['success' => $result, 'message' => $result ? 'Archivo eliminado' : 'Error al eliminar']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>