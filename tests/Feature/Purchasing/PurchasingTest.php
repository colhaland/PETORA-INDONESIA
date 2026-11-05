<?php
declare(strict_types=1);
namespace Tests\Feature\Purchasing;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class PurchasingTest extends TestCase
{
    use RefreshDatabase;
    public function test_purchasing_list_loads(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/purchasing')->assertStatus(200);
    }
}
