<?php
declare(strict_types=1);
namespace App\Modules\Auth\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->two_factor_confirmed_at === null && auth()->user()->hasRole('Super Admin')) {
            return redirect('/two-factor/setup');
        }
        return $next($request);
    }
}
