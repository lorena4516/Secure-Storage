<?php
class Config {
    public static function get($key) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "SELECT setting_value FROM settings WHERE setting_key = :key";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['setting_value'];
        }
        return null;
    }
    
    public static function set($key, $value) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "INSERT INTO settings (setting_key, setting_value) 
                  VALUES (:key, :value) 
                  ON DUPLICATE KEY UPDATE setting_value = :value, updated_at = NOW()";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->bindParam(":value", $value);
        
        return $stmt->execute();
    }
}
?>