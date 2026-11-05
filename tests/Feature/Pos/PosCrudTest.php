<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Models\Product;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\Warehouse;
use App\Modules\Pos\Services\PosService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PosCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = $this->actingAsWithPermissions(['pos.create', 'pos.cash_manage', 'pos.cash_close', 'pos.view'], 1);

        return $user;
    }

    public function test_create_order_via_service(): void
    {
        $this->authUser();
        $svc = new PosService;
        $order = $svc->createOrder($this->primaryBranchId(), null, auth()->id());
        $this->assertNotNull($order->order_number);
        $this->assertEquals('draft', $order->status);
    }

    public function test_add_item_and_pay(): void
    {
        $this->authUser();
        $svc = new PosService;
        $order = $svc->createOrder($this->primaryBranchId(), null, auth()->id());
        $svc->addItem($order, $this->product()->id, 2);
        $this->assertEquals(20000, (float) $order->fresh()->total);
        $svc->pay($order, 'cash', 20000, 'payment-order-one');
        $this->assertEquals('paid', $order->fresh()->status);
    }

    public function test_open_and_close_shift(): void
    {
        $this->authUser();
        $svc = new PosService;
        $shift = $svc->openShift($this->primaryBranchId(), auth()->id(), 100000);
        $this->assertEquals('open', $shift->status);
        $svc->closeShift($shift, 120000);
        $this->assertEquals('closed', $shift->fresh()->status);
    }

    public function test_cannot_add_item_to_paid_order(): void
    {
        $this->authUser();
        $svc = new PosService;
        $order = $svc->createOrder($this->primaryBranchId(), null, auth()->id());
        $svc->addItem($order, $this->product()->id, 1);
        $svc->pay($order, 'cash', 10000, 'payment-order-two');
        $this->expectException(\RuntimeException::class);
        $svc->addItem($order->fresh(), $this->product()->id, 1);
    }

    public function test_pay_sets_paid_at(): void
    {
        $this->authUser();
        $svc = new PosService;
        $order = $svc->createOrder($this->primaryBranchId(), null, auth()->id());
        $svc->addItem($order, $this->product()->id, 1);
        $svc->pay($order, 'cash', 10000, 'idem-key-1');
        $fresh = $order->fresh();
        $this->assertEquals('paid', $fresh->status);
        $this->assertNotNull($fresh->paid_at);
    }

    public function test_payment_is_idempotent_for_the_same_key(): void
    {
        $this->authUser();
        $svc = new PosService;
        $order = $svc->createOrder($this->primaryBranchId(), null, auth()->id());
        $svc->addItem($order, $this->product()->id, 1);

        $first = $svc->pay($order, 'cash', 10000, 'same-request');
        $second = $svc->pay($order, 'cash', 10000, 'same-request');

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, $order->payments()->count());
    }

    public function test_rejects_negative_and_excess_payments(): void
    {
        $this->authUser();
        $svc = new PosService;
        $order = $svc->createOrder($this->primaryBranchId(), null, auth()->id());
        $svc->addItem($order, $this->product()->id, 1);

        try {
            $svc->pay($order, 'cash', -1, 'negative-payment');
            $this->fail('Negative payment should be rejected.');
        } catch (\InvalidArgumentException) {
            $this->assertSame(0, $order->payments()->count());
        }

        $this->expectException(\InvalidArgumentException::class);
        $svc->pay($order, 'cash', 10001, 'excess-payment');
    }

    public function test_uses_catalog_price_and_configured_tax(): void
    {
        $this->authUser();
        config(['petora.tax.enabled' => true, 'petora.tax.rate' => 11, 'petora.tax.price_mode' => 'exclusive']);
        $svc = new PosService;
        $order = $svc->createOrder($this->primaryBranchId(), null, auth()->id());
        $product = $this->product();

        $item = $svc->addItem($order, $product->id, 1);

        $this->assertSame('10000.00', $item->unit_price);
        $this->assertSame('1100.00', $order->fresh()->tax);
        $this->assertSame('11100.00', $order->fresh()->total);
    }

    public function test_fractional_quantities_round_to_currency_precision(): void
    {
        $this->authUser();
        $service = new PosService;
        $order = $service->createOrder($this->primaryBranchId(), null, auth()->id());
        $product = $this->product();
        $product->update(['sell_price' => 10001]);

        $service->addItem($order, $product->id, 0.333);

        $this->assertSame('3330.33', $order->fresh()->subtotal);
    }

    public function test_paid_order_issues_tracked_stock_once(): void
    {
        $this->authUser();
        $warehouse = Warehouse::create([
            'branch_id' => $this->primaryBranchId(),
            'code' => 'POS1',
            'name' => 'POS warehouse',
            'is_main' => true,
        ]);
        $product = $this->product();
        $product->update(['track_inventory' => true]);
        StockBalance::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
        $service = new PosService;
        $order = $service->createOrder($this->primaryBranchId(), null, auth()->id(), $warehouse->id);
        $service->addItem($order, $product->id, 2);

        $payment = $service->pay($order, 'cash', 20000, 'tracked-order-payment');
        $replayedPayment = $service->pay($order, 'cash', 20000, 'tracked-order-payment');

        $this->assertSame($payment->id, $replayedPayment->id);
        $this->assertSame('3.000', StockBalance::query()->where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity'));
        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'type' => 'sale_out',
            'reference_type' => 'order',
            'reference_id' => $order->id,
            'quantity' => 2,
        ]);
        $this->assertNotNull($order->fresh()->stock_issued_at);
    }

    public function test_tracked_order_without_warehouse_does_not_accept_payment(): void
    {
        $this->authUser();
        $product = $this->product();
        $product->update(['track_inventory' => true]);
        $service = new PosService;
        $order = $service->createOrder($this->primaryBranchId(), null, auth()->id());
        $service->addItem($order, $product->id, 1);

        $this->expectException(\RuntimeException::class);
        $service->pay($order, 'cash', 10000, 'tracked-order-no-warehouse');
    }

    public function test_insufficient_tracked_stock_rolls_back_order_payment(): void
    {
        $this->authUser();
        $warehouse = Warehouse::create([
            'branch_id' => $this->primaryBranchId(),
            'code' => 'POS2',
            'name' => 'POS warehouse',
            'is_main' => true,
        ]);
        $product = $this->product();
        $product->update(['track_inventory' => true]);
        $balance = StockBalance::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $service = new PosService;
        $order = $service->createOrder($this->primaryBranchId(), null, auth()->id(), $warehouse->id);
        $service->addItem($order, $product->id, 2);

        try {
            $service->pay($order, 'cash', 20000, 'tracked-order-insufficient-stock');
            $this->fail('Insufficient stock must reject payment.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Stok tidak mencukupi.', $exception->getMessage());
        }

        $this->assertSame(0, $order->payments()->count());
        $this->assertSame('1.000', $balance->fresh()->quantity);
        $this->assertNull($order->fresh()->stock_issued_at);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    private function product(): Product
    {
        return Product::create([
            'name' => 'Makanan',
            'sku' => (string) Str::uuid(),
            'sell_price' => 10000,
            'min_price' => 5000,
            'status' => 'active',
        ]);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/pos')->assertStatus(200);
    }

    public function test_postgresql_rejects_order_discounts_until_policy_is_set(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Discount database constraints are PostgreSQL-specific.');
        }

        $this->authUser();
        $order = (new PosService)->createOrder($this->primaryBranchId(), null, auth()->id());

        $this->expectException(QueryException::class);
        DB::table('orders')->where('id', $order->id)->update(['discount' => 1]);
    }

    public function test_postgresql_rejects_order_item_discounts_until_policy_is_set(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Discount database constraints are PostgreSQL-specific.');
        }

        $this->authUser();
        $service = new PosService;
        $order = $service->createOrder($this->primaryBranchId(), null, auth()->id());
        $item = $service->addItem($order, $this->product()->id, 1);

        $this->expectException(QueryException::class);
        DB::table('order_items')->where('id', $item->id)->update(['discount' => 1]);
    }
}
