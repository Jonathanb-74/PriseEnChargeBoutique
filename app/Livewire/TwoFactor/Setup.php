<?php

namespace App\Livewire\TwoFactor;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use PragmaRX\Google2FAQRCode\Google2FA;

#[Layout('layouts.app')]
class Setup extends Component
{
    public ?string $pendingSecret = null;

    public string $qrCodeUrl = '';

    public string $code = '';

    public string $disableCode = '';

    /** @var array<int, string>|null */
    public ?array $recoveryCodes = null;

    public function mount(): void
    {
        abort_unless(Auth::user()->usesLocalAuth(), 403, "Les comptes Microsoft 365 sont protégés par l'authentification Azure AD.");
    }

    public function startSetup(): void
    {
        $google2fa = new Google2FA();

        $this->pendingSecret = $google2fa->generateSecretKey();
        $this->qrCodeUrl = $google2fa->getQRCodeInline(
            config('app.name'),
            Auth::user()->email,
            $this->pendingSecret
        );
    }

    public function confirm(): void
    {
        $this->validate(['code' => ['required', 'digits:6']]);

        $google2fa = new Google2FA();

        if (! $google2fa->verifyKey($this->pendingSecret, $this->code)) {
            $this->addError('code', 'Le code est invalide.');

            return;
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::random(10).'-'.Str::random(10))
            ->all();

        Auth::user()->forceFill([
            'two_factor_secret' => $this->pendingSecret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->recoveryCodes = $recoveryCodes;
        $this->pendingSecret = null;
        $this->qrCodeUrl = '';
        $this->code = '';
    }

    public function disable(): void
    {
        $this->validate(['disableCode' => ['required', 'digits:6']]);

        $google2fa = new Google2FA();

        if (! $google2fa->verifyKey(Auth::user()->two_factor_secret, $this->disableCode)) {
            $this->addError('disableCode', 'Le code est invalide.');

            return;
        }

        Auth::user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->disableCode = '';
        $this->recoveryCodes = null;
    }

    public function render()
    {
        return view('livewire.two-factor.setup');
    }
}
