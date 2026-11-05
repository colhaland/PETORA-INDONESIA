<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Models\User;
use App\Modules\Pos\Services\PosService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $this->seed(BranchSeeder::class);
        $user->givePermissionTo('pos.create', 'pos.cash_manage', 'pos.cash_close', 'pos.view');
        $this->actingAs($user);

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
        $svc->addItem($order, 'Makanan', 2, 10000);
        $this->assertEquals(20000, (float) $order->fresh()->total);
        $svc->pay($order, 'cash', 20000);
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
        $svc->addItem($order, 'Makanan', 1, 10000);
        $svc->pay($order, 'cash', 10000);
        $this->expectException(\RuntimeException::class);
        $svc->addItem($order->fresh(), 'Extra', 1, 5000);
    }

    public function test_pay_sets_paid_at(): void
    {
        $this->authUser();
        $svc = new PosService;
        $order = $svc->createOrder(1, null, auth()->id());
        $svc->addItem($order, 'Makanan', 1, 10000);
        $svc->pay($order, 'cash', 10000, 'idem-key-1');
        $fresh = $order->fresh();
        $this->assertEquals('paid', $fresh->status);
        $this->assertNotNull($fresh->paid_at);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/pos')->assertStatus(200);
    }
}
