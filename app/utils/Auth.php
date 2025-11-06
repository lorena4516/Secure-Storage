<?php
class Auth {
    public static function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login($user) {
        self::startSession();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        $_SESSION['group_id'] = $user->group_id;
    }

    public static function logout() {
        self::startSession();
        session_destroy();
    }

    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin() {
        self::startSession();
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function getUserId() {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    public static function requireAuth() {
        if (!self::isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }

    public static function requireAdmin() {
        self::requireAuth();
        if (!self::isAdmin()) {
            header('Location: /index.php');
            exit;
        }
    }
}
?>