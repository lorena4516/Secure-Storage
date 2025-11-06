<?php
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $group_id;
    public $storage_limit;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username OR email = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->role = $row['role'];
                $this->group_id = $row['group_id'];
                $this->storage_limit = $row['storage_limit'];
                return true;
            }
        }
        return false;
    }

    // Cantidad de almacenamiento usada por usuario
    public function getUsedStorage() {
        $query = "SELECT COALESCE(SUM(file_size), 0) as total_size FROM files WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_size'];
    }

    public function getEffectiveStorageLimit() {
        // Si el usuario tiene un límite específico, usarlo
        if ($this->storage_limit && $this->storage_limit > 0) {
            return $this->storage_limit;
        }
        
        // Si pertenece a un grupo, usar el límite del grupo
        if ($this->group_id) {
            $query = "SELECT storage_limit FROM m_groups WHERE id = :group_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":group_id", $this->group_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['storage_limit'];
            }
        }
        
        // Usar límite global desde la configuración
        $global_limit = Config::get('global_storage_limit');
        return $global_limit ?: 10485760; // 10MB por defecto
    }
}
?>