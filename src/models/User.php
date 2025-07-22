<?php

require_once "database/Database.php";

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function create($username, $email, $password)
    {
        $id = UUID::generate();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (id, username, email, password) VALUES (?, ?, ?, ?)",
        );
        return $stmt->execute([$id, $username, $email, $hashedPassword]);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?",
        );
        return $stmt->execute([$hashedPassword, $userId]);
    }

    // ================================
    // 4. OPCIONAL: Agregar en User.php para logs de seguridad
    // ================================

    public function logPasswordChange($userId)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO password_changes_log (user_id, changed_at, ip_address) VALUES (?, NOW(), ?)",
        );
        $ipAddress = $_SERVER["REMOTE_ADDR"] ?? "unknown";
        return $stmt->execute([$userId, $ipAddress]);
    }
}
?>
