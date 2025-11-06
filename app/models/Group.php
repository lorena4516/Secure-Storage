<?php
class Group {
    private $conn;
    private $table = 'm_groups';

    public $id;
    public $name;
    public $storage_limit;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para obtener todos los grupos
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Método para crear grupo
    public function create() {
        $query = "INSERT INTO " . $this->table . " SET name=:name, storage_limit=:storage_limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":storage_limit", $this->storage_limit);
        return $stmt->execute();
    }

    // Método para obtener grupo por ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // Método para actualizar grupo
    public function update() {
        $query = "UPDATE " . $this->table . " SET name=:name, storage_limit=:storage_limit WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":storage_limit", $this->storage_limit);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // Método para eliminar grupo
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // Método para contar usuarios en el grupo
    public function countUsers() {
        $query = "SELECT COUNT(*) as user_count FROM users WHERE group_id = :group_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":group_id", $this->id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['user_count'];
    }
}
?>