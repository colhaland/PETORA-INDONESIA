<?php
declare(strict_types=1);
namespace Tests\Feature\Inventory;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use App\Modules\Inventory\Services\StockService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class StockCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\BranchSeeder::class);
        $user->givePermissionTo('inventory.adjust', 'inventory.view');
        $this->actingAs($user);
        return $user;
    }
    public function test_receive_stock(): void
    {
        $this->authUser();
        $wh = Warehouse::create(['branch_id' => 1, 'code' => 'WH1', 'name' => 'Main']);
        $prod = Product::create(['name' => 'Food', 'sku' => 'F1']);
        $svc = new StockService();
        $svc->receive($wh->id, $prod->id, 10);
        $this->assertDatabaseHas('stock_balances', ['warehouse_id' => $wh->id, 'product_id' => $prod->id, 'quantity' => 10]);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/inventory')->assertStatus(200);
    }
}
