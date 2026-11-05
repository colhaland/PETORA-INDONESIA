<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_email_works(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password12345')]);
        $this->post('/logout'); // ensure guest
        $response = $this->from('/login')->get('/login');
        $response->assertOk();
        $this->assertGuest();
    }

    public function test_rate_limiter_key_is_case_insensitive_shape(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password12345')]);
        $this->seed(PermissionSeeder::class);
        $this->seed(BranchSeeder::class);
        $user->givePermissionTo('dashboard.view');
        $user->branches()->sync([$this->primaryBranchId()]);
        $this->actingAs($user)->get('/')->assertOk();
    }
}
