<div class="space-y-6">
    <!-- License Warning -->
    <?php if (!$license_status['valid']): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <?= e($license_status['message']) ?>
                        <a href="index.php?page=settings.license" class="font-medium underline">
                            Activer la licence →
                        </a>
                    </p>
                </div>
            </div>
        </div>
    <?php elseif ($license_status['days_remaining'] <= 30): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <?= e($license_status['message']) ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>
        <p class="mt-1 text-sm text-gray-500">
            Bienvenue, <?= e(Auth::user()['name']) ?> | Année académique <?= e(academic_year()) ?>
        </p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Students -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Élèves actifs
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                <?= number_format($student_stats['active']) ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="index.php?page=students" class="font-medium text-blue-600 hover:text-blue-500">
                        Voir tous →
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Encaissements
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                <?= fmt_money($invoice_stats['total_paid']) ?> FCFA
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="index.php?page=billing" class="font-medium text-blue-600 hover:text-blue-500">
                        Voir détails →
                    </a>
                </div>
            </div>
        </div>

        <!-- Outstanding -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Impayés
                            </dt>
                            <dd class="text-2xl font-semibold text-red-600">
                                <?= fmt_money($invoice_stats['total_outstanding']) ?> FCFA
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="index.php?page=reports.arrears" class="font-medium text-blue-600 hover:text-blue-500">
                        Voir liste →
                    </a>
                </div>
            </div>
        </div>

        <!-- Cash Balance -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Solde caisse
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                <?= fmt_money($cashbook_balance['balance']) ?> FCFA
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="index.php?page=cash" class="font-medium text-blue-600 hover:text-blue-500">
                        Journal de caisse →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Payments -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Paiements récents</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (empty($recent_payments)): ?>
                    <div class="px-5 py-4 text-sm text-gray-500">
                        Aucun paiement récent
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_payments as $payment): ?>
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= e($payment['first_name'] . ' ' . $payment['last_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?= fmt_datetime($payment['paid_at']) ?>
                                    </p>
                                </div>
                                <span class="text-sm font-semibold text-green-600">
                                    +<?= fmt_money($payment['amount']) ?> FCFA
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Students -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Nouveaux élèves</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (empty($recent_students)): ?>
                    <div class="px-5 py-4 text-sm text-gray-500">
                        Aucun nouvel élève
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_students as $student): ?>
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Matricule: <?= e($student['matricule']) ?>
                                    </p>
                                </div>
                                <a href="index.php?page=students.show&id=<?= $student['id'] ?>"
                                   class="text-sm text-blue-600 hover:text-blue-800">
                                    Voir →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
