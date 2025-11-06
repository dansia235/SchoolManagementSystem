<aside class="w-64 bg-white border-r border-gray-200 overflow-y-auto">
    <nav class="p-4 space-y-1">
        <!-- Dashboard -->
        <a href="index.php?page=dashboard" class="flex items-center px-3 py-2 text-sm font-medium rounded-md <?= is_page('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Tableau de bord
        </a>

        <!-- Students -->
        <?php if (Auth::hasRole(['ADMIN', 'TEACHER', 'CASHIER', 'VIEWER'])): ?>
            <a href="index.php?page=students" class="flex items-center px-3 py-2 text-sm font-medium rounded-md <?= str_starts_with(current_page(), 'students') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Élèves
            </a>
        <?php endif; ?>

        <!-- Grades -->
        <?php if (Auth::hasRole(['ADMIN', 'TEACHER'])): ?>
            <a href="index.php?page=grades" class="flex items-center px-3 py-2 text-sm font-medium rounded-md <?= str_starts_with(current_page(), 'grades') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Notes
            </a>
        <?php endif; ?>

        <!-- Billing -->
        <?php if (Auth::hasRole(['ADMIN', 'CASHIER'])): ?>
            <a href="index.php?page=billing" class="flex items-center px-3 py-2 text-sm font-medium rounded-md <?= str_starts_with(current_page(), 'billing') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Facturation
            </a>
        <?php endif; ?>

        <!-- Cashbook -->
        <?php if (Auth::hasRole(['ADMIN', 'CASHIER'])): ?>
            <a href="index.php?page=cash" class="flex items-center px-3 py-2 text-sm font-medium rounded-md <?= str_starts_with(current_page(), 'cash') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Caisse
            </a>
        <?php endif; ?>

        <!-- Reports -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Rapports
            </p>

            <a href="index.php?page=reports.arrears" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 mt-2">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Impayés
            </a>

            <a href="index.php?page=reports.class_list" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Listes de classe
            </a>
        </div>

        <!-- Settings -->
        <?php if (Auth::hasRole(['ADMIN'])): ?>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Administration
                </p>

                <a href="index.php?page=settings.general" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 mt-2">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Paramètres
                </a>

                <a href="index.php?page=settings.users" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Utilisateurs
                </a>

                <a href="index.php?page=settings.license" class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    Licence
                </a>
            </div>
        <?php endif; ?>
    </nav>
</aside>
