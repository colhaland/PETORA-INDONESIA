<?php
declare(strict_types=1);
namespace Tests\Feature\Pos;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class PosTest extends TestCase
{
    use RefreshDatabase;
    public function test_pos_page_loads(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/pos')->assertStatus(200);
    }
}
