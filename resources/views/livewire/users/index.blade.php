<div>
    <x-page-header title="Utilisateurs" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-4">
        @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
        @endif

        {{-- CRÉATION / MODIFICATION --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ $editingId ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur (compte local)' }}
            </h3>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" value="Nom" />
                        <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" type="email" class="mt-1 block w-full" wire:model="email" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="role" value="Rôle" />
                        <select id="role" wire:model="role" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]">
                            @foreach ($roles as $roleOption)
                                <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model="is_assignable" class="rounded border-gray-300 dark:border-gray-700">
                            Peut être affecté à une prise en charge
                        </label>
                    </div>
                </div>

                @unless ($editingId)
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Aucun mot de passe n'est défini ici : un email sera envoyé à l'utilisateur pour qu'il choisisse lui-même son mot de passe.
                    </p>
                @endunless

                <div class="flex items-center justify-end gap-3">
                    @if ($editingId)
                        <button type="button" wire:click="cancelEdit" class="text-sm text-gray-600 dark:text-gray-400">Annuler</button>
                    @endif
                    <x-primary-button>{{ $editingId ? 'Enregistrer' : 'Créer et envoyer l\'invitation' }}</x-primary-button>
                </div>
            </form>
        </div>

        {{-- Mobile : cartes --}}
        <div class="grid grid-cols-1 gap-3 sm:hidden">
            @foreach ($users as $user)
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4" wire:key="user-mobile-{{ $user->id }}">
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        {{ $user->usesLocalAuth() ? 'Compte local' : 'Microsoft 365' }}
                        @if ($user->usesLocalAuth())
                            · 2FA {{ $user->hasTwoFactorEnabled() ? 'activée' : 'non activée' }}
                        @endif
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

                    @if ($user->usesLocalAuth())
                        <div class="flex flex-col items-start gap-1 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <button wire:click="edit({{ $user->id }})" class="text-xs text-[rgb(var(--color-accent))]">Modifier</button>
                            @unless ($user->is(auth()->user()))
                                <button wire:click="resetPassword({{ $user->id }})" wire:confirm="Envoyer un email de réinitialisation du mot de passe à {{ $user->name }} ?" class="text-xs text-gray-600 dark:text-gray-400">
                                    Réinitialiser le mot de passe
                                </button>
                                @if ($user->hasTwoFactorEnabled())
                                    <button wire:click="resetTwoFactor({{ $user->id }})" wire:confirm="Réinitialiser la 2FA de {{ $user->name }} ?" class="text-xs text-red-600 dark:text-red-400">
                                        Réinitialiser la 2FA
                                    </button>
                                @endif
                            @endunless
                        </div>
                    @endif
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
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $user->usesLocalAuth() ? 'Local' : 'Microsoft 365' }}
                                @if ($user->usesLocalAuth())
                                    <div class="text-xs text-gray-400 dark:text-gray-500">2FA {{ $user->hasTwoFactorEnabled() ? 'activée' : 'non activée' }}</div>
                                @endif
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
                            <td class="px-4 py-3 text-sm">
                                @if ($user->usesLocalAuth())
                                    <div class="flex flex-col items-start gap-1">
                                        <button wire:click="edit({{ $user->id }})" class="text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">Modifier</button>
                                        @unless ($user->is(auth()->user()))
                                            <button wire:click="resetPassword({{ $user->id }})" wire:confirm="Envoyer un email de réinitialisation du mot de passe à {{ $user->name }} ?" class="text-gray-600 dark:text-gray-400">
                                                Réinitialiser le mot de passe
                                            </button>
                                            @if ($user->hasTwoFactorEnabled())
                                                <button wire:click="resetTwoFactor({{ $user->id }})" wire:confirm="Réinitialiser la 2FA de {{ $user->name }} ?" class="text-red-600 dark:text-red-400">
                                                    Réinitialiser la 2FA
                                                </button>
                                            @endif
                                        @endunless
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
