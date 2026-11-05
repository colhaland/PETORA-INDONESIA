<?php

declare(strict_types=1);

namespace Tests\Feature\Products;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_list_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('products.view');
        $this->actingAs($user)->get('/products')->assertStatus(200);
    }
}
