<?php
/**
 * EduChad Bootstrap
 *
 * This file initializes the application:
 * - Loads configuration
 * - Starts session
 * - Loads core classes
 * - Sets up error handling
 * - Enforces security measures
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Load helper functions
require_once __DIR__ . '/helpers.php';

// Load core classes
require_once __DIR__ . '/Core/DB.php';
require_once __DIR__ . '/Core/Auth.php';
require_once __DIR__ . '/Core/License.php';
require_once __DIR__ . '/Core/View.php';

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error handling
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);

    // Custom error handler
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        $log_message = date('Y-m-d H:i:s') . " | Error [$errno]: $errstr in $errfile on line $errline\n";
        error_log($log_message, 3, LOG_FILE);

        if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            http_response_code(500);
            die('Une erreur est survenue. Veuillez contacter l\'administrateur.');
        }
    });
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

session_name(SESSION_NAME);
session_start();

// Session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
    session_unset();
    session_destroy();
    session_start();
    flash('error', 'Votre session a expiré. Veuillez vous reconnecter.');
}
$_SESSION['last_activity'] = time();

// CSRF protection for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? $_POST[CSRF_TOKEN_NAME] ?? '';

    if (!csrf_check($token)) {
        http_response_code(419);
        die('Token CSRF invalide. Veuillez réessayer.');
    }
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Content Security Policy (basic)
if (!DEBUG) {
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
}

// Create necessary directories if they don't exist
$directories = [
    LOGS_PATH,
    EXPORTS_PATH,
    UPLOADS_PATH,
    UPLOADS_PATH . '/logos',
    UPLOADS_PATH . '/students'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Create log file if it doesn't exist
if (!file_exists(LOG_FILE)) {
    touch(LOG_FILE);
    chmod(LOG_FILE, 0644);
}

// Database connection test (only in debug mode)
if (DEBUG) {
    try {
        DB::pdo();
    } catch (PDOException $e) {
        die('Erreur de connexion à la base de données: ' . $e->getMessage());
    }
}
