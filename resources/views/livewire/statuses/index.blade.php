<div>
    <x-page-header title="Statuts des prises en charge" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto space-y-6">
        @if (session('error'))
            <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ $editingId ? 'Modifier le statut' : 'Nouveau statut' }}
            </h3>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <x-input-label for="label" value="Libellé" />
                        <x-text-input id="label" type="text" class="mt-1 block w-full" wire:model="label" />
                        <x-input-error :messages="$errors->get('label')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="color" value="Couleur" />
                        <input id="color" type="color" wire:model="color" class="mt-1 block w-full h-10 rounded-md border-gray-300 dark:border-gray-700">
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="is_default" class="rounded border-gray-300 dark:border-gray-700">
                        Statut par défaut à la création
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="is_final" class="rounded border-gray-300 dark:border-gray-700">
                        Statut final
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3">
                    @if ($editingId)
                        <button type="button" wire:click="cancelEdit" class="text-sm text-gray-600 dark:text-gray-400">Annuler</button>
                    @endif
                    <x-primary-button>Enregistrer</x-primary-button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-x-auto">
            <p class="text-xs text-gray-500 dark:text-gray-400 px-4 pt-3">Glissez-déposez une ligne pour changer l'ordre des statuts.</p>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3"></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Options</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-data="{ dragId: null }">
                    @foreach ($statuses as $status)
                        <tr wire:key="status-{{ $status->id }}"
                            draggable="true"
                            @dragstart="dragId = {{ $status->id }}"
                            @dragover.prevent
                            @drop="$wire.moveStatus(dragId, {{ $status->id }})"
                            @dragend="dragId = null"
                            :class="dragId === {{ $status->id }} ? 'opacity-40' : ''"
                            class="cursor-move">
                            <td class="px-4 py-3 text-gray-400 dark:text-gray-500">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M7 4a1 1 0 11-2 0 1 1 0 012 0zM7 10a1 1 0 11-2 0 1 1 0 012 0zM7 16a1 1 0 11-2 0 1 1 0 012 0zM15 4a1 1 0 11-2 0 1 1 0 012 0zM15 10a1 1 0 11-2 0 1 1 0 012 0zM15 16a1 1 0 11-2 0 1 1 0 012 0z" />
                                </svg>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2 text-sm text-gray-900 dark:text-gray-100">
                                    <span class="h-3 w-3 rounded-full inline-block" style="background-color: {{ $status->color }}"></span>
                                    {{ $status->label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                @if ($status->is_default) <span class="text-xs text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">Par défaut</span> @endif
                                @if ($status->is_final) <span class="text-xs text-gray-500 dark:text-gray-400">Final</span> @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <button wire:click="edit({{ $status->id }})" class="text-sm text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">Modifier</button>
                                <button wire:click="delete({{ $status->id }})" wire:confirm="Supprimer ce statut ?" class="text-sm text-red-600 dark:text-red-400">Supprimer</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
