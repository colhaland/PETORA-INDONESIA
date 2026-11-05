<?php
declare(strict_types=1);
namespace Tests\Feature\Auth;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_page_loads(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/login')->assertStatus(200);
    }
}
