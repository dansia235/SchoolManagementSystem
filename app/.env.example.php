<?php
/**
 * EduChad Environment Configuration
 *
 * Copy this file to .env.php and update with your settings
 * IMPORTANT: Never commit .env.php to version control
 */

return [
    // Database Configuration
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'educhad',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_PORT' => '3306',

    // Application Secret (Change this to a long random string!)
    // Use: openssl rand -hex 32
    'APP_SECRET' => 'change_me_to_a_very_long_random_secret_key_at_least_64_chars',

    // Application URL
    'APP_URL' => 'http://localhost:8000',
    'APP_NAME' => 'EduChad',

    // Timezone
    'TIMEZONE' => 'Africa/Ndjamena',

    // Session Configuration
    'SESSION_LIFETIME' => 7200, // 2 hours in seconds
    'SESSION_NAME' => 'educhad_session',

    // Security
    'CSRF_TOKEN_NAME' => 'csrf_token',
    'PASSWORD_MIN_LENGTH' => 8,

    // File Upload
    'MAX_UPLOAD_SIZE' => 2097152, // 2MB in bytes
    'ALLOWED_IMAGE_TYPES' => ['image/jpeg', 'image/png', 'image/gif'],

    // Pagination
    'ITEMS_PER_PAGE' => 20,

    // Debug Mode (set to false in production)
    'DEBUG' => true,

    // Logging
    'LOG_LEVEL' => 'error', // error, warning, info, debug
    'LOG_FILE' => __DIR__ . '/../storage/logs/app.log',
];
