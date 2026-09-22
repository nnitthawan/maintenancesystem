<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTwoFactorConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $allowedRoutes = [
            'profile.edit',
            'profile.update',
            'two-factor.enable',
            'two-factor.confirm',
            'two-factor.disable',
        ];

        if ($user?->isAdmin()
            && ! $user->two_factor_enabled
            && ! in_array($request->route()?->getName(), $allowedRoutes, true)) {
            if (! $request->session()->has('two_factor.setup_secret')) {
                $request->session()->put('two_factor.setup_secret', Google2FA::generateSecretKey());
            }

            return redirect()->route('profile.edit')->with('status', 'two-factor-required');
        }

        return $next($request);
    }
}
