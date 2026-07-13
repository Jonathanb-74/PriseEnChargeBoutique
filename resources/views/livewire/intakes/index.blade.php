<div>
    <x-page-header title="Prises en charge">
        <x-slot name="actions">
            <a href="{{ route('intakes.create') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md text-xs font-semibold text-white dark:text-gray-800 uppercase tracking-widest">
                Nouvelle prise en charge
            </a>
        </x-slot>
    </x-page-header>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-4">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 space-y-3">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Rechercher (référence, client, n° série, marque…)"
                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]" />

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                <select wire:model.live="statusId" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">Tous statuts</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->label }}</option>
                    @endforeach
                </select>

                <select wire:model.live="clientType" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">Tous types</option>
                    <option value="particulier">Particulier</option>
                    <option value="pro">Professionnel</option>
                </select>

                <select wire:model.live="technicianId" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">Tous techniciens</option>
                    @foreach ($technicians as $technician)
                        <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                    @endforeach
                </select>

                <input type="date" wire:model.live="dateFrom" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                <input type="date" wire:model.live="dateTo" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
            </div>

            <button type="button" wire:click="resetFilters" class="text-xs text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">Réinitialiser les filtres</button>
        </div>

        {{-- Mobile : cartes --}}
        <div class="grid grid-cols-1 gap-3 sm:hidden">
            @forelse ($intakes as $intake)
                <a href="{{ route('intakes.show', $intake) }}" wire:navigate class="block bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $intake->reference }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full text-white" style="background-color: {{ $intake->status->color }}">{{ $intake->status->label }}</span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $intake->client->full_name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $intake->machine->brand }} {{ $intake->machine->model }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $intake->created_at->format('d/m/Y') }}</div>
                </a>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">Aucune prise en charge trouvée.</p>
            @endforelse
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:block bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Référence</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Client</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Machine</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Technicien</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($intakes as $intake)
                        <tr wire:key="intake-{{ $intake->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-900 cursor-pointer" onclick="window.location='{{ route('intakes.show', $intake) }}'">
                            <td class="px-4 py-3 text-sm font-medium text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">{{ $intake->reference }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $intake->client->full_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $intake->machine->brand }} {{ $intake->machine->model }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $intake->technician?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full text-white" style="background-color: {{ $intake->status->color }}">{{ $intake->status->label }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $intake->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Aucune prise en charge trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $intakes->links() }}
    </div>
</div>
