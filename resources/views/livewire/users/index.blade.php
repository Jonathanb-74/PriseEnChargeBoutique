<div>
    <x-page-header title="Utilisateurs" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-4">
        @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
        @endif

        {{-- Mobile : cartes --}}
        <div class="grid grid-cols-1 gap-3 sm:hidden">
            @foreach ($users as $user)
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4" wire:key="user-mobile-{{ $user->id }}">
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        {{ $user->usesLocalAuth() ? 'Compte local' : 'Microsoft 365' }}
                    </div>

                    @if ($user->is(auth()->user()))
                        <span class="inline-block mt-2 text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            {{ $user->role->label() }} (vous)
                        </span>
                    @else
                        <select wire:change="updateRole({{ $user->id }}, $event.target.value)"
                            class="mt-2 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    @endif

                    <label class="inline-flex items-center gap-2 mt-3 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:click="toggleAssignable({{ $user->id }})" @checked($user->is_assignable)
                            class="rounded border-gray-300 dark:border-gray-700">
                        Peut être affecté à une prise en charge
                    </label>
                </div>
            @endforeach
        </div>

        {{-- Desktop / tablette --}}
        <div class="hidden sm:block bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Connexion</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rôle</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Affectable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $user->usesLocalAuth() ? 'Local' : 'Microsoft 365' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->is(auth()->user()))
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $user->role->label() }} (vous)
                                    </span>
                                @else
                                    <select wire:change="updateRole({{ $user->id }}, $event.target.value)"
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:click="toggleAssignable({{ $user->id }})" @checked($user->is_assignable)
                                        class="rounded border-gray-300 dark:border-gray-700">
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
