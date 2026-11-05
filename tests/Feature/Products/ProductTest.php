<?php
declare(strict_types=1);
namespace Tests\Feature\Products;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class ProductTest extends TestCase
{
    use RefreshDatabase;
    public function test_product_list_loads(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/products')->assertStatus(200);
    }
}
