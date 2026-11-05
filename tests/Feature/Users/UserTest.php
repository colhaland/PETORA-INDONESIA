<?php
declare(strict_types=1);
namespace Tests\Feature\Users;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class UserTest extends TestCase
{
    use RefreshDatabase;
    public function test_page_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('users.view');
        $this->actingAs($user)->get('/users')->assertStatus(200);
    }
}
