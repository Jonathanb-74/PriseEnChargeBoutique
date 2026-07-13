<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public string $intakeTermsText = '';

    public string $accentColor = Setting::DEFAULT_ACCENT_COLOR;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $newLogo = null;

    public function mount(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->intakeTermsText = Setting::get(Setting::INTAKE_TERMS_TEXT, '');
        $this->accentColor = Setting::accentColor();
    }

    public function save(): void
    {
        $this->validate([
            'intakeTermsText' => ['nullable', 'string', 'max:5000'],
            'accentColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'newLogo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:1024'],
        ]);

        Setting::set(Setting::INTAKE_TERMS_TEXT, $this->intakeTermsText);
        Setting::set(Setting::BRAND_ACCENT_COLOR, $this->accentColor);

        if ($this->newLogo) {
            $oldPath = Setting::get(Setting::BRAND_LOGO_PATH);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $this->newLogo->store('branding', 'public');
            Setting::set(Setting::BRAND_LOGO_PATH, $path);
            $this->newLogo = null;
        }

        session()->flash('status', 'Paramètres enregistrés.');
    }

    public function removeLogo(): void
    {
        $path = Setting::get(Setting::BRAND_LOGO_PATH);

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        Setting::set(Setting::BRAND_LOGO_PATH, null);

        session()->flash('status', 'Logo réinitialisé.');
    }

    public function resetAccentColor(): void
    {
        $this->accentColor = Setting::DEFAULT_ACCENT_COLOR;
        Setting::set(Setting::BRAND_ACCENT_COLOR, $this->accentColor);

        session()->flash('status', 'Couleur réinitialisée.');
    }

    public function render()
    {
        return view('livewire.settings.index', [
            'currentLogoUrl' => Setting::logoUrl(),
        ]);
    }
}
