<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public const INTAKE_TERMS_TEXT = 'intake_terms_text';

    public const BRAND_ACCENT_COLOR = 'brand_accent_color';

    public const BRAND_LOGO_PATH = 'brand_logo_path';

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
}
