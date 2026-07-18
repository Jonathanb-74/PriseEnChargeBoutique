<?php

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::where('email', $this->email)->first();

        if ($user && ! $user->usesLocalAuth()) {
            $this->addError('email', "Cette adresse n'est pas autorisée à réinitialiser un mot de passe. Contactez votre administrateur.");

            return;
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', match ($status) {
                Password::RESET_THROTTLED => 'Veuillez patienter avant de redemander un lien.',
                default => "Impossible d'envoyer le lien de réinitialisation. Vérifiez l'adresse saisie.",
            });

            return;
        }

        $this->reset('email');

        session()->flash('status', 'Un lien de réinitialisation a été envoyé à votre adresse email.');
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Mot de passe oublié ? Indiquez votre adresse email et nous vous enverrons un lien pour en choisir un nouveau.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Envoyer le lien de réinitialisation
            </x-primary-button>
        </div>
    </form>
</div>
