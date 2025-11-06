<?php
class File {
    private $conn;
    private $table = 'files';

    public $id;
    public $user_id;
    public $filename;
    public $original_name;
    public $file_path;
    public $file_size;
    public $file_type;
    public $uploaded_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, filename=:filename, original_name=:original_name, 
                  file_path=:file_path, file_size=:file_size, file_type=:file_type";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":filename", $this->filename);
        $stmt->bindParam(":original_name", $this->original_name);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":file_size", $this->file_size);
        $stmt->bindParam(":file_type", $this->file_type);
        
        return $stmt->execute();
    }

    public function getUserFiles($user_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY uploaded_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt;
    }

    public function delete($file_id, $user_id) {
        // Primero obtener información del archivo
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $file_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Eliminar archivo físico
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }
            
            // Eliminar registro de la base de datos
            $deleteQuery = "DELETE FROM " . $this->table . " WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(":id", $file_id);
            
            return $deleteStmt->execute();
        }
        return false;
    }
}
?>