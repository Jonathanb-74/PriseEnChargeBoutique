<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public const INTAKE_TERMS_TEXT = 'intake_terms_text';

    public const BRAND_ACCENT_COLOR = 'brand_accent_color';

    public const BRAND_LOGO_PATH = 'brand_logo_path';

    public const PDF_LOGO_PATH = 'pdf_logo_path';

    public const INTAKE_CREATED_TEMPLATE_ID = 'intake_created_template_id';

    public const MAIL_MAILER = 'mail_mailer';

    public const MAIL_HOST = 'mail_host';

    public const MAIL_PORT = 'mail_port';

    public const MAIL_ENCRYPTION = 'mail_encryption';

    public const MAIL_USERNAME = 'mail_username';

    public const MAIL_PASSWORD = 'mail_password';

    public const MAIL_FROM_ADDRESS = 'mail_from_address';

    public const MAIL_FROM_NAME = 'mail_from_name';

    public const EMAIL_SIGNATURE = 'email_signature';

    public const DEFAULT_ACCENT_COLOR = '#4f46e5';

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function accentColor(): string
    {
        return static::get(self::BRAND_ACCENT_COLOR, self::DEFAULT_ACCENT_COLOR);
    }

    /**
     * The configured accent color as "R G B" (space-separated channels), for use in a CSS
     * custom property so Tailwind's arbitrary-value opacity modifiers keep working, e.g.
     * `text-[rgb(var(--color-accent))]` or `bg-[rgb(var(--color-accent)/0.1)]`.
     */
    public static function accentColorRgb(): string
    {
        $hex = ltrim(static::accentColor(), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = ltrim(self::DEFAULT_ACCENT_COLOR, '#');
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r} {$g} {$b}";
    }

    public static function logoUrl(): ?string
    {
        $path = static::get(self::BRAND_LOGO_PATH);

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /**
     * Public URL of the PDF-specific logo, for use in emails (which need a reachable URL,
     * unlike DomPDF's local path). Falls back to the general app logo if none is set.
     */
    public static function pdfLogoUrl(): ?string
    {
        $path = static::get(self::PDF_LOGO_PATH) ?? static::get(self::BRAND_LOGO_PATH);

        return $path && Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }

    /**
     * Absolute filesystem path to the PDF-specific logo, for use with DomPDF (which needs a
     * local path rather than a URL). Falls back to the general app logo if none is set.
     */
    public static function pdfLogoPath(): ?string
    {
        $path = static::get(self::PDF_LOGO_PATH) ?? static::get(self::BRAND_LOGO_PATH);

        return $path && Storage::disk('public')->exists($path)
            ? Storage::disk('public')->path($path)
            : null;
    }

    public static function emailSignature(): string
    {
        return static::get(self::EMAIL_SIGNATURE) ?: "Cordialement,\n".config('app.name');
    }

    public static function mailPassword(): ?string
    {
        $encrypted = static::get(self::MAIL_PASSWORD);

        return $encrypted ? Crypt::decryptString($encrypted) : null;
    }

    public static function setMailPassword(?string $password): void
    {
        static::set(self::MAIL_PASSWORD, $password ? Crypt::encryptString($password) : null);
    }

    /**
     * Overrides Laravel's mail config with the admin-configured SMTP settings, when set.
     * Falls back to the .env-based config entirely if no mailer override has been saved.
     */
    public static function applyMailConfig(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $mailer = static::get(self::MAIL_MAILER);

        if (! $mailer) {
            return;
        }

        config(['mail.default' => $mailer]);

        if ($mailer === 'smtp') {
            config([
                'mail.mailers.smtp.scheme' => static::get(self::MAIL_ENCRYPTION) === 'ssl' ? 'smtps' : 'smtp',
                'mail.mailers.smtp.host' => static::get(self::MAIL_HOST) ?: config('mail.mailers.smtp.host'),
                'mail.mailers.smtp.port' => (int) (static::get(self::MAIL_PORT) ?: config('mail.mailers.smtp.port')),
                'mail.mailers.smtp.username' => static::get(self::MAIL_USERNAME),
                'mail.mailers.smtp.password' => static::mailPassword(),
            ]);
        }

        if ($fromAddress = static::get(self::MAIL_FROM_ADDRESS)) {
            config(['mail.from.address' => $fromAddress]);
        }

        if ($fromName = static::get(self::MAIL_FROM_NAME)) {
            config(['mail.from.name' => $fromName]);
        }
    }
}
