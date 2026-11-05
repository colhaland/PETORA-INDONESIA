<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_creates_the_first_super_admin_with_a_user_supplied_password(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->artisan('petora:bootstrap-admin', ['name' => 'Root Admin', 'email' => 'root@example.test'])
            ->expectsQuestion('Password awal (minimal 12 karakter, huruf dan angka)', 'SecurePassword123')
            ->expectsQuestion('Ulangi password awal', 'SecurePassword123')
            ->assertExitCode(0);

        $admin = User::query()->where('email', 'root@example.test')->firstOrFail();
        $this->assertTrue($admin->hasRole('Super Admin'));
        $this->assertTrue(Hash::check('SecurePassword123', $admin->password));
        $this->assertNull($admin->two_factor_confirmed_at);
    }

    public function test_bootstrap_refuses_to_run_after_any_user_exists(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
        User::factory()->create();

        $this->artisan('petora:bootstrap-admin', ['name' => 'Second Admin', 'email' => 'second@example.test'])
            ->assertExitCode(1);
        $this->assertSame(1, User::query()->count());
    }
}
