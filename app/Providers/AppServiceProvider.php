<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use LogicException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->assertProductionConfiguration();

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

    private function assertProductionConfiguration(): void
    {
        if (! $this->app->environment('production')) {
            return;
        }

        $missing = [];
        foreach ([
            'APP_KEY' => config('app.key'),
            'DB_HOST' => config('database.connections.pgsql.host'),
            'DB_DATABASE' => config('database.connections.pgsql.database'),
            'DB_USERNAME' => config('database.connections.pgsql.username'),
            'DB_PASSWORD' => config('database.connections.pgsql.password'),
            'DB_SSLROOTCERT' => config('database.connections.pgsql.sslrootcert'),
            'REDIS_PASSWORD' => config('database.redis.default.password'),
            'REDIS_URL' => config('database.redis.default.url'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_PASSWORD' => config('mail.mailers.smtp.password'),
            'MAIL_SCHEME' => config('mail.mailers.smtp.scheme'),
        ] as $name => $value) {
            if (! is_string($value) || trim($value) === '') {
                $missing[] = $name;
            }
        }

        if (config('database.default') !== 'pgsql') {
            $missing[] = 'DB_CONNECTION=pgsql';
        }
        if (config('database.connections.pgsql.sslmode') !== 'verify-full') {
            $missing[] = 'DB_SSLMODE=verify-full';
        }
        if (config('app.debug')) {
            $missing[] = 'APP_DEBUG=false';
        }
        if (! config('session.encrypt')) {
            $missing[] = 'SESSION_ENCRYPT=true';
        }
        if (config('session.secure') !== true) {
            $missing[] = 'SESSION_SECURE_COOKIE=true';
        }
        if (! in_array(config('session.driver'), ['database', 'redis'], true)) {
            $missing[] = 'SESSION_DRIVER=database|redis';
        }
        if (config('mail.default') !== 'smtp') {
            $missing[] = 'MAIL_MAILER=smtp';
        }
        if (! str_starts_with((string) config('app.url'), 'https://')) {
            $missing[] = 'APP_URL=https://...';
        }
        if (! str_starts_with((string) config('database.redis.default.url'), 'rediss://')) {
            $missing[] = 'REDIS_URL=rediss://...';
        }
        if (config('mail.mailers.smtp.scheme') !== 'smtps' || (int) config('mail.mailers.smtp.port') !== 465) {
            $missing[] = 'MAIL_SCHEME=smtps with MAIL_PORT=465';
        }

        if ($missing !== []) {
            throw new LogicException('Production configuration is incomplete or insecure: '.implode(', ', array_unique($missing)));
        }

        if (! is_file((string) config('database.connections.pgsql.sslrootcert'))) {
            throw new LogicException('Production PostgreSQL CA certificate is not readable.');
        }
    }
}
