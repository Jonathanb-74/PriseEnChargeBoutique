<?php

namespace App\Providers;

use App\Models\ClientNotification;
use App\Models\Setting;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
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
