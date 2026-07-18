<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AzureAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $this->ensureAzureIsConfigured();

        return Socialite::driver('azure')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $this->ensureAzureIsConfigured();

        $azureUser = Socialite::driver('azure')->user();

        abort_if(blank($azureUser->getId()) || blank($azureUser->getEmail()), 403);

        $user = User::query()->where('azure_id', $azureUser->getId())->first();

        if (! $user) {
            $user = User::query()->where('email', $azureUser->getEmail())->first();

            if ($user) {
                $user->update(['azure_id' => $azureUser->getId()]);
            } else {
                $user = User::create([
                    'name' => $azureUser->getName() ?? $azureUser->getEmail(),
                    'email' => $azureUser->getEmail(),
                    'azure_id' => $azureUser->getId(),
                    'password' => null,
                    'role' => UserRole::Technicien,
                    'email_verified_at' => now(),
                ]);
            }
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * SSO is only usable with a fully configured, tenant-restricted Azure app: without an
     * explicit tenant, Azure would accept any Microsoft account (including personal ones),
     * and the email-based account linking below would allow account takeover.
     */
    protected function ensureAzureIsConfigured(): void
    {
        $config = config('services.azure');

        abort_if(
            blank($config['client_id'] ?? null)
            || blank($config['client_secret'] ?? null)
            || blank($config['tenant'] ?? null)
            || in_array(strtolower((string) $config['tenant']), ['common', 'consumers'], true),
            404
        );
    }
}
