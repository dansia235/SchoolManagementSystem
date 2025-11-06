<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'EduChad') ?> - <?= e(setting('school_name')) ?></title>

    <?php
    // Load theme CSS variables
    $theme = Setting::getTheme();
    if ($theme && !empty($theme['css_vars'])) {
        $vars = json_decode($theme['css_vars'], true);
        echo '<style>:root {';
        foreach ($vars as $key => $value) {
            echo $key . ':' . $value . ';';
        }
        echo '}</style>';
    }
    ?>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/assets/css/app.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-gray-50">
    <div class="min-h-full">
        <?php if (Auth::check()): ?>
            <!-- Top Navigation -->
            <?php include __DIR__ . '/partials/topnav.php'; ?>

            <div class="flex h-[calc(100vh-4rem)]">
                <!-- Sidebar -->
                <?php include __DIR__ . '/partials/sidebar.php'; ?>

                <!-- Main Content -->
                <main class="flex-1 overflow-y-auto p-6">
                    <?php include __DIR__ . '/partials/alerts.php'; ?>

                    <?= $content ?>
                </main>
            </div>
        <?php else: ?>
            <!-- Content for non-authenticated pages -->
            <?php include __DIR__ . '/partials/alerts.php'; ?>
            <?= $content ?>
        <?php endif; ?>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
