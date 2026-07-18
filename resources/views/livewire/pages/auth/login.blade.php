<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $needsChallenge = $this->form->authenticate();

        if ($needsChallenge) {
            $this->redirect(route('two-factor.challenge'), navigate: true);

            return;
        }

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div x-data="{ showEmailLogin: {{ $errors->any() ? 'true' : 'false' }} }">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <a href="{{ route('auth.azure.redirect') }}"
        class="w-full inline-flex justify-center items-center gap-2.5 px-4 py-3 bg-[#2564cf] hover:bg-[#1e50a8] rounded-md text-sm font-semibold text-white shadow-sm transition">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 21 21" fill="none">
            <rect x="1" y="1" width="9" height="9" fill="#f25022" />
            <rect x="11" y="1" width="9" height="9" fill="#7fba00" />
            <rect x="1" y="11" width="9" height="9" fill="#00a4ef" />
            <rect x="11" y="11" width="9" height="9" fill="#ffb900" />
        </svg>
        Se connecter avec Microsoft 365
    </a>

    <div class="mt-4 text-center" x-show="! showEmailLogin">
        <button type="button" @click="showEmailLogin = true" class="text-sm text-gray-500 dark:text-gray-400 underline">
            Se connecter avec un email et un mot de passe
        </button>
    </div>

    <div x-show="showEmailLogin" x-cloak class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <form wire:submit="login">
            <!-- Email Address -->
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" autocomplete="username" />
                <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" value="Mot de passe" />

                <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                autocomplete="current-password" />

                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember" class="inline-flex items-center">
                    <input wire:model="form.remember" id="remember" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-[rgb(var(--color-accent))] shadow-sm focus:ring-[rgb(var(--color-accent))] dark:focus:ring-[rgb(var(--color-accent))] dark:focus:ring-offset-gray-800" name="remember">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Se souvenir de moi</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[rgb(var(--color-accent))] dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}" wire:navigate>
                        Mot de passe oublié ?
                    </a>
                @endif

                <x-primary-button class="ms-3">
                    Se connecter
                </x-primary-button>
            </div>
        </form>
    </div>
</div>
