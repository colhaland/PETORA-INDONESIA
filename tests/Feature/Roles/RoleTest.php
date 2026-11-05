<?php
declare(strict_types=1);
namespace Tests\Feature\Roles;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class RoleTest extends TestCase
{
    use RefreshDatabase;
    public function test_page_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('roles.view');
        $this->actingAs($user)->get('/roles')->assertStatus(200);
    }
}
