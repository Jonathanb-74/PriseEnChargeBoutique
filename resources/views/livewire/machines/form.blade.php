<div>
    <x-page-header :title="$machine ? 'Modifier la machine' : 'Nouvelle machine'" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto space-y-4">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Client : <a href="{{ route('clients.show', $client) }}" wire:navigate class="text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">{{ $client->full_name }}</a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="brand" value="Marque" />
                        <x-text-input id="brand" type="text" class="mt-1 block w-full" wire:model="brand" autofocus />
                        <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="model" value="Modèle" />
                        <x-text-input id="model" type="text" class="mt-1 block w-full" wire:model="model" />
                        <x-input-error :messages="$errors->get('model')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="serial_number" value="Numéro de série" />
                    <x-text-input id="serial_number" type="text" class="mt-1 block w-full" wire:model="serial_number" />
                    <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                </div>

                <div x-data="{ show: false }">
                    <x-input-label for="password" value="Mot de passe de la machine" />
                    <div class="mt-1 relative">
                        <input :type="show ? 'text' : 'password'" id="password" wire:model="password"
                            class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))] rounded-md shadow-sm pr-16" />
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 text-xs text-gray-500 dark:text-gray-400">
                            <span x-text="show ? 'Masquer' : 'Afficher'"></span>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Stocké chiffré, visible uniquement sur la fiche de prise en charge.</p>
                </div>

                <div>
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" wire:model="notes" rows="3"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))] rounded-md shadow-sm"></textarea>
                </div>

                <div>
                    <x-input-label for="newPhotos" value="Ajouter des photos" />
                    <input type="file" id="newPhotos" wire:model="newPhotos" multiple accept="image/png,image/jpeg,image/webp"
                        class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300" />
                    <div wire:loading wire:target="newPhotos" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Chargement…</div>
                    <x-input-error :messages="$errors->get('newPhotos')" class="mt-2" />
                    <x-input-error :messages="$errors->get('newPhotos.*')" class="mt-2" />

                    @if ($newPhotos)
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-3">
                            @foreach ($newPhotos as $photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-full object-cover rounded-md">
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($machine && $machine->photos->isNotEmpty())
                    <div>
                        <x-input-label value="Photos existantes" />
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-2">
                            @foreach ($machine->photos as $photo)
                                <div class="relative group" wire:key="photo-{{ $photo->id }}">
                                    <img src="{{ $photo->viewUrl() }}" class="h-20 w-full object-cover rounded-md">
                                    <button type="button" wire:click="deletePhoto({{ $photo->id }})" wire:confirm="Supprimer cette photo ?"
                                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full h-5 w-5 text-xs leading-5 text-center">
                                        ×
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('clients.show', $client) }}" wire:navigate class="text-sm text-gray-600 dark:text-gray-400">
                        Annuler
                    </a>
                    <x-primary-button>Enregistrer</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
