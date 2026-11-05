<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Models\Product;
use App\Models\User;
use App\Modules\Pos\Services\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $order = $svc->createOrder(1, null, auth()->id());
        $this->assertNotNull($order->order_number);
        $this->assertEquals('draft', $order->status);
    }

    public function test_add_item_and_pay(): void
    {
        $this->authUser();
        $svc = new PosService;
        $order = $svc->createOrder(1, null, auth()->id());
        $svc->addItem($order, $this->product()->id, 2);
        $this->assertEquals(20000, (float) $order->fresh()->total);
        $svc->pay($order, 'cash', 20000, 'payment-order-one');
        $this->assertEquals('paid', $order->fresh()->status);
    }

    public function test_open_and_close_shift(): void
    {
        $this->authUser();
        $svc = new PosService;
        $shift = $svc->openShift(1, auth()->id(), 100000);
        $this->assertEquals('open', $shift->status);
        $svc->closeShift($shift, 120000);
        $this->assertEquals('closed', $shift->fresh()->status);
    }

    public function test_cannot_add_item_to_paid_order(): void
    {
        $this->authUser();
        $svc = new PosService;
        $order = $svc->createOrder(1, null, auth()->id());
        $svc->addItem($order, $this->product()->id, 1);
        $svc->pay($order, 'cash', 10000, 'payment-order-two');
        $this->expectException(\RuntimeException::class);
        $svc->addItem($order->fresh(), $this->product()->id, 1);
    }

    public function test_pay_sets_paid_at(): void
    {
        $this->authUser();
        $svc = new PosService;
        $order = $svc->createOrder(1, null, auth()->id());
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
        $order = $svc->createOrder(1, null, auth()->id());
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
        $order = $svc->createOrder(1, null, auth()->id());
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
        $order = $svc->createOrder(1, null, auth()->id());
        $product = $this->product();

        $item = $svc->addItem($order, $product->id, 1);

        $this->assertSame('10000.00', $item->unit_price);
        $this->assertSame('1100.00', $order->fresh()->tax);
        $this->assertSame('11100.00', $order->fresh()->total);
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
}
