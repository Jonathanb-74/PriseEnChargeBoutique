<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['label' => 'En cours', 'slug' => 'en_cours', 'color' => '#3b82f6', 'sort_order' => 1, 'is_default' => true, 'is_final' => false],
            ['label' => 'En attente de pièce', 'slug' => 'attente_piece', 'color' => '#f59e0b', 'sort_order' => 2, 'is_default' => false, 'is_final' => false],
            ['label' => 'En attente client', 'slug' => 'attente_client', 'color' => '#f97316', 'sort_order' => 3, 'is_default' => false, 'is_final' => false],
            ['label' => 'Terminé', 'slug' => 'termine', 'color' => '#22c55e', 'sort_order' => 4, 'is_default' => false, 'is_final' => false],
            ['label' => 'Récupéré', 'slug' => 'recupere', 'color' => '#6b7280', 'sort_order' => 5, 'is_default' => false, 'is_final' => true],
        ];

        foreach ($statuses as $status) {
            Status::query()->updateOrCreate(['slug' => $status['slug']], $status);
        }
    }
}
