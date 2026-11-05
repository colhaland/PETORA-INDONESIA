<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
                return true;
            }

            if (method_exists($user, 'hasPermissionTo')) {
                try {
                    if ($user->hasPermissionTo($ability)) {
                        return true;
                    }
                } catch (\Throwable) {
                    return null;
                }
            }

            return null;
        });
    }
}
