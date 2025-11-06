<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between items-center">
            <div class="flex items-center">
                <?php if (setting('school_logo')): ?>
                    <img src="/<?= e(setting('school_logo')) ?>" alt="Logo" class="h-10 w-auto">
                <?php endif; ?>
                <h1 class="ml-4 text-xl font-bold text-gray-900">
                    <?= e(setting('school_name')) ?>
                </h1>
            </div>

            <div class="flex items-center space-x-4">
                <!-- License Warning -->
                <?php if (License::expiringSoon()): ?>
                    <a href="index.php?page=settings.license" class="text-sm text-orange-600 hover:text-orange-800">
                        ⚠️ Licence expire bientôt
                    </a>
                <?php endif; ?>

                <!-- User Menu -->
                <div class="relative group">
                    <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                        <span class="text-sm font-medium"><?= e(Auth::user()['name']) ?></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                        <div class="px-4 py-2 text-xs text-gray-500 border-b">
                            <?= e(ROLES[Auth::role()]) ?>
                        </div>
                        <a href="index.php?page=settings.profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Mon profil
                        </a>
                        <a href="index.php?page=logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                            Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
