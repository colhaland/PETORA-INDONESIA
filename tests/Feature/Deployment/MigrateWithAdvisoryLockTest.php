<?php

declare(strict_types=1);

namespace Tests\Feature\Deployment;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MigrateWithAdvisoryLockTest extends TestCase
{
    public function test_migrations_are_refused_when_the_default_connection_is_not_postgresql(): void
    {
        config(['database.default' => 'sqlite']);

        $this->artisan('petora:migrate')
            ->expectsOutput('Production migrations require PostgreSQL advisory locks.')
            ->assertExitCode(1);
    }

    public function test_postgresql_migration_command_acquires_and_releases_its_advisory_lock(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL advisory locks can only be verified with PostgreSQL.');
        }

        $queries = [];
        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            $queries[] = strtolower($query->sql);
        });

        $this->artisan('petora:migrate')->assertSuccessful();

        $lockQueries = array_values(array_filter($queries, fn (string $sql): bool => str_contains($sql, 'pg_advisory_lock')));
        $unlockQueries = array_values(array_filter($queries, fn (string $sql): bool => str_contains($sql, 'pg_advisory_unlock')));

        $this->assertCount(1, $lockQueries);
        $this->assertCount(1, $unlockQueries);

        config([
            'database.connections.pgsql_lock_probe' => config('database.connections.pgsql'),
        ]);
        $probe = DB::connection('pgsql_lock_probe');
        try {
            $result = $probe->select('SELECT pg_try_advisory_lock(?, ?) AS acquired', [1_145_855_813, 1_145_855_814]);
            $this->assertTrue((bool) $result[0]->acquired);
        } finally {
            $probe->select('SELECT pg_advisory_unlock(?, ?)', [1_145_855_813, 1_145_855_814]);
            DB::purge('pgsql_lock_probe');
        }
    }
}
