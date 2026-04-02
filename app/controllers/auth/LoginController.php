<?php
/**
 * Login Controller
 * Handles: login form display, authentication, logout, and registration.
 */
class LoginController extends Controller {

    private UserModel  $userModel;
    private LogsModel  $logsModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->logsModel = $this->model('Logs');
    }

    /** GET /login — Show login form */
    public function index(): void {
        // Already logged in? Redirect to their dashboard
        if (is_logged_in()) {
            $this->redirectByRole(user_role());
        }
        $this->view('auth/login', [], false); // No layout wrapper for login page
    }

    /** POST /login/authenticate — Process credentials */
    public function authenticate(): void {
        if (!$this->isPost()) {
            $this->redirect('login/index');
        }

        // Basic input sanitization before lookup
        $email    = trim(filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL)   ?? '');
        $password = trim(filter_input(INPUT_POST, 'password', FILTER_DEFAULT)          ?? '');

        // Validate inputs are not empty
        if (empty($email) || empty($password)) {
            $this->flash('error', 'Email and password are required.');
            $this->redirect('login/index');
        }

        $user = $this->userModel->findByEmail($email);

        // Constant-time failure: always call verifyPassword even on miss
        // to prevent timing attacks
        $dummyHash   = '$2y$12$invalidhashfortimingprotection000000000000000000000000';
        $inputHash   = $user ? $user['password_hash'] : $dummyHash;
        $passwordOk  = $this->userModel->verifyPassword($password, $inputHash);

        if (!$user || !$passwordOk || !$user['is_active']) {
            // Log the failed attempt
            $this->logsModel->log('LOGIN_FAIL', 0, "Failed login attempt for: {$email}");
            $this->flash('error', 'Invalid email or password.');
            $this->redirect('login/index');
        }

        // ── Successful login ──────────────────────────────────────────────
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        $_SESSION['user_id']   = (int) $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email']= $user['email'];
        $_SESSION['logged_in_at'] = time();

        // Audit log
        $this->logsModel->log('LOGIN_SUCCESS', (int) $user['user_id'], "User logged in: {$email}");

        $this->redirectByRole($user['role']);
    }

    /** GET /login/logout — Destroy session */
    public function logout(): void {
        if (is_logged_in()) {
            $this->logsModel->log('LOGOUT', user_id(), 'User logged out.');
        }

        $_SESSION = [];

        // Expire the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }

        session_destroy();
        $this->redirect('login/index');
    }

    /** GET/POST /login/register — Customer self-registration */
    public function register(): void {
        if ($this->isPost()) {
            $name     = trim(filter_input(INPUT_POST, 'name',     FILTER_DEFAULT)       ?? '');
            $email    = trim(filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL) ?? '');
            $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT) ?? '';
            $phone    = trim(filter_input(INPUT_POST, 'phone',    FILTER_DEFAULT)       ?? '');

            // Validation
            $errors = [];
            if (strlen($name) < 2)                   $errors[] = 'Name must be at least 2 characters.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
            if (strlen($password) < 8)               $errors[] = 'Password must be at least 8 characters.';

            if (empty($errors)) {
                $created = $this->userModel->create([
                    'name'     => $name,
                    'email'    => $email,
                    'password' => $password,
                    'role'     => 'customer',
                    'phone'    => $phone,
                ]);

                if ($created) {
                    $this->flash('success', 'Account created! Please log in.');
                    $this->redirect('login/index');
                } else {
                    $errors[] = 'Registration failed — email may already be in use.';
                }
            }

            $this->view('auth/login', ['errors' => $errors, 'register_mode' => true], false);
            return;
        }

        $this->view('auth/login', ['register_mode' => true], false);
    }

    /** Redirect user to their role-appropriate dashboard */
    private function redirectByRole(string $role): void {
        match ($role) {
            'admin'        => $this->redirect('admin/dashboard'),
            'receptionist' => $this->redirect('receptionist/dashboard'),
            default        => $this->redirect('customer/dashboard'),
        };
    }
}