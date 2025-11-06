<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?= e(setting('school_name')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <!-- Background decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-white opacity-10 rounded-full blur-3xl float-animation"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl float-animation" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Login Card -->
        <div class="login-card rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6 text-center">
                <?php if (setting('school_logo')): ?>
                    <img src="/<?= e(setting('school_logo')) ?>" alt="Logo" class="mx-auto h-16 w-auto mb-3">
                <?php else: ?>
                    <div class="w-16 h-16 mx-auto mb-3 bg-white rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                <?php endif; ?>
                <h1 class="text-2xl font-bold text-white mb-1">
                    <?= e(setting('school_name')) ?>
                </h1>
                <p class="text-blue-100 text-sm">
                    Système de Gestion Scolaire
                </p>
            </div>

            <!-- Form -->
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                    Connexion
                </h2>

                <!-- Alerts -->
                <?php if ($success = flash('success')): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                        <p class="text-sm text-green-800"><?= e($success) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error = flash('error')): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                        <p class="text-sm text-red-800"><?= e($error) ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?page=login" class="space-y-5">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Adresse email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input type="email" id="email" name="email" required
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                   placeholder="votre@email.com"
                                   value="">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Mot de passe
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Se connecter
                    </button>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-600 text-center mb-3 font-semibold">
                        Identifiants de démonstration:
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                            <p class="text-xs font-semibold text-blue-900 mb-1">👨‍💼 Admin</p>
                            <p class="text-xs text-blue-700 font-mono">admin@educhad.local</p>
                            <p class="text-xs text-blue-700 font-mono">Admin@123</p>
                        </div>
                        <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                            <p class="text-xs font-semibold text-green-900 mb-1">👨‍🏫 Enseignant</p>
                            <p class="text-xs text-green-700 font-mono">teacher@educhad.local</p>
                            <p class="text-xs text-green-700 font-mono">Admin@123</p>
                        </div>
                        <div class="bg-purple-50 p-3 rounded-lg border border-purple-200">
                            <p class="text-xs font-semibold text-purple-900 mb-1">💰 Caissier</p>
                            <p class="text-xs text-purple-700 font-mono">cashier@educhad.local</p>
                            <p class="text-xs text-purple-700 font-mono">Admin@123</p>
                        </div>
                        <div class="bg-orange-50 p-3 rounded-lg border border-orange-200">
                            <p class="text-xs font-semibold text-orange-900 mb-1">👁️ Observateur</p>
                            <p class="text-xs text-orange-700 font-mono text-[10px]">viewer@educhad.local</p>
                            <p class="text-xs text-orange-700 font-mono">Admin@123</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-white text-sm opacity-90">
                EduChad v<?= APP_VERSION ?> - Système 100% Offline
            </p>
            <p class="text-white text-xs opacity-75 mt-1">
                © 2024-2025 Tous droits réservés
            </p>
        </div>
    </div>
</body>
</html>
