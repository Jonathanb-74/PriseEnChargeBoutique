<div>
    <x-page-header title="Tableau de bord">
        <x-slot name="actions">
            <a href="{{ route('intakes.create') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md text-xs font-semibold text-white dark:text-gray-800 uppercase tracking-widest">
                Nouvelle prise en charge
            </a>
        </x-slot>
    </x-page-header>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <a href="{{ route('intakes.index') }}" wire:navigate class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $openCount }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Prises en charge ouvertes</div>
            </a>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $mineCount }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Assignées à moi</div>
            </div>
            <a href="{{ route('clients.index') }}" wire:navigate class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">→</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Voir les clients</div>
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 font-medium text-gray-900 dark:text-gray-100">
                Dernières prises en charge
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($recentIntakes as $intake)
                    <a href="{{ route('intakes.show', $intake) }}" wire:navigate class="flex items-center justify-between p-4 text-sm">
                        <span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $intake->reference }}</span>
                            <span class="text-gray-500 dark:text-gray-400"> — {{ $intake->client->full_name }}</span>
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full text-white" style="background-color: {{ $intake->status->color }}">
                            {{ $intake->status->label }}
                        </span>
                    </a>
                @empty
                    <p class="p-4 text-sm text-gray-500 dark:text-gray-400">Aucune prise en charge.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
