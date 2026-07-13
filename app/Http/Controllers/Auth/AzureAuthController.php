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
        return Socialite::driver('azure')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $azureUser = Socialite::driver('azure')->user();

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
}
