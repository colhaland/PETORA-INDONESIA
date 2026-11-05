<?php
declare(strict_types=1);
namespace Tests\Feature\Audit;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class AuditTest extends TestCase
{
    use RefreshDatabase;
    public function test_page_loads(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/audit')->assertStatus(200);
    }
}
