<?php

namespace App\Livewire\TwoFactor;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;
use PragmaRX\Google2FAQRCode\Google2FA;

#[Layout('layouts.guest')]
class Challenge extends Component
{
    public string $code = '';

    public bool $useRecoveryCode = false;

    public function mount(): void
    {
        if (! Session::has('2fa.user_id')) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function verify(): void
    {
        $this->validate(['code' => ['required', 'string']]);

        $user = User::find(Session::get('2fa.user_id'));

        if (! $user) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $throttleKey = '2fa:'.$user->id.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('code', "Trop de tentatives. Veuillez réessayer dans {$seconds} secondes.");

            return;
        }

        if ($this->useRecoveryCode) {
            $codes = $user->two_factor_recovery_codes ?? [];

            if (! in_array($this->code, $codes, true)) {
                RateLimiter::hit($throttleKey);
                $this->addError('code', 'Code de récupération invalide.');

                return;
            }

            $user->forceFill([
                'two_factor_recovery_codes' => array_values(array_diff($codes, [$this->code])),
            ])->save();
        } else {
            $google2fa = new Google2FA();

            if (! $google2fa->verifyKey($user->two_factor_secret, $this->code)) {
                RateLimiter::hit($throttleKey);
                $this->addError('code', 'Code invalide.');

                return;
            }
        }

        RateLimiter::clear($throttleKey);

        $remember = Session::pull('2fa.remember', false);
        Session::forget('2fa.user_id');

        Auth::login($user, $remember);
        Session::regenerate();

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    public function render()
    {
        return view('livewire.two-factor.challenge');
    }
}
