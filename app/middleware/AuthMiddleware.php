<?php
/**
 * Auth Middleware
 * Call requireAuth() at the top of any protected controller method.
 */
class AuthMiddleware {

    /** Ensure user is logged in */
    public static function requireAuth(): void {
        require_once APP_PATH . '/helpers/session_helper.php';
        if (!is_logged_in()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please log in to continue.'];
            header('Location: ' . base_url('login/index'));
            exit;
        }
    }

    /** Ensure user has one of the given roles */
    public static function requireRole(string ...$roles): void {
        self::requireAuth();
        if (!in_array(user_role(), $roles, true)) {
            http_response_code(403);
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'You do not have permission to access that page.'];
            header('Location: ' . base_url('login/index'));
            exit;
        }
    }

    /** Ensure user is admin */
    public static function requireAdmin(): void {
        self::requireRole('admin');
    }

    /** Ensure user is admin or receptionist */
    public static function requireStaff(): void {
        self::requireRole('admin', 'receptionist');
    }
}