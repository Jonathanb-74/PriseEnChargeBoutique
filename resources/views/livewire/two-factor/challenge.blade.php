<div>
    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Vérification en deux étapes</h2>

    <form wire:submit="verify" class="space-y-4">
        @if ($useRecoveryCode)
            <div>
                <x-input-label for="code" value="Code de récupération" />
                <x-text-input id="code" type="text" class="mt-1 block w-full" wire:model="code" autofocus />
            </div>
        @else
            <div>
                <x-input-label for="code" value="Code de l'application d'authentification" />
                <x-text-input id="code" type="text" inputmode="numeric" class="mt-1 block w-full" wire:model="code" autofocus />
            </div>
        @endif
        <x-input-error :messages="$errors->get('code')" class="mt-2" />

        <div class="flex items-center justify-between">
            <button type="button" wire:click="$set('useRecoveryCode', {{ $useRecoveryCode ? 'false' : 'true' }})" class="text-sm text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">
                {{ $useRecoveryCode ? "Utiliser l'application d'authentification" : 'Utiliser un code de récupération' }}
            </button>
            <x-primary-button>Vérifier</x-primary-button>
        </div>
    </form>
</div>
