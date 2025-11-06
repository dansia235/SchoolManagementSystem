<?php
/**
 * Authentication and Authorization
 *
 * Handles user authentication, session management, and role-based access control
 */
class Auth {
    /**
     * Attempt to log in with username and password
     *
     * @param string $username Username
     * @param string $password User password
     * @return bool Success status
     */
    public static function attempt($username, $password) {
        $st = DB::pdo()->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1');
        $st->execute([$username]);
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['uid'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();

            // Log activity
            log_activity('login', 'user', $user['id'], 'User logged in: ' . $username);

            return true;
        }

        // Log failed attempt
        error_log('Failed login attempt for username: ' . $username);

        return false;
    }

    /**
     * Get currently authenticated user
     *
     * @return array|null User data or null
     */
    public static function user() {
        if (empty($_SESSION['uid'])) {
            return null;
        }

        static $user = null;

        if ($user === null) {
            $st = DB::pdo()->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
            $st->execute([$_SESSION['uid']]);
            $user = $st->fetch();

            // If user not found or inactive, clear session
            if (!$user) {
                self::logout();
                return null;
            }
        }

        return $user;
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public static function check() {
        return self::user() !== null;
    }

    /**
     * Check if user is a guest (not authenticated)
     *
     * @return bool
     */
    public static function guest() {
        return !self::check();
    }

    /**
     * Get user ID
     *
     * @return int|null
     */
    public static function id() {
        $user = self::user();
        return $user ? $user['id'] : null;
    }

    /**
     * Get user role
     *
     * @return string|null
     */
    public static function role() {
        $user = self::user();
        return $user ? $user['role'] : null;
    }

    /**
     * Check if user has specific role(s)
     *
     * @param string|array $roles Role(s) to check
     * @return bool
     */
    public static function hasRole($roles) {
        $user = self::user();
        if (!$user) {
            return false;
        }

        $roles = (array) $roles;
        return in_array($user['role'], $roles, true);
    }

    /**
     * Require authentication (redirect to login if not authenticated)
     */
    public static function requireAuth() {
        if (!self::check()) {
            flash('error', 'Vous devez être connecté pour accéder à cette page.');
            redirect('login');
        }
    }

    /**
     * Require specific role(s) (403 error if not authorized)
     *
     * @param string|array $roles Required role(s)
     */
    public static function requireRole($roles) {
        self::requireAuth();

        if (!self::hasRole($roles)) {
            http_response_code(403);
            die('
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>Accès refusé</title>
                    <style>
                        body { font-family: sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; text-align: center; }
                        h1 { color: #dc2626; }
                        a { color: #0284c7; text-decoration: none; }
                    </style>
                </head>
                <body>
                    <h1>⛔ Accès refusé</h1>
                    <p>Vous n\'avez pas les permissions nécessaires pour accéder à cette page.</p>
                    <p><a href="index.php?page=dashboard">← Retour au tableau de bord</a></p>
                </body>
                </html>
            ');
        }
    }

    /**
     * Logout user
     */
    public static function logout() {
        $user = self::user();
        if ($user) {
            log_activity('logout', 'user', $user['id'], 'User logged out');
        }

        // Clear session
        $_SESSION = [];

        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy session
        session_destroy();

        // Start new session
        session_start();
    }

    /**
     * Register a new user
     *
     * @param array $data User data
     * @return int|false User ID or false on failure
     */
    public static function register($data) {
        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return false;
        }

        // Check if email already exists
        $st = DB::pdo()->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$data['email']]);
        if ($st->fetch()) {
            flash('error', 'Cette adresse email est déjà utilisée.');
            return false;
        }

        // Hash password
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $st = DB::pdo()->prepare('
            INSERT INTO users (name, email, password_hash, role, is_active)
            VALUES (?, ?, ?, ?, ?)
        ');

        $result = $st->execute([
            $data['name'],
            $data['email'],
            $password_hash,
            $data['role'] ?? 'VIEWER',
            $data['is_active'] ?? 1
        ]);

        if ($result) {
            $user_id = DB::lastInsertId();
            log_activity('user_created', 'user', $user_id, 'New user registered');
            return $user_id;
        }

        return false;
    }

    /**
     * Update user password
     *
     * @param int $user_id User ID
     * @param string $new_password New password
     * @return bool Success status
     */
    public static function updatePassword($user_id, $new_password) {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $st = DB::pdo()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $result = $st->execute([$password_hash, $user_id]);

        if ($result) {
            log_activity('password_changed', 'user', $user_id, 'Password updated');
        }

        return $result;
    }

    /**
     * Check if password meets requirements
     *
     * @param string $password Password to check
     * @return bool
     */
    public static function isStrongPassword($password) {
        // At least PASSWORD_MIN_LENGTH characters, 1 uppercase, 1 lowercase, 1 number
        return strlen($password) >= PASSWORD_MIN_LENGTH &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }
}
