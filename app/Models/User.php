<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'azure_id', 'is_assignable'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_assignable' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isTechnicien(): bool
    {
        return $this->role === UserRole::Technicien;
    }

    public function usesLocalAuth(): bool
    {
        return $this->azure_id === null;
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeAssignable(Builder $query): Builder
    {
        return $query->where('is_assignable', true);
    }

    /**
     * @return HasMany<Intake, $this>
     */
    public function assignedIntakes(): HasMany
    {
        return $this->hasMany(Intake::class, 'technician_id');
    }

    /**
     * @return HasMany<Intake, $this>
     */
    public function createdIntakes(): HasMany
    {
        return $this->hasMany(Intake::class, 'created_by');
    }
}
