<div>
    <x-page-header :title="$client->full_name">
        <x-slot name="actions">
            <a href="{{ route('clients.edit', $client) }}" wire:navigate class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                Modifier
            </a>
            <a href="{{ route('intakes.create', ['client' => $client->id]) }}" wire:navigate class="inline-flex items-center px-3 py-2 bg-gray-800 dark:bg-gray-200 rounded-md text-xs font-semibold text-white dark:text-gray-800 uppercase tracking-widest">
                Nouvelle prise en charge
            </a>
        </x-slot>
    </x-page-header>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <div class="text-xs uppercase text-gray-500 dark:text-gray-400">Type</div>
                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $client->type === 'pro' ? 'Professionnel' : 'Particulier' }}</div>
                @if ($client->company_name)
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $client->company_name }}</div>
                @endif
            </div>
            <div>
                <div class="text-xs uppercase text-gray-500 dark:text-gray-400">Contact</div>
                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $client->email ?: '—' }}</div>
                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $client->phone ?: '—' }}</div>
            </div>
            <div class="sm:col-span-2">
                <div class="text-xs uppercase text-gray-500 dark:text-gray-400">Adresse</div>
                <div class="text-sm text-gray-900 dark:text-gray-100">
                    {{ $client->address_line1 }}
                    @if ($client->address_line2) <br>{{ $client->address_line2 }} @endif
                    @if ($client->postal_code || $client->city) <br>{{ $client->postal_code }} {{ $client->city }} @endif
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">Machines</h3>
                <a href="{{ route('machines.create', $client) }}" wire:navigate class="text-sm text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">
                    + Ajouter une machine
                </a>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($client->machines as $machine)
                    <a href="{{ route('machines.edit', $machine) }}" wire:navigate class="flex items-center justify-between py-2 text-sm">
                        <span class="text-gray-900 dark:text-gray-100">{{ $machine->brand }} {{ $machine->model }}</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $machine->serial_number }}</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 py-2">Aucune machine enregistrée.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Prises en charge</h3>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($client->intakes as $intake)
                    <a href="{{ route('intakes.show', $intake) }}" wire:navigate class="flex items-center justify-between py-2 text-sm">
                        <span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $intake->reference }}</span>
                            <span class="text-gray-500 dark:text-gray-400"> — {{ $intake->machine->brand }} {{ $intake->machine->model }}</span>
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full text-white" style="background-color: {{ $intake->status->color }}">
                            {{ $intake->status->label }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 py-2">Aucune prise en charge.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
