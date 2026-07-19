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
// Volontairement `Schedule::call()` (exécution PHP directe, dans le même processus) plutôt
// que `Schedule::command()` : cette dernière lance un vrai sous-processus OS (`proc_open`),
// même en mode "foreground". Beaucoup d'hébergements mutualisés désactivent `proc_open`/`exec`
// pour le PHP qui tourne derrière le serveur web (PHP-FPM) — la commande y échoue alors
// silencieusement (schedule:run continue de répondre normalement), alors que le même code
// fonctionne en SSH où le PHP en ligne de commande a ces fonctions activées. `Schedule::call()`
// n'a besoin d'aucune des deux, donc fonctionne de façon identique dans les deux contextes.
//
// `withoutOverlapping()` pose un verrou pour empêcher deux exécutions simultanées ; par
// défaut ce verrou expire après 24h. Sur un hébergement mutualisé où le processus PHP peut
// être tué brutalement (timeout d'exécution, connexion SMTP qui traîne...) avant d'avoir pu
// relâcher le verrou proprement, ça bloque silencieusement tout traitement de la file pendant
// jusqu'à 24h — on réduit donc cette expiration à 5 minutes pour qu'un verrou resté coincé se
// libère de lui-même rapidement.
Schedule::call(function () {
    Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--tries' => 3,
        '--max-time' => 25,
    ]);
})
    ->everyMinute()
    ->name('queue-work')
    ->withoutOverlapping(5);
