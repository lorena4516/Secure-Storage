<?php
class SettingsController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllSettings() {
        $query = "SELECT * FROM settings ORDER BY setting_key";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function getSetting($key) {
        $query = "SELECT setting_value FROM settings WHERE setting_key = :key";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['setting_value'];
        }
        return null;
    }

    public function updateSettings($settings) {
        try {
            $this->db->beginTransaction();

            foreach ($settings as $key => $value) {
                $query = "INSERT INTO settings (setting_key, setting_value) 
                          VALUES (:key, :value) 
                          ON DUPLICATE KEY UPDATE setting_value = :value, updated_at = NOW()";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(":key", $key);
                $stmt->bindParam(":value", $value);
                $stmt->execute();
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Configuración actualizada correctamente'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al actualizar la configuración: ' . $e->getMessage()];
        }
    }

    public function getSystemStats() {
        $stats = [];

        // Total de usuarios
        $query = "SELECT COUNT(*) as total FROM users";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de archivos
        $query = "SELECT COUNT(*) as total FROM files";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_files'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de grupos
        $query = "SELECT COUNT(*) as total FROM m_groups";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_groups'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Uso total de almacenamiento
        $query = "SELECT COALESCE(SUM(file_size), 0) as total FROM files";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_storage'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Tipos de archivo más comunes
        $query = "SELECT file_type, COUNT(*) as count 
                  FROM files 
                  GROUP BY file_type 
                  ORDER BY count DESC 
                  LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['file_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    public function validateBannedExtensions($extensions) {
        $ext_array = explode(',', $extensions);
        $valid_extensions = [];

        foreach ($ext_array as $ext) {
            $ext = trim($ext);
            if (!empty($ext)) {
                // Remover el punto si lo tiene
                $ext = ltrim($ext, '.');
                // Solo permitir caracteres alfanuméricos
                if (preg_match('/^[a-zA-Z0-9]+$/', $ext)) {
                    $valid_extensions[] = strtolower($ext);
                }
            }
        }

        return array_unique($valid_extensions);
    }
}
?>