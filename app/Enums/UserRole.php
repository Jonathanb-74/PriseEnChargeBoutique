<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Technicien = 'technicien';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Technicien => 'Technicien',
        };
    }
}
