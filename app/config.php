<?php
/**
 * EduChad Global Configuration
 *
 * This file contains application-wide constants and configurations
 */

// Load environment variables
$env = require __DIR__ . '/.env.php';

// Define application constants
define('APP_NAME', $env['APP_NAME']);
define('APP_URL', $env['APP_URL']);
define('APP_VERSION', '1.0.0');
define('APP_SECRET', $env['APP_SECRET']);

// Define paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('VIEWS_PATH', APP_PATH . '/Views');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('LOGS_PATH', STORAGE_PATH . '/logs');
define('EXPORTS_PATH', STORAGE_PATH . '/exports');

// Database configuration
define('DB_HOST', $env['DB_HOST']);
define('DB_NAME', $env['DB_NAME']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);
define('DB_PORT', $env['DB_PORT']);

// Timezone
define('TIMEZONE', $env['TIMEZONE']);

// Session configuration
define('SESSION_LIFETIME', $env['SESSION_LIFETIME']);
define('SESSION_NAME', $env['SESSION_NAME']);

// Security
define('CSRF_TOKEN_NAME', $env['CSRF_TOKEN_NAME']);
define('PASSWORD_MIN_LENGTH', $env['PASSWORD_MIN_LENGTH']);

// File upload
define('MAX_UPLOAD_SIZE', $env['MAX_UPLOAD_SIZE']);
define('ALLOWED_IMAGE_TYPES', $env['ALLOWED_IMAGE_TYPES']);

// Pagination
define('ITEMS_PER_PAGE', $env['ITEMS_PER_PAGE']);

// Debug mode
define('DEBUG', $env['DEBUG']);

// Logging
define('LOG_LEVEL', $env['LOG_LEVEL']);
define('LOG_FILE', $env['LOG_FILE']);

// Academic terms
define('TERMS', ['T1' => 'Trimestre 1', 'T2' => 'Trimestre 2', 'T3' => 'Trimestre 3']);

// User roles
define('ROLES', [
    'ADMIN' => 'Administrateur',
    'CASHIER' => 'Caissier',
    'TEACHER' => 'Enseignant',
    'VIEWER' => 'Observateur'
]);

// Payment methods
define('PAYMENT_METHODS', [
    'CASH' => 'Espèces',
    'MOBILE' => 'Mobile Money',
    'BANK' => 'Virement Bancaire',
    'CHEQUE' => 'Chèque',
    'OTHER' => 'Autre'
]);

// Invoice/Payment statuses
define('INVOICE_STATUSES', [
    'DUE' => 'À payer',
    'PARTIAL' => 'Partiel',
    'PAID' => 'Payé',
    'CANCELLED' => 'Annulé'
]);

// Student statuses
define('STUDENT_STATUSES', [
    'ACTIVE' => 'Actif',
    'LEFT' => 'Parti',
    'SUSPENDED' => 'Suspendu',
    'GRADUATED' => 'Diplômé'
]);

// Return config array for use in application
return $env;
