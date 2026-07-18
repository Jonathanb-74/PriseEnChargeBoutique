<?php

namespace App\Providers;

use App\Models\ClientNotification;
use App\Models\Setting;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewContract;
use SocialiteProviders\Azure\AzureExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force les URLs générées en https en production, quel que soit le schéma détecté.
        // Indispensable pour les liens envoyés par email (réinitialisation de mot de passe,
        // vérification…) : ces notifications partent depuis le worker de la file d'attente
        // (`queue:work`, lancé par la commande cron), donc sans requête HTTP en cours — Laravel
        // se rabat alors sur APP_URL, et un simple oubli de mettre "https://" dans .env suffit
        // à générer des liens en http:// malgré un site servi en https.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Event::listen(SocialiteWasCalled::class, AzureExtendSocialite::class);

        Setting::applyMailConfig();

        Event::listen(MessageSending::class, function (MessageSending $event) {
            $replyTo = Setting::mailReplyTo();

            if ($replyTo && empty($event->message->getReplyTo())) {
                $event->message->replyTo($replyTo);
            }
        });

        Event::listen(MessageSent::class, function (MessageSent $event) {
            $notificationId = $event->data['notificationId'] ?? null;

            if ($notificationId) {
                ClientNotification::whereKey($notificationId)->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }
        });

        View::composer(['layouts.app', 'layouts.guest'], function (ViewContract $view) {
            $view->with([
                'brandAccentRgb' => Setting::accentColorRgb(),
                'brandLogoUrl' => Setting::logoUrl(),
            ]);
        });
    }
}
