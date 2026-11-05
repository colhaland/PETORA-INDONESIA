<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class BootstrapAdmin extends Command
{
    protected $signature = 'petora:bootstrap-admin {name?} {email?}';

    protected $description = 'Create the first Super Admin using an interactive one-time CLI bootstrap.';

    public function handle(): int
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('roles') || ! Schema::hasTable('super_admin_bootstraps')) {
            $this->error('Jalankan migrasi dan seeder role sebelum bootstrap admin.');

            return self::FAILURE;
        }

        if (User::query()->exists()) {
            $this->error('Bootstrap hanya tersedia pada instalasi tanpa pengguna.');

            return self::FAILURE;
        }

        $role = Role::query()->where('name', 'Super Admin')->where('guard_name', 'web')->first();
        if (! $role) {
            $this->error('Role Super Admin tidak ditemukan. Jalankan seeder terlebih dahulu.');

            return self::FAILURE;
        }

        $name = trim((string) ($this->argument('name') ?: $this->ask('Nama Super Admin')));
        $email = strtolower(trim((string) ($this->argument('email') ?: $this->ask('Email Super Admin'))));
        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Nama dan alamat email yang valid wajib diisi.');

            return self::FAILURE;
        }

        $password = $this->secret('Password awal (minimal 12 karakter, huruf dan angka)');
        $confirmedPassword = $this->secret('Ulangi password awal');
        if (! is_string($password) || strlen($password) < 12
            || preg_match('/[A-Za-z]/', $password) !== 1
            || preg_match('/\d/', $password) !== 1
            || ! hash_equals((string) $password, (string) $confirmedPassword)) {
            $this->error('Password harus cocok, minimal 12 karakter, serta mengandung huruf dan angka.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($name, $email, $password, $role): void {
                if (User::query()->exists()) {
                    throw new \RuntimeException('Bootstrap hanya tersedia pada instalasi tanpa pengguna.');
                }

                DB::table('super_admin_bootstraps')->insert([
                    'id' => 1,
                    'email' => $email,
                    'created_at' => now(),
                ]);
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => 'active',
                ]);
                $user->assignRole($role);
            });
        } catch (QueryException|\RuntimeException) {
            $this->error('Bootstrap gagal: instalasi sudah dipakai atau email telah digunakan.');

            return self::FAILURE;
        }

        $this->info('Super Admin dibuat. Login pertama akan mewajibkan aktivasi 2FA.');

        return self::SUCCESS;
    }
}
