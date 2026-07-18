<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class CronController extends Controller
{
    /**
     * Runs the Laravel scheduler on demand, for hosts (e.g. Infomaniak mutualisé) that don't
     * allow a real crontab and instead expect a public URL to be pinged periodically. Guarded
     * by a long random secret (CRON_SECRET in .env) compared in constant time; on any mismatch
     * — including when CRON_SECRET isn't configured at all — this responds 404, exactly like a
     * route that doesn't exist, so it gives no hint of its own existence to anyone probing the
     * URL without the secret.
     */
    public function __invoke(Request $request, string $token): Response
    {
        $secret = config('services.cron_secret');

        abort_if(blank($secret) || ! hash_equals($secret, $token), 404);

        Artisan::call('schedule:run');

        return response('OK');
    }
}
