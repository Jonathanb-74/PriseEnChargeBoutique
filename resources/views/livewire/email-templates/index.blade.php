<div>
    <x-page-header title="Modèles d'email" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ $editingId ? 'Modifier le modèle' : 'Nouveau modèle' }}
            </h3>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <x-input-label for="name" value="Nom (interne)" />
                    <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="subject" value="Sujet" />
                    <x-text-input id="subject" type="text" class="mt-1 block w-full" wire:model="subject" />
                    <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="body" value="Message" />
                    <textarea id="body" wire:model="body" rows="5"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]"></textarea>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Variables disponibles : <code>&#123;&#123;reference&#125;&#125;</code>, <code>&#123;&#123;client&#125;&#125;</code>, <code>&#123;&#123;machine&#125;&#125;</code>, <code>&#123;&#123;statut&#125;&#125;</code>
                    </p>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 dark:border-gray-700">
                    Actif
                </label>

                <div class="flex items-center justify-end gap-3">
                    @if ($editingId)
                        <button type="button" wire:click="cancelEdit" class="text-sm text-gray-600 dark:text-gray-400">Annuler</button>
                    @endif
                    <x-primary-button>Enregistrer</x-primary-button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($templates as $template)
                <div class="p-4 flex items-center justify-between" wire:key="template-{{ $template->id }}">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $template->subject }}</div>
                        @unless ($template->is_active)
                            <span class="text-xs text-gray-400">Inactif</span>
                        @endunless
                    </div>
                    <div class="space-x-3">
                        <button wire:click="edit({{ $template->id }})" class="text-sm text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">Modifier</button>
                        <button wire:click="delete({{ $template->id }})" wire:confirm="Supprimer ce modèle ?" class="text-sm text-red-600 dark:text-red-400">Supprimer</button>
                    </div>
                </div>
            @empty
                <p class="p-4 text-sm text-gray-500 dark:text-gray-400">Aucun modèle.</p>
            @endforelse
        </div>
    </div>
</div>
