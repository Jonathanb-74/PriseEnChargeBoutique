<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StatusSeeder::class,
            EmailTemplateSeeder::class,
        ]);

        Setting::query()->firstOrCreate(
            ['key' => Setting::INTAKE_TERMS_TEXT],
            ['value' => "Tarif minimum de diagnostic : 30€ TTC, dû même en cas de refus du devis.\nAu-delà de 15 jours sans nouvelles du client après notification de fin de réparation, des frais de gardiennage de 2€/jour pourront être appliqués.\nLe magasin décline toute responsabilité en cas de perte de données non sauvegardées."]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@boutique.test'],
            [
                'name' => 'Administrateur',
                'password' => 'password',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );
    }
}
