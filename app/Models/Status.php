<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['label', 'slug', 'color', 'sort_order', 'is_default', 'is_final'])]
class Status extends Model
{
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_final' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Intake, $this>
     */
    public function intakes(): HasMany
    {
        return $this->hasMany(Intake::class);
    }

    public static function default(): ?self
    {
        return static::query()->where('is_default', true)->orderBy('sort_order')->first();
    }
}
