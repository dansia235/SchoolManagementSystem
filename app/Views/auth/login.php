<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <?php if (setting('school_logo')): ?>
                <img src="/<?= e(setting('school_logo')) ?>" alt="Logo" class="mx-auto h-20 w-auto">
            <?php endif; ?>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                <?= e(setting('school_name')) ?>
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Système de gestion scolaire
            </p>
        </div>

        <form class="mt-8 space-y-6" method="POST" action="index.php?page=login">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input id="email" name="email" type="email" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="Email"
                           value="">
                </div>
                <div>
                    <label for="password" class="sr-only">Mot de passe</label>
                    <input id="password" name="password" type="password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="Mot de passe">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    Se connecter
                </button>
            </div>

            <div class="text-center text-sm text-gray-600">
                <p>Identifiants par défaut:</p>
                <p class="font-mono mt-1">admin@educhad.local / Admin@123</p>
            </div>
        </form>

        <div class="text-center text-xs text-gray-500">
            <p>EduChad v<?= APP_VERSION ?> - Système 100% offline</p>
        </div>
    </div>
</div>
