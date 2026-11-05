<?php
declare(strict_types=1);
namespace Tests\Feature\Pos;
use App\Models\User;
use App\Models\Order;
use App\Modules\Pos\Services\PosService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class PosCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\BranchSeeder::class);
        $user->givePermissionTo('pos.create', 'pos.cash_manage', 'pos.cash_close', 'pos.view');
        $this->actingAs($user);
        return $user;
    }
    public function test_create_order_via_service(): void
    {
        $this->authUser();
        $svc = new PosService();
        $order = $svc->createOrder(1, null, auth()->id());
        $this->assertNotNull($order->order_number);
        $this->assertEquals('draft', $order->status);
    }
    public function test_add_item_and_pay(): void
    {
        $this->authUser();
        $svc = new PosService();
        $order = $svc->createOrder(1, null, auth()->id());
        $svc->addItem($order, 'Makanan', 2, 10000);
        $this->assertEquals(20000, (float) $order->fresh()->total);
        $svc->pay($order, 'cash', 20000);
        $this->assertEquals('paid', $order->fresh()->status);
    }
    public function test_open_and_close_shift(): void
    {
        $this->authUser();
        $svc = new PosService();
        $shift = $svc->openShift(1, auth()->id(), 100000);
        $this->assertEquals('open', $shift->status);
        $svc->closeShift($shift, 120000);
        $this->assertEquals('closed', $shift->fresh()->status);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/pos')->assertStatus(200);
    }
}
