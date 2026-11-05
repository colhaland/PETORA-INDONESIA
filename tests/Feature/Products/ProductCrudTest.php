<?php

declare(strict_types=1);

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\Warehouse;
use App\Modules\Products\Services\ProductService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('products.create', 'products.update', 'products.delete', 'products.view');
        $this->actingAs($user);

        return $user;
    }

    public function test_create_product_via_service(): void
    {
        $this->authUser();
        $svc = new ProductService;
        $p = $svc->create(['name' => 'Makanan', 'sku' => 'SKU001', 'sell_price' => 10000, 'track_inventory' => true]);
        $this->assertEquals('Makanan', $p->name);
        $this->assertEquals('SKU001', $p->sku);
        $this->assertTrue($p->track_inventory);
    }

    public function test_update_product(): void
    {
        $this->authUser();
        $svc = new ProductService;
        $p = $svc->create(['name' => 'Makanan', 'sku' => 'SKU001']);
        $svc->update($p, ['name' => 'Minuman']);
        $this->assertEquals('Minuman', $p->fresh()->name);
    }

    public function test_stock_tracking_cannot_be_disabled_while_stock_is_present(): void
    {
        $this->authUser();
        $product = Product::create(['name' => 'Food', 'sku' => 'SKU-STOCK', 'track_inventory' => true]);
        $warehouse = Warehouse::create([
            'branch_id' => $this->primaryBranchId(),
            'code' => 'PSTOCK',
            'name' => 'Stock warehouse',
        ]);
        StockBalance::create(['warehouse_id' => $warehouse->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->expectException(\RuntimeException::class);
        (new ProductService)->update($product, ['track_inventory' => false]);
    }

    public function test_delete_product(): void
    {
        $this->authUser();
        $svc = new ProductService;
        $p = $svc->create(['name' => 'Makanan', 'sku' => 'SKU001']);
        $svc->delete($p);
        $this->assertSoftDeleted('products', ['id' => $p->id]);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/products')->assertStatus(200);
    }

    public function test_create_page_loads(): void
    {
        $this->authUser();
        $this->get('/products/create')->assertStatus(200);
    }
}
