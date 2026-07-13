<?php

namespace App\Providers;

use App\Models\Setting;
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

        View::composer(['layouts.app', 'layouts.guest'], function (ViewContract $view) {
            $view->with([
                'brandAccentRgb' => Setting::accentColorRgb(),
                'brandLogoUrl' => Setting::logoUrl(),
            ]);
        });
    }
}
