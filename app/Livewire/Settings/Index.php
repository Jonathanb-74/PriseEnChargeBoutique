<?php

namespace App\Livewire\Settings;

use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

    public string $intakeCreatedTemplateId = '';

    public bool $sendIntakeCreatedEmail = true;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $newLogo = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $newPdfLogo = null;

    public string $mailMailer = 'log';

    public string $mailHost = '';

    public string $mailPort = '';

    public string $mailEncryption = 'tls';

    public string $mailUsername = '';

    public string $mailPassword = '';

    public bool $hasStoredMailPassword = false;

    public string $mailFromAddress = '';

    public string $mailFromName = '';

    public string $mailReplyTo = '';

    public string $testEmailAddress = '';

    public ?string $testStatus = null;

    public ?string $testError = null;

    public function mount(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->intakeTermsText = Setting::get(Setting::INTAKE_TERMS_TEXT, '');
        $this->accentColor = Setting::accentColor();
        $this->intakeCreatedTemplateId = Setting::get(Setting::INTAKE_CREATED_TEMPLATE_ID, '');
        $this->sendIntakeCreatedEmail = Setting::sendIntakeCreatedEmailByDefault();

        $this->mailMailer = Setting::get(Setting::MAIL_MAILER) ?: config('mail.default');
        $this->mailHost = Setting::get(Setting::MAIL_HOST) ?: (string) config('mail.mailers.smtp.host');
        $this->mailPort = Setting::get(Setting::MAIL_PORT) ?: (string) config('mail.mailers.smtp.port');
        $this->mailEncryption = Setting::get(Setting::MAIL_ENCRYPTION, 'tls');
        $this->mailUsername = Setting::get(Setting::MAIL_USERNAME) ?: (string) config('mail.mailers.smtp.username');
        $this->hasStoredMailPassword = (bool) Setting::get(Setting::MAIL_PASSWORD);
        $this->mailFromAddress = Setting::get(Setting::MAIL_FROM_ADDRESS) ?: (string) config('mail.from.address');
        $this->mailFromName = Setting::get(Setting::MAIL_FROM_NAME) ?: (string) config('mail.from.name');
        $this->mailReplyTo = Setting::mailReplyTo() ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'intakeTermsText' => ['nullable', 'string', 'max:5000'],
            'accentColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'intakeCreatedTemplateId' => ['nullable', 'exists:email_templates,id'],
            'sendIntakeCreatedEmail' => ['boolean'],
            'newLogo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:1024'],
            'newPdfLogo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'mailMailer' => ['required', 'in:log,smtp'],
            'mailHost' => ['required_if:mailMailer,smtp', 'nullable', 'string', 'max:255'],
            'mailPort' => ['required_if:mailMailer,smtp', 'nullable', 'integer'],
            'mailEncryption' => ['required', 'in:tls,ssl,none'],
            'mailUsername' => ['nullable', 'string', 'max:255'],
            'mailPassword' => ['nullable', 'string', 'max:255'],
            'mailFromAddress' => ['required', 'email', 'max:255'],
            'mailFromName' => ['required', 'string', 'max:255'],
            'mailReplyTo' => ['nullable', 'email', 'max:255'],
        ]);

        Setting::set(Setting::INTAKE_TERMS_TEXT, $this->intakeTermsText);
        Setting::set(Setting::BRAND_ACCENT_COLOR, $this->accentColor);
        Setting::set(Setting::INTAKE_CREATED_TEMPLATE_ID, $this->intakeCreatedTemplateId ?: null);
        Setting::set(Setting::SEND_INTAKE_CREATED_EMAIL, $this->sendIntakeCreatedEmail ? '1' : '0');

        if ($this->newLogo) {
            $this->replaceLogo(Setting::BRAND_LOGO_PATH, $this->newLogo);
            $this->newLogo = null;
        }

        if ($this->newPdfLogo) {
            $this->replaceLogo(Setting::PDF_LOGO_PATH, $this->newPdfLogo);
            $this->newPdfLogo = null;
        }

        Setting::set(Setting::MAIL_MAILER, $this->mailMailer);
        Setting::set(Setting::MAIL_HOST, $this->mailHost ?: null);
        Setting::set(Setting::MAIL_PORT, $this->mailPort ?: null);
        Setting::set(Setting::MAIL_ENCRYPTION, $this->mailEncryption);
        Setting::set(Setting::MAIL_USERNAME, $this->mailUsername ?: null);
        Setting::set(Setting::MAIL_FROM_ADDRESS, $this->mailFromAddress);
        Setting::set(Setting::MAIL_FROM_NAME, $this->mailFromName);
        Setting::set(Setting::MAIL_REPLY_TO, $this->mailReplyTo ?: null);

        if ($this->mailPassword !== '') {
            Setting::setMailPassword($this->mailPassword);
            $this->mailPassword = '';
        }

        $this->hasStoredMailPassword = (bool) Setting::get(Setting::MAIL_PASSWORD);

        Setting::applyMailConfig();

        session()->flash('status', 'Paramètres enregistrés.');
    }

    public function sendTestEmail(): void
    {
        $this->testStatus = null;
        $this->testError = null;

        $this->validate([
            'testEmailAddress' => ['required', 'email'],
        ]);

        Setting::applyMailConfig();

        try {
            Mail::raw(
                'Ceci est un email de test envoyé depuis les paramètres de l\'application.',
                fn ($message) => $message->to($this->testEmailAddress)->subject('Test de configuration SMTP')
            );

            $this->testStatus = 'success';
        } catch (\Throwable $e) {
            $this->testStatus = 'error';
            $this->testError = $e->getMessage();
        }
    }

    protected function replaceLogo(string $settingKey, $file): void
    {
        $oldPath = Setting::get($settingKey);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        Setting::set($settingKey, $file->store('branding', 'public'));
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

    public function removePdfLogo(): void
    {
        $path = Setting::get(Setting::PDF_LOGO_PATH);

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        Setting::set(Setting::PDF_LOGO_PATH, null);

        session()->flash('status', 'Logo PDF réinitialisé.');
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
            'currentPdfLogoUrl' => Setting::get(Setting::PDF_LOGO_PATH) ? Storage::disk('public')->url(Setting::get(Setting::PDF_LOGO_PATH)) : null,
            'emailTemplates' => EmailTemplate::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
