<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Providers\AppServiceProvider;
use LogicException;
use Tests\TestCase;

class ProductionConfigurationTest extends TestCase
{
    public function test_production_boot_rejects_an_unsafe_database_connection(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['database.default' => 'sqlite']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('DB_CONNECTION=pgsql');

        (new AppServiceProvider($this->app))->boot();
    }
}
