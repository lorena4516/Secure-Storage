<?php
class GroupController {
    private $db;
    private $group;

    public function __construct($db) {
        $this->db = $db;
        $this->group = new Group($db);
    }

    public function getAllGroups() {
        $query = "SELECT g.*, COUNT(u.id) as user_count 
                  FROM m_groups g 
                  LEFT JOIN users u ON g.id = u.group_id 
                  GROUP BY g.id 
                  ORDER BY g.name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getGroupById($id) {
        $query = "SELECT * FROM m_groups WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    public function createGroup($data) {
        // Validar datos requeridos
        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'El nombre del grupo es requerido'];
        }

        // Verificar si el grupo ya existe
        $checkQuery = "SELECT id FROM m_groups WHERE name = :name";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(":name", $data['name']);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Ya existe un grupo con ese nombre'];
        }

        // Crear grupo
        $query = "INSERT INTO m_groups (name, storage_limit) VALUES (:name, :storage_limit)";
        $stmt = $this->db->prepare($query);
        
        $storage_limit = !empty($data['storage_limit']) ? $data['storage_limit'] * 1048576 : 10485760; // 10MB por defecto

        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":storage_limit", $storage_limit);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Grupo creado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al crear el grupo'];
    }

    public function updateGroup($id, $data) {
        // Verificar si el grupo existe
        $existingGroup = $this->getGroupById($id);
        if (!$existingGroup) {
            return ['success' => false, 'message' => 'Grupo no encontrado'];
        }

        // Verificar si el nombre ya existe (excluyendo el actual)
        $checkQuery = "SELECT id FROM m_groups WHERE name = :name AND id != :id";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(":name", $data['name']);
        $checkStmt->bindParam(":id", $id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Ya existe un grupo con ese nombre'];
        }

        // Actualizar grupo
        $query = "UPDATE m_groups SET name = :name, storage_limit = :storage_limit WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        $storage_limit = !empty($data['storage_limit']) ? $data['storage_limit'] * 1048576 : 10485760;

        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":storage_limit", $storage_limit);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Grupo actualizado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al actualizar el grupo'];
    }

    public function deleteGroup($id) {
        // Verificar si el grupo existe
        $existingGroup = $this->getGroupById($id);
        if (!$existingGroup) {
            return ['success' => false, 'message' => 'Grupo no encontrado'];
        }

        // Verificar si el grupo tiene usuarios asignados
        $usersQuery = "SELECT COUNT(*) as user_count FROM users WHERE group_id = :group_id";
        $usersStmt = $this->db->prepare($usersQuery);
        $usersStmt->bindParam(":group_id", $id);
        $usersStmt->execute();
        $userCount = $usersStmt->fetch(PDO::FETCH_ASSOC)['user_count'];

        if ($userCount > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar el grupo porque tiene usuarios asignados'];
        }

        // Eliminar grupo
        $query = "DELETE FROM m_groups WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Grupo eliminado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar el grupo'];
    }

    public function getGroupUsers($group_id) {
        $query = "SELECT u.* FROM users u WHERE u.group_id = :group_id ORDER BY u.username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":group_id", $group_id);
        $stmt->execute();
        return $stmt;
    }

    public function getGroupStorageUsage($group_id) {
        $query = "SELECT COALESCE(SUM(f.file_size), 0) as total_storage 
                  FROM files f 
                  JOIN users u ON f.user_id = u.id 
                  WHERE u.group_id = :group_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":group_id", $group_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_storage'];
    }

    public function getGroups() {
        $query = "SELECT * FROM m_groups ORDER BY name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>