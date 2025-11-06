<?php
/**
 * EduChad Helper Functions
 *
 * Global utility functions used throughout the application
 */

/**
 * Generate CSRF token
 */
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Check CSRF token validity
 */
function csrf_check($token) {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/**
 * Set flash message
 */
function flash($key, $value = null) {
    if ($value === null) {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    $_SESSION['flash'][$key] = $value;
}

/**
 * Format money (number)
 */
function fmt_money($amount) {
    return number_format((float)$amount, 0, ',', ' ');
}

/**
 * Format date
 */
function fmt_date($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Format datetime
 */
function fmt_datetime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) return '';
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

/**
 * Sanitize output (XSS prevention)
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a page
 */
function redirect($page, $params = []) {
    $query = http_build_query(array_merge(['page' => $page], $params));
    header('Location: index.php?' . $query);
    exit;
}

/**
 * Get current page
 */
function current_page() {
    return $_GET['page'] ?? 'dashboard';
}

/**
 * Check if current page matches
 */
function is_page($page) {
    return current_page() === $page;
}

/**
 * Generate a unique matricule for students
 */
function generate_matricule($year = null) {
    if ($year === null) {
        $year = date('Y');
    }

    $st = DB::pdo()->query("SELECT COUNT(*) FROM students WHERE matricule LIKE '$year%'");
    $count = $st->fetchColumn();

    return $year . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate invoice number
 */
function generate_invoice_number() {
    $year = date('Y');
    $st = DB::pdo()->query("SELECT COUNT(*) FROM invoices WHERE invoice_number LIKE 'INV-$year-%'");
    $count = $st->fetchColumn();

    return 'INV-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate payment number
 */
function generate_payment_number() {
    $year = date('Y');
    $st = DB::pdo()->query("SELECT COUNT(*) FROM payments WHERE payment_number LIKE 'PAY-$year-%'");
    $count = $st->fetchColumn();

    return 'PAY-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate transaction number for cashbook
 */
function generate_transaction_number() {
    $year = date('Y');
    $st = DB::pdo()->query("SELECT COUNT(*) FROM cashbook WHERE transaction_number LIKE 'TRANS-$year-%'");
    $count = $st->fetchColumn();

    return 'TRANS-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

/**
 * Log activity (optional audit trail)
 */
function log_activity($action, $entity_type = null, $entity_id = null, $description = null) {
    $user = Auth::user();
    $user_id = $user ? $user['id'] : null;

    $st = DB::pdo()->prepare('
        INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    $st->execute([
        $user_id,
        $action,
        $entity_type,
        $entity_id,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

/**
 * Get setting value
 */
function setting($key, $default = null) {
    static $cache = [];

    if (!isset($cache[$key])) {
        $st = DB::pdo()->prepare('SELECT v FROM settings WHERE k = ?');
        $st->execute([$key]);
        $cache[$key] = $st->fetchColumn() ?: $default;
    }

    return $cache[$key];
}

/**
 * Update setting value
 */
function update_setting($key, $value) {
    $st = DB::pdo()->prepare('REPLACE INTO settings (k, v) VALUES (?, ?)');
    return $st->execute([$key, $value]);
}

/**
 * Get current academic year
 */
function academic_year() {
    return setting('academic_year', date('Y') . '-' . (date('Y') + 1));
}

/**
 * Calculate age from birthdate
 */
function calculate_age($birthdate) {
    if (empty($birthdate)) return null;

    $birth = new DateTime($birthdate);
    $now = new DateTime();
    $age = $now->diff($birth);

    return $age->y;
}

/**
 * Upload file
 */
function upload_file($file, $destination_folder = 'uploads') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        flash('error', 'Fichier trop volumineux (max ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB)');
        return false;
    }

    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
        flash('error', 'Type de fichier non autorisé');
        return false;
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = PUBLIC_PATH . '/' . $destination_folder . '/' . $filename;

    // Create directory if it doesn't exist
    $dir = dirname($destination);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $destination_folder . '/' . $filename;
    }

    return false;
}

/**
 * Delete file
 */
function delete_file($file_path) {
    $full_path = PUBLIC_PATH . '/' . $file_path;
    if (file_exists($full_path)) {
        return unlink($full_path);
    }
    return false;
}

/**
 * Format percentage
 */
function fmt_percent($value, $decimals = 2) {
    return number_format((float)$value, $decimals, ',', ' ') . '%';
}

/**
 * Get grade label based on average
 */
function grade_label($average) {
    if ($average >= 18) return 'Excellent';
    if ($average >= 16) return 'Très bien';
    if ($average >= 14) return 'Bien';
    if ($average >= 12) return 'Assez bien';
    if ($average >= 10) return 'Passable';
    return 'Insuffisant';
}

/**
 * Paginate query results
 */
function paginate($total_items, $current_page = 1, $per_page = null) {
    if ($per_page === null) {
        $per_page = ITEMS_PER_PAGE;
    }

    $total_pages = ceil($total_items / $per_page);
    $current_page = max(1, min($total_pages, (int)$current_page));
    $offset = ($current_page - 1) * $per_page;

    return [
        'total_items' => $total_items,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number
 */
function is_valid_phone($phone) {
    // Simple validation for Chadian phone numbers
    return preg_match('/^[+]?[0-9]{8,15}$/', str_replace([' ', '-', '(', ')'], '', $phone));
}

/**
 * Debug helper
 */
function dd(...$vars) {
    if (!DEBUG) return;

    echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:20px;border-radius:5px;margin:20px;">';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    die();
}
