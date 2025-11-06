<?php
class UserController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    public function getAllUsers() {
        $query = "SELECT u.*, g.name as group_name 
                  FROM users u 
                  LEFT JOIN m_groups g ON u.group_id = g.id 
                  ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getUserById($id) {
        $query = "SELECT u.*, g.name as group_name 
                  FROM users u 
                  LEFT JOIN m_groups g ON u.group_id = g.id 
                  WHERE u.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    public function createUser($data) {
        // Validar datos requeridos
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'Todos los campos son requeridos'];
        }

        // Verificar si el usuario o email ya existen
        $checkQuery = "SELECT id FROM users WHERE username = :username OR email = :email";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(":username", $data['username']);
        $checkStmt->bindParam(":email", $data['email']);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'El usuario o email ya existen'];
        }

        // Crear usuario
        $query = "INSERT INTO users 
                  (username, email, password, role, group_id, storage_limit) 
                  VALUES 
                  (:username, :email, :password, :role, :group_id, :storage_limit)";
        
        $stmt = $this->db->prepare($query);
        
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $storage_limit = !empty($data['storage_limit']) ? $data['storage_limit'] * 1048576 : null; // Convertir MB a bytes

        $stmt->bindParam(":username", $data['username']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":role", $data['role']);
        $stmt->bindParam(":group_id", $data['group_id']);
        $stmt->bindParam(":storage_limit", $storage_limit);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Usuario creado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al crear el usuario'];
    }

    public function updateUser($id, $data) {
        // Verificar si el usuario existe
        $existingUser = $this->getUserById($id);
        if (!$existingUser) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        // Verificar si el usuario o email ya existen (excluyendo el actual)
        $checkQuery = "SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(":username", $data['username']);
        $checkStmt->bindParam(":email", $data['email']);
        $checkStmt->bindParam(":id", $id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'El usuario o email ya existen'];
        }

        // Construir query de actualización
        $query = "UPDATE users SET 
                  username = :username, 
                  email = :email, 
                  role = :role, 
                  group_id = :group_id, 
                  storage_limit = :storage_limit";

        // Si se proporciona nueva contraseña, actualizarla
        if (!empty($data['password'])) {
            $query .= ", password = :password";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->db->prepare($query);
        
        $storage_limit = !empty($data['storage_limit']) ? $data['storage_limit'] * 1048576 : null;

        $stmt->bindParam(":username", $data['username']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":role", $data['role']);
        $stmt->bindParam(":group_id", $data['group_id']);
        $stmt->bindParam(":storage_limit", $storage_limit);
        $stmt->bindParam(":id", $id);

        if (!empty($data['password'])) {
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $password_hash);
        }

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Usuario actualizado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al actualizar el usuario'];
    }

    public function deleteUser($id) {
        // No permitir eliminar al usuario admin principal
        if ($id == 1) {
            return ['success' => false, 'message' => 'No se puede eliminar el usuario administrador principal'];
        }

        // Verificar si el usuario existe
        $existingUser = $this->getUserById($id);
        if (!$existingUser) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        // Eliminar archivos del usuario primero
        $fileQuery = "DELETE FROM files WHERE user_id = :user_id";
        $fileStmt = $this->db->prepare($fileQuery);
        $fileStmt->bindParam(":user_id", $id);
        $fileStmt->execute();

        // Eliminar usuario
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Usuario eliminado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar el usuario'];
    }

    public function getUsersByGroup($group_id) {
        $query = "SELECT * FROM users WHERE group_id = :group_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":group_id", $group_id);
        $stmt->execute();
        return $stmt;
    }
}
?>