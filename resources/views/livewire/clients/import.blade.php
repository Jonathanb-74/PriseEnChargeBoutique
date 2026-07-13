<div>
    <x-page-header title="Importer des clients" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-4">
        @if ($result)
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                Import terminé : {{ $result['created'] }} client(s) créé(s), {{ $result['updated'] }} mis à jour.
            </div>
            <a href="{{ route('clients.index') }}" wire:navigate class="text-sm text-[rgb(var(--color-accent))]">
                Retour à la liste des clients
            </a>
        @else
            @if ($error)
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                    {{ $error }}
                </div>
            @endif

            @if (empty($rows))
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Sélectionnez un fichier CSV (séparateur point-virgule) avec les colonnes : Code, Raison sociale, CP, Ville, Adresse1, Adresse2, Tél, E-mail, SIRET, Actif.
                        La colonne Fax n'est pas importée. Chaque client est importé en tant que professionnel (société = Raison sociale) ; si un code client existe déjà, le client correspondant est mis à jour.
                    </p>
                    <form wire:submit="parse" class="space-y-4">
                        <div>
                            <input type="file" wire:model="file" accept=".csv,.txt" class="block w-full text-sm text-gray-600 dark:text-gray-400" />
                            <div wire:loading wire:target="file" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Chargement du fichier…</div>
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        </div>
                        <div class="flex justify-end">
                            <x-primary-button wire:loading.attr="disabled" wire:target="parse">Analyser le fichier</x-primary-button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ count($rows) }} ligne(s) prête(s) à être importée(s).
                    </p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Société</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ville</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Téléphone</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($rows as $row)
                                    <tr>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['code_client'] }}</td>
                                        <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $row['company_name'] }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['city'] }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['phone'] }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['actif'] ? 'Oui' : 'Non' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" wire:click="cancel" class="text-sm text-gray-600 dark:text-gray-400">
                            Annuler
                        </button>
                        <x-primary-button wire:click="import" wire:loading.attr="disabled" wire:target="import">
                            Confirmer l'import ({{ count($rows) }})
                        </x-primary-button>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
