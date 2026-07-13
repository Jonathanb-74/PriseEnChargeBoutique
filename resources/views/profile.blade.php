<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (auth()->user()->usesLocalAuth())
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <livewire:profile.update-profile-information-form />
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <livewire:profile.update-password-form />
                    </div>
                </div>
            @else
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informations du compte</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Ce compte est géré via Microsoft 365. Le nom, l'email et le mot de passe ne peuvent pas être modifiés ici — contactez votre administrateur si ces informations doivent changer sur votre compte Microsoft.
                        </p>
                        <dl class="mt-4 space-y-2">
                            <div>
                                <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Nom</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ auth()->user()->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ auth()->user()->email }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            @endif

            @if (auth()->user()->usesLocalAuth())
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Authentification à deux facteurs</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ auth()->user()->hasTwoFactorEnabled() ? 'Activée sur votre compte.' : 'Non activée.' }}
                        </p>
                        <a href="{{ route('two-factor.setup') }}" wire:navigate class="inline-flex items-center px-4 py-2 mt-3 bg-gray-800 dark:bg-gray-200 rounded-md text-xs font-semibold text-white dark:text-gray-800 uppercase tracking-widest">
                            Gérer la 2FA
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
