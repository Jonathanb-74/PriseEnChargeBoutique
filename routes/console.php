<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Traite les emails en attente à chaque minute (via cron `schedule:run`), sans nécessiter
// un worker `queue:work` permanent en arrière-plan.
//
// `withoutOverlapping()` pose un verrou pour empêcher deux exécutions simultanées ; par
// défaut ce verrou expire après 24h. Sur un hébergement mutualisé où le processus PHP peut
// être tué brutalement (timeout d'exécution, connexion SMTP qui traîne...) avant d'avoir pu
// relâcher le verrou proprement, ça bloque silencieusement tout traitement de la file pendant
// jusqu'à 24h — on réduit donc cette expiration à 5 minutes pour qu'un verrou resté coincé se
// libère de lui-même rapidement.
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=25')
    ->everyMinute()
    ->withoutOverlapping(5);
