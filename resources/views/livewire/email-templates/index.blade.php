<div>
    <x-page-header title="Modèles d'email" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Modèle de signature</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Ajoutée en bas de chaque email envoyé au client (notifications et confirmation de prise en charge), à la place de la formule par défaut.
            </p>
            <form wire:submit="saveSignature" class="space-y-4">
                <div>
                    <textarea id="emailSignature" wire:model="emailSignature" rows="4"
                        placeholder="Cordialement,&#10;{{ config('app.name') }}"
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]"></textarea>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Laissez vide pour utiliser la formule par défaut ("Cordialement," suivi du titre du site).
                    </p>
                    <x-input-error :messages="$errors->get('emailSignature')" class="mt-2" />
                </div>
                <div class="flex justify-end">
                    <x-primary-button>Enregistrer la signature</x-primary-button>
                </div>
            </form>
        </div>

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
                    <x-input-label for="email_title" value="Titre affiché dans l'email (facultatif)" />
                    <x-text-input id="email_title" type="text" class="mt-1 block w-full" wire:model="email_title" placeholder="{{ config('app.name') }}" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Affiché en en-tête et en pied de page de l'email. Laissez vide pour utiliser le titre du site ({{ config('app.name') }}).
                    </p>
                    <x-input-error :messages="$errors->get('email_title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="body" value="Message" />
                    <textarea id="body" wire:model="body" rows="5"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]"></textarea>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Variables disponibles : <code>&#123;&#123;reference&#125;&#125;</code>, <code>&#123;&#123;client&#125;&#125;</code>, <code>&#123;&#123;machine&#125;&#125;</code>, <code>&#123;&#123;statut&#125;&#125;</code>, <code>&#123;&#123;panne&#125;&#125;</code>
                    </p>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 dark:border-gray-700">
                    Actif
                </label>

                <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="attach_pdf" class="rounded border-gray-300 dark:border-gray-700 mt-0.5">
                    <span>
                        Joindre la fiche de prise en charge en PDF
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Version sans mot de passe machine, destinée au client.</span>
                    </span>
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
                        @if ($template->email_title)
                            <div class="text-xs text-gray-400">Titre : {{ $template->email_title }}</div>
                        @endif
                        @unless ($template->is_active)
                            <span class="text-xs text-gray-400">Inactif</span>
                        @endunless
                        @if ($template->attach_pdf)
                            <span class="text-xs text-gray-400">· Fiche PDF jointe</span>
                        @endif
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
