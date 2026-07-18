<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsEnabled
{
    /**
     * Local (non-Microsoft 365) accounts must have 2FA configured before using the app.
     * Azure/SSO accounts are exempt (protected by Azure AD's own auth). Only enforced on
     * page navigations (GET) so in-page Livewire actions on the setup page itself — e.g.
     * logging out from the nav bar — aren't caught mid-flight by this same check.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (
            $request->isMethod('GET')
            && $user
            && $user->usesLocalAuth()
            && ! $user->hasTwoFactorEnabled()
            && ! $request->routeIs('two-factor.setup')
        ) {
            return redirect()->route('two-factor.setup');
        }

        return $next($request);
    }
}
