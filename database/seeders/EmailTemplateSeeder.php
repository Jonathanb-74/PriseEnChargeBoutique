<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'en_cours',
                'name' => 'Réparation en cours',
                'subject' => 'Votre prise en charge {{reference}} est en cours de traitement',
                'body' => "Bonjour,\n\nNous vous informons que la réparation de votre matériel ({{machine}}) est actuellement en cours.\n\nRéférence : {{reference}}",
            ],
            [
                'key' => 'attente_piece',
                'name' => 'En attente de pièce',
                'subject' => 'Votre prise en charge {{reference}} est en attente de pièce',
                'body' => "Bonjour,\n\nLa réparation de votre matériel ({{machine}}) nécessite une pièce que nous avons commandée. Nous revenons vers vous dès sa réception.\n\nRéférence : {{reference}}",
            ],
            [
                'key' => 'termine',
                'name' => 'Réparation terminée',
                'subject' => 'Votre matériel est prêt - {{reference}}',
                'body' => "Bonjour,\n\nVotre matériel ({{machine}}) est réparé et prêt à être récupéré en boutique.\n\nRéférence : {{reference}}",
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::query()->updateOrCreate(['key' => $template['key']], $template);
        }
    }
}
