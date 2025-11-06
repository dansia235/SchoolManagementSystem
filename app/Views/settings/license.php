<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestion de la licence</h1>
        <p class="mt-1 text-sm text-gray-500">
            Activez votre licence annuelle pour utiliser EduChad
        </p>
    </div>

    <!-- License Status -->
    <div class="bg-white shadow rounded-lg p-6">
        <?php if ($license_status['valid']): ?>
            <div class="flex items-center space-x-3">
                <svg class="h-12 w-12 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-lg font-medium text-green-900">Licence active</h3>
                    <p class="text-sm text-green-700">
                        <?= e($license_status['message']) ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="flex items-center space-x-3">
                <svg class="h-12 w-12 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-lg font-medium text-red-900">Licence invalide</h3>
                    <p class="text-sm text-red-700">
                        <?= e($license_status['message']) ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">École:</span>
                <span class="ml-2 font-medium text-gray-900"><?= e($license_status['school_name']) ?></span>
            </div>
            <div>
                <span class="text-gray-500">Expire le:</span>
                <span class="ml-2 font-medium text-gray-900">
                    <?= $license_status['license_until'] ? fmt_date($license_status['license_until']) : '-' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- License Form -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Activer/Renouveler la licence</h3>

        <form method="POST" action="index.php?page=settings.license" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div>
                <label for="license_key" class="block text-sm font-medium text-gray-700">
                    Clé de licence
                </label>
                <textarea id="license_key" name="license_key" rows="3" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                          placeholder="Collez votre clé de licence ici"></textarea>
                <p class="mt-1 text-xs text-gray-500">
                    La clé vous a été fournie par votre éditeur
                </p>
            </div>

            <div>
                <label for="license_until" class="block text-sm font-medium text-gray-700">
                    Date d'expiration
                </label>
                <input type="date" id="license_until" name="license_until" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">
                    Format: AAAA-MM-JJ (exemple: 2025-08-31)
                </p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Activer la licence
                </button>
            </div>
        </form>
    </div>

    <!-- Instructions -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-blue-900 mb-2">Comment obtenir une licence ?</h3>
        <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
            <li>Contactez votre éditeur/fournisseur d'EduChad</li>
            <li>Fournissez le nom exact de votre école: <span class="font-mono font-semibold"><?= e($license_status['school_name']) ?></span></li>
            <li>Recevez votre clé de licence et la date d'expiration</li>
            <li>Collez-les dans le formulaire ci-dessus</li>
        </ol>
        <p class="mt-4 text-xs text-blue-700">
            ℹ️ La licence est validée offline (sans connexion Internet) et est liée au nom de votre école.
        </p>
    </div>

    <!-- Technical Info -->
    <details class="bg-gray-50 rounded-lg p-4">
        <summary class="text-sm font-medium text-gray-700 cursor-pointer">
            Informations techniques
        </summary>
        <div class="mt-4 space-y-2 text-xs text-gray-600 font-mono">
            <div>École: <?= e($license_status['school_name']) ?></div>
            <?php if (!empty($license_status['license_key'])): ?>
                <div>Clé: <?= e(substr($license_status['license_key'], 0, 20)) ?>...</div>
            <?php endif; ?>
            <div>Version: <?= APP_VERSION ?></div>
        </div>
    </details>
</div>
