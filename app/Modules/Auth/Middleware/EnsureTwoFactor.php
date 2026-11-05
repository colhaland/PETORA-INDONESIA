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

        $requires2fa = method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin', 'Admin']);
        if ($requires2fa && empty($user->two_factor_confirmed_at)) {
            if (! $request->routeIs('two-factor.*', 'logout')) {
                return redirect()->route('two-factor.setup');
            }
        }

        return $next($request);
    }
}
