<div>
    <x-page-header title="Authentification à deux facteurs" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-xl mx-auto space-y-6">
        @if ($recoveryCodes)
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">2FA activée</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Conservez ces codes de récupération dans un endroit sûr. Chacun ne peut être utilisé qu'une seule fois si vous perdez l'accès à votre application d'authentification.
                </p>
                <div class="grid grid-cols-2 gap-2 font-mono text-sm bg-gray-50 dark:bg-gray-900 rounded-md p-3">
                    @foreach ($recoveryCodes as $rc)
                        <span>{{ $rc }}</span>
                    @endforeach
                </div>
            </div>
        @elseif (auth()->user()->hasTwoFactorEnabled())
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">2FA activée</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Votre compte est protégé par une authentification à deux facteurs.</p>

                <form wire:submit="disable" class="space-y-3">
                    <x-input-label for="disableCode" value="Entrez un code pour désactiver la 2FA" />
                    <x-text-input id="disableCode" type="text" inputmode="numeric" class="mt-1 block w-full" wire:model="disableCode" />
                    <x-input-error :messages="$errors->get('disableCode')" class="mt-2" />
                    <button class="text-sm text-red-600 dark:text-red-400">Désactiver la 2FA</button>
                </form>
            </div>
        @elseif ($pendingSecret)
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6 text-center">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Scannez ce QR code</h3>
                <img src="{{ $qrCodeUrl }}" alt="QR code 2FA" class="mx-auto mb-4" width="200" height="200">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 break-all">Clé manuelle : {{ $pendingSecret }}</p>

                <form wire:submit="confirm" class="space-y-3 text-left">
                    <x-input-label for="code" value="Code à 6 chiffres" />
                    <x-text-input id="code" type="text" inputmode="numeric" class="mt-1 block w-full" wire:model="code" autofocus />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    <x-primary-button class="w-full justify-center">Confirmer</x-primary-button>
                </form>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">2FA désactivée</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Ajoutez une couche de sécurité supplémentaire à votre compte administrateur avec une application d'authentification (Google Authenticator, Microsoft Authenticator…).
                </p>
                <x-primary-button wire:click="startSetup">Activer la 2FA</x-primary-button>
            </div>
        @endif
    </div>
</div>
