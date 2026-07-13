<div>
    <x-page-header :title="$client ? 'Modifier le client' : 'Nouveau client'" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="type" value="Type de client" />
                        <select id="type" wire:model="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))] rounded-md shadow-sm">
                            <option value="particulier">Particulier</option>
                            <option value="pro">Professionnel</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    @if ($type === 'pro')
                        <div>
                            <x-input-label for="company_name" value="Société" />
                            <x-text-input id="company_name" type="text" class="mt-1 block w-full" wire:model="company_name" />
                            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="first_name" value="Prénom" />
                        <x-text-input id="first_name" type="text" class="mt-1 block w-full" wire:model="first_name" autofocus />
                        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="last_name" value="Nom" />
                        <x-text-input id="last_name" type="text" class="mt-1 block w-full" wire:model="last_name" />
                        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" type="email" class="mt-1 block w-full" wire:model="email" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Téléphone" />
                        <x-text-input id="phone" type="text" class="mt-1 block w-full" wire:model="phone" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="address_line1" value="Adresse" />
                    <x-text-input id="address_line1" type="text" class="mt-1 block w-full" wire:model="address_line1" />
                    <x-input-error :messages="$errors->get('address_line1')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="address_line2" value="Complément d'adresse" />
                    <x-text-input id="address_line2" type="text" class="mt-1 block w-full" wire:model="address_line2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="postal_code" value="Code postal" />
                        <x-text-input id="postal_code" type="text" class="mt-1 block w-full" wire:model="postal_code" />
                        <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="city" value="Ville" />
                        <x-text-input id="city" type="text" class="mt-1 block w-full" wire:model="city" />
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ $client ? route('clients.show', $client) : route('clients.index') }}" wire:navigate class="text-sm text-gray-600 dark:text-gray-400">
                        Annuler
                    </a>
                    <x-primary-button>Enregistrer</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
