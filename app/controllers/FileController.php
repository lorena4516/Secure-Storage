<?php
class FileController {
    private $db;
    private $file;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->file = new File($db);
        $this->user = new User($db);
    }

    public function uploadFile($user_id, $uploadedFile) {
        // Verificar si el usuario existe
        $this->user->id = $user_id;
        // Uso de espacio actual
        $usedStorage = $this->user->getUsedStorage();
        // Almacenamiento asignado
        $storageLimit = $this->user->getEffectiveStorageLimit();

        // Validar cuota de almacenamiento
        if ($usedStorage + $uploadedFile['size'] > $storageLimit) {
            return [
                'success' => false,
                'message' => "Error: Cuota de almacenamiento (" . $this->formatBytes($storageLimit) . ") excedida"
            ];
        }

        // Validar tipo de archivo
        $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        $bannedExtensions = explode(',', Config::get('banned_extensions'));
        
        if (in_array($fileExtension, $bannedExtensions)) {
            return [
                'success' => false,
                'message' => "Error: El tipo de archivo '.$fileExtension' no está permitido"
            ];
        }

        // Validar archivos ZIP
        if ($fileExtension === 'zip') {
            $zipCheck = $this->validateZipFile($uploadedFile['tmp_name']);
            if (!$zipCheck['success']) {
                return $zipCheck;
            }
        }

        // Crear directorio de uploads si no existe
        $uploadDir = 'public/uploads/' . $user_id . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generar nombre único para el archivo
        $filename = uniqid() . '_' . basename($uploadedFile['name']);
        $filePath = $uploadDir . $filename;

        // Mover archivo
        if (move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
            
            $fileType = $uploadedFile['type'];
            if (strlen($fileType) > 100) {
                $fileType = substr($fileType, 0, 97) . '...';
            }
            
            if (empty($fileType)) {
                $fileType = $fileExtension;
            }

            // Guardar en base de datos
            $this->file->user_id = $user_id;
            $this->file->filename = $filename;
            $this->file->original_name = $uploadedFile['name'];
            $this->file->file_path = $filePath;
            $this->file->file_size = $uploadedFile['size'];
            $this->file->file_type = $fileType;

            if ($this->file->create()) {
                return [
                    'success' => true,
                    'message' => 'Archivo subido correctamente'
                ];
            } else {
                // Si hay error al guardar en BD, eliminar el archivo físico
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                return [
                    'success' => false,
                    'message' => 'Error al guardar la información del archivo en la base de datos'
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Error al subir el archivo'
        ];
    }

    private function validateZipFile($zipPath) {
        if (!class_exists('ZipArchive')) {
            return ['success' => false, 'message' => 'Error: No se puede validar archivo ZIP'];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            $bannedExtensions = explode(',', Config::get('banned_extensions'));
            
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileInfo = $zip->statIndex($i);
                $fileName = $fileInfo['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                if (in_array($fileExtension, $bannedExtensions)) {
                    $zip->close();
                    return [
                        'success' => false,
                        'message' => "Error: El archivo '$fileName' dentro del .zip no está permitido"
                    ];
                }
            }
            $zip->close();
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Error: No se puede abrir el archivo ZIP'];
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function getUserFiles($user_id) {
        return $this->file->getUserFiles($user_id);
    }

    public function deleteFile($file_id, $user_id) {
        return $this->file->delete($file_id, $user_id);
    }
}
?>