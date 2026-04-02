<?php
/**
 * Session Helper
 * Start session safely and provide flash message utilities.
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,           // Until browser closes
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']), // HTTPS only in production
        'httponly' => true,        // JS cannot access cookie
        'samesite' => 'Strict',    // CSRF mitigation
    ]);
    session_start();
}

/** Check if a user is logged in */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/** Get the current user's role */
function user_role(): string {
    return $_SESSION['user_role'] ?? '';
}

/** Get the current user's ID */
function user_id(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

/** Get the current user's name */
function user_name(): string {
    return $_SESSION['user_name'] ?? 'Guest';
}

/** Retrieve and clear a flash message (one-time display) */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Require login — redirect to login page if not authenticated */
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . base_url('login/index'));
        exit;
    }
}

/** Require a specific role — redirect with error if unauthorized */
function require_role(string ...$roles): void {
    require_login();
    if (!in_array(user_role(), $roles, true)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Access denied.'];
        header('Location: ' . base_url('login/index'));
        exit;
    }
}