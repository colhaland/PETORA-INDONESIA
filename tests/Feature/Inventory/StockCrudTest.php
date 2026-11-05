<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Modules\Inventory\Services\StockService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $this->seed(BranchSeeder::class);
        $user->givePermissionTo('inventory.adjust', 'inventory.view');
        $user->branches()->sync([$this->primaryBranchId()]);
        $this->actingAs($user);

        return $user;
    }

    public function test_receive_stock(): void
    {
        $this->authUser();
        $wh = Warehouse::create(['branch_id' => $this->primaryBranchId(), 'code' => 'WH1', 'name' => 'Main']);
        $prod = Product::create(['name' => 'Food', 'sku' => 'F1', 'track_inventory' => true]);
        $svc = new StockService;
        $svc->receive($wh->id, $prod->id, 10);
        $this->assertDatabaseHas('stock_balances', ['warehouse_id' => $wh->id, 'product_id' => $prod->id, 'quantity' => 10]);
    }

    public function test_receipts_for_the_same_unbatched_lot_share_one_locked_balance(): void
    {
        $this->authUser();
        $warehouse = Warehouse::create(['branch_id' => $this->primaryBranchId(), 'code' => 'WH2', 'name' => 'Main']);
        $product = Product::create(['name' => 'Treats', 'sku' => 'F2', 'track_inventory' => true]);
        $service = new StockService;

        $service->receive($warehouse->id, $product->id, 2);
        $service->receive($warehouse->id, $product->id, 3);

        $this->assertDatabaseCount('stock_balances', 1);
        $this->assertDatabaseHas('stock_balances', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    public function test_untracked_products_cannot_be_adjusted_as_inventory(): void
    {
        $this->authUser();
        $warehouse = Warehouse::create(['branch_id' => $this->primaryBranchId(), 'code' => 'WH3', 'name' => 'Main']);
        $product = Product::create(['name' => 'Consultation', 'sku' => 'SVC1']);

        $this->expectException(\InvalidArgumentException::class);
        (new StockService)->receive($warehouse->id, $product->id, 1);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/inventory')->assertStatus(200);
    }
}
