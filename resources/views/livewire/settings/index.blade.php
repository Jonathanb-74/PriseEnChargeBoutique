<div>
    <x-page-header title="Paramètres" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
        @endif

        <form wire:submit="save" class="space-y-6">
            {{-- APPARENCE --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6 space-y-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">Apparence</h3>

                <div>
                    <x-input-label value="Logo" />
                    <div class="mt-2 flex items-center gap-4">
                        @if ($newLogo)
                            <img src="{{ $newLogo->temporaryUrl() }}" class="h-12 w-auto max-w-[160px] object-contain bg-gray-50 dark:bg-gray-900 rounded-md p-1">
                        @elseif ($currentLogoUrl)
                            <img src="{{ $currentLogoUrl }}" class="h-12 w-auto max-w-[160px] object-contain bg-gray-50 dark:bg-gray-900 rounded-md p-1">
                        @else
                            <x-application-logo class="h-12 w-12 fill-current text-gray-400" />
                        @endif

                        <div class="space-y-1">
                            <input type="file" wire:model="newLogo" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                class="block text-sm text-gray-600 dark:text-gray-300">
                            <div wire:loading wire:target="newLogo" class="text-xs text-gray-500 dark:text-gray-400">Chargement…</div>

                            @if ($currentLogoUrl)
                                <button type="button" wire:click="removeLogo" wire:confirm="Revenir au logo par défaut ?" class="text-xs text-red-600 dark:text-red-400">
                                    Revenir au logo par défaut
                                </button>
                            @endif
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('newLogo')" class="mt-2" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">PNG, JPG, WebP ou SVG, 1 Mo maximum.</p>
                </div>

                <div>
                    <x-input-label for="accentColor" value="Couleur de mise en avant" />
                    <div class="mt-1 flex items-center gap-3">
                        <input id="accentColor" type="color" wire:model="accentColor" class="h-10 w-14 rounded-md border-gray-300 dark:border-gray-700 p-1 bg-white dark:bg-gray-900">
                        <x-text-input type="text" wire:model="accentColor" class="w-32" maxlength="7" />
                        <button type="button" wire:click="resetAccentColor" class="text-xs text-gray-500 dark:text-gray-400 underline">
                            Réinitialiser
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Utilisée pour les liens, les menus actifs et les mises en avant dans toute l'application.
                    </p>
                    <x-input-error :messages="$errors->get('accentColor')" class="mt-2" />
                </div>
            </div>

            {{-- MENTIONS / TARIFS --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Mentions et tarifs affichés avant la signature client</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Ce texte est affiché juste au-dessus de la signature du client lors de la prise en charge (tarifs minimum, conditions, mentions légales…) et repris sur la fiche PDF.
                </p>
                <textarea wire:model="intakeTermsText" rows="8"
                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]"></textarea>
                <x-input-error :messages="$errors->get('intakeTermsText')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <x-primary-button>Enregistrer</x-primary-button>
            </div>
        </form>
    </div>
</div>
