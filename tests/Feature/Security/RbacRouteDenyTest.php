<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacRouteDenyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_access_customers(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/customers')->assertForbidden();
    }

    public function test_user_with_permission_can_access_customers(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('customers.view');
        $this->actingAs($user)->get('/customers')->assertOk();
    }

    public function test_user_without_permission_cannot_access_pos(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/pos')->assertForbidden();
    }

    public function test_user_without_permission_cannot_access_dashboard(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/')->assertForbidden();
    }

    public function test_user_without_permission_cannot_access_settings(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/settings')->assertForbidden();
    }

    public function test_user_without_permission_cannot_access_finance(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/finance')->assertForbidden();
    }

    public function test_user_without_permission_cannot_access_users(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/users')->assertForbidden();
    }

    public function test_user_without_permission_cannot_access_roles(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/roles')->assertForbidden();
    }

    public function test_user_without_permission_cannot_access_veterinary(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/veterinary')->assertForbidden();
    }

    public function test_user_without_permission_cannot_access_integrations(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/integration')->assertForbidden();
    }
}
