<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateWithAdvisoryLock extends Command
{
    protected $signature = 'petora:migrate';

    protected $description = 'Run database migrations while holding a PostgreSQL advisory lock.';

    public function handle(): int
    {
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'pgsql') {
            $this->error('Production migrations require PostgreSQL advisory locks.');

            return self::FAILURE;
        }

        $lockClass = 1_145_855_813;
        $lockObject = 1_145_855_814;
        $connection->select('SELECT pg_advisory_lock(?, ?)', [$lockClass, $lockObject]);

        try {
            $exitCode = Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
            $this->output->write(Artisan::output());

            return $exitCode;
        } finally {
            $connection->select('SELECT pg_advisory_unlock(?, ?)', [$lockClass, $lockObject]);
        }
    }
}
