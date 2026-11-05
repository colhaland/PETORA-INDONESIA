<?php

declare(strict_types=1);

namespace Tests\Feature\Purchasing;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Modules\Purchasing\Services\PurchasingService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchasingCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $this->seed(BranchSeeder::class);
        $user->givePermissionTo('purchasing.create', 'purchasing.approve', 'purchasing.receive', 'purchasing.view');
        $user->branches()->sync([$this->primaryBranchId()]);
        $this->actingAs($user);

        return $user;
    }

    public function test_create_po_via_service(): void
    {
        $this->authUser();
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $po = $this->createPo($supplier->id);
        $this->assertNotNull($po->po_number);
        $this->assertEquals('draft', $po->status);
        $this->assertSame('25000.00', $po->total);
        $this->assertCount(1, $po->items);
    }

    public function test_approve_po(): void
    {
        $this->authUser();
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $po = $this->createPo($supplier->id);
        (new PurchasingService)->approve($po);
        $this->assertEquals('approved', $po->fresh()->status);
    }

    public function test_receive_po(): void
    {
        $this->authUser();
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $po = $this->createPo($supplier->id);
        $service = new PurchasingService;
        $service->approve($po);
        $warehouse = $this->warehouse();
        $gr = $service->receive($po, [[
            'purchase_order_item_id' => $po->items->first()->id,
            'quantity' => 5,
            'warehouse_id' => $warehouse->id,
        ]], 'receipt-full', auth()->id());
        $this->assertEquals('received', $po->fresh()->status);
        $this->assertNotNull($gr);
        $this->assertDatabaseHas('stock_balances', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $po->items->first()->product_id,
            'quantity' => 5,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'purchase_in',
            'reference_type' => 'goods_receipt',
            'reference_id' => $gr->items->first()->id,
        ]);
    }

    public function test_partial_receipts_are_idempotent_and_cannot_exceed_ordered_quantity(): void
    {
        $this->authUser();
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $po = $this->createPo($supplier->id);
        $service = new PurchasingService;
        $service->approve($po);
        $warehouse = $this->warehouse();
        $line = [
            'purchase_order_item_id' => $po->items->first()->id,
            'quantity' => 2,
            'warehouse_id' => $warehouse->id,
        ];

        $receipt = $service->receive($po, [$line], 'receipt-part-1', auth()->id());
        $replayedReceipt = $service->receive($po, [$line], 'receipt-part-1', auth()->id());
        $this->assertSame($receipt->id, $replayedReceipt->id);
        $this->assertDatabaseCount('goods_receipts', 1);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertNotNull($receipt->items->first()->stock_applied_at);
        $this->assertSame('partially_received', $po->fresh()->status);

        try {
            $service->receive($po, [[...$line, 'quantity' => 4]], 'receipt-too-many', auth()->id());
            $this->fail('Receiving more than the remaining quantity must be rejected.');
        } catch (\InvalidArgumentException) {
            $this->assertDatabaseCount('goods_receipts', 1);
        }

        $service->receive($po, [[...$line, 'quantity' => 3]], 'receipt-part-2', auth()->id());
        $this->assertSame('received', $po->fresh()->status);
        $this->assertDatabaseHas('stock_balances', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $po->items->first()->product_id,
            'quantity' => 5,
        ]);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/purchasing')->assertStatus(200);
    }

    public function test_create_page_loads(): void
    {
        $this->authUser();
        $this->get('/purchasing/create')->assertStatus(200);
    }

    private function createPo(int $supplierId): PurchaseOrder
    {
        $product = Product::create([
            'name' => 'Food',
            'sku' => 'FOOD-'.Str::upper(Str::random(6)),
            'track_inventory' => true,
        ]);

        return (new PurchasingService)->createPo($this->primaryBranchId(), $supplierId, [[
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_cost' => 5000,
        ]], auth()->id());
    }

    private function warehouse(): Warehouse
    {
        return Warehouse::create([
            'branch_id' => $this->primaryBranchId(),
            'code' => 'WH'.Str::upper(Str::random(6)),
            'name' => 'Receiving warehouse',
        ]);
    }
}
