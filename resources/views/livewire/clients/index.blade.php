<div>
    <x-page-header title="Clients">
        <x-slot name="actions">
            <a href="{{ route('clients.create') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md text-xs font-semibold text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">
                Nouveau client
            </a>
        </x-slot>
    </x-page-header>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-4">
        @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Rechercher un client (nom, société, email, téléphone)…"
                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]" />
        </div>

        {{-- Mobile: liste en cartes --}}
        <div class="grid grid-cols-1 gap-3 sm:hidden">
            @forelse ($clients as $client)
                <a href="{{ route('clients.show', $client) }}" wire:navigate class="block bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $client->full_name }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $client->type === 'pro' ? 'bg-[rgb(var(--color-accent)/0.12)] text-[rgb(var(--color-accent))] dark:bg-[rgb(var(--color-accent)/0.25)] dark:text-[rgb(var(--color-accent))]' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $client->type === 'pro' ? 'Pro' : 'Particulier' }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $client->email }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $client->phone }}</div>
                </a>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">Aucun client trouvé.</p>
            @endforelse
        </div>

        {{-- Desktop / tablette : tableau --}}
        <div class="hidden sm:block bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Téléphone</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($clients as $client)
                        <tr wire:key="client-{{ $client->id }}">
                            <td class="px-4 py-3">
                                <a href="{{ route('clients.show', $client) }}" wire:navigate class="text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))] font-medium">
                                    {{ $client->full_name }}
                                </a>
                                @if ($client->type === 'pro' && $client->company_name)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $client->company_name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $client->type === 'pro' ? 'Pro' : 'Particulier' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $client->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $client->phone }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('delete', $client)
                                    <button wire:click="delete({{ $client->id }})" wire:confirm="Supprimer ce client ?" class="text-sm text-red-600 dark:text-red-400">
                                        Supprimer
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Aucun client trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $clients->links() }}
    </div>
</div>
