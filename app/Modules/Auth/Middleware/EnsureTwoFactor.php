<?php

declare(strict_types=1);

namespace App\Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs('two-factor.challenge', 'logout')) {
            return $next($request);
        }

        $requires2fa = method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin', 'Admin']);
        if ($requires2fa && empty($user->two_factor_confirmed_at) && ! $request->routeIs('two-factor.setup')) {
            return redirect()->route('two-factor.setup');
        }

        if (! empty($user->two_factor_confirmed_at) && ! session('auth.2fa_verified') && ! $request->routeIs('two-factor.setup')) {
            session(['auth.2fa_pending_user_id' => $user->id, 'url.intended' => $request->fullUrl()]);
            auth()->logout();

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
