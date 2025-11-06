<?php
/**
 * EduChad Front Controller
 *
 * This is the single entry point for all requests
 */

// Load bootstrap
require_once __DIR__ . '/../app/bootstrap.php';

// Get requested page
$page = $_GET['page'] ?? 'dashboard';

// Route map: page => [Controller, method]
$routes = [
    // Authentication
    'login' => ['AuthController', 'login'],
    'logout' => ['AuthController', 'logout'],

    // Dashboard
    'dashboard' => ['DashboardController', 'index'],

    // Students
    'students' => ['StudentController', 'index'],
    'students.create' => ['StudentController', 'create'],
    'students.store' => ['StudentController', 'store'],
    'students.show' => ['StudentController', 'show'],
    'students.edit' => ['StudentController', 'edit'],
    'students.update' => ['StudentController', 'update'],
    'students.delete' => ['StudentController', 'delete'],

    // Grades
    'grades' => ['GradeController', 'index'],
    'grades.entry' => ['GradeController', 'entry'],
    'grades.store' => ['GradeController', 'store'],
    'grades.coefficients' => ['GradeController', 'coefficients'],

    // Billing
    'billing' => ['BillingController', 'index'],
    'billing.show' => ['BillingController', 'show'],
    'billing.create' => ['BillingController', 'create'],
    'billing.store' => ['BillingController', 'store'],
    'billing.payment' => ['BillingController', 'payment'],

    // Cash
    'cash' => ['CashController', 'index'],
    'cash.income' => ['CashController', 'newIncome'],
    'cash.expense' => ['CashController', 'newExpense'],
    'cash.export' => ['CashController', 'export'],

    // Reports
    'reports.card' => ['ReportController', 'reportCard'],
    'reports.arrears' => ['ReportController', 'arrears'],
    'reports.ledger' => ['ReportController', 'studentLedger'],
    'reports.class_list' => ['ReportController', 'classList'],

    // Settings
    'settings.general' => ['SettingController', 'general'],
    'settings.license' => ['SettingController', 'license'],
    'settings.users' => ['SettingController', 'users'],
    'settings.users.create' => ['SettingController', 'createUser'],
    'settings.users.edit' => ['SettingController', 'editUser'],
    'settings.users.delete' => ['SettingController', 'deleteUser'],
    'settings.profile' => ['SettingController', 'profile'],
];

// Check license validity (except for login and license pages)
if (!License::valid() && !in_array($page, ['login', 'logout', 'settings.license'])) {
    $page = 'settings.license';
}

// Redirect to login if not authenticated (except for public pages)
$public_pages = ['login', 'logout'];
if (!in_array($page, $public_pages) && Auth::guest()) {
    flash('error', 'Vous devez être connecté pour accéder à cette page.');
    $page = 'login';
}

// Check if route exists
if (!isset($routes[$page])) {
    http_response_code(404);
    die('
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Page non trouvée - EduChad</title>
            <style>
                body { font-family: -apple-system, sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; text-align: center; }
                h1 { color: #dc2626; font-size: 3em; margin: 0; }
                p { color: #6b7280; font-size: 1.1em; }
                a { color: #0284c7; text-decoration: none; }
            </style>
        </head>
        <body>
            <h1>404</h1>
            <p>Page non trouvée</p>
            <p><a href="index.php?page=dashboard">← Retour au tableau de bord</a></p>
        </body>
        </html>
    ');
}

// Load controller and execute method
[$controller_name, $method] = $routes[$page];
$controller_file = APP_PATH . '/Controllers/' . $controller_name . '.php';

if (!file_exists($controller_file)) {
    die('Controller not found: ' . $controller_name);
}

require_once $controller_file;

try {
    $controller = new $controller_name();
    echo $controller->$method();
} catch (Exception $e) {
    if (DEBUG) {
        die('Error: ' . $e->getMessage() . '<br><pre>' . $e->getTraceAsString() . '</pre>');
    } else {
        error_log('Application Error: ' . $e->getMessage());
        die('
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Erreur - EduChad</title>
                <style>
                    body { font-family: sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; text-align: center; }
                    h1 { color: #dc2626; }
                </style>
            </head>
            <body>
                <h1>Une erreur est survenue</h1>
                <p>Veuillez contacter l\'administrateur.</p>
                <p><a href="index.php?page=dashboard">← Retour au tableau de bord</a></p>
            </body>
            </html>
        ');
    }
}
