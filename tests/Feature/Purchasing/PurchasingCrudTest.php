<?php
declare(strict_types=1);
namespace Tests\Feature\Purchasing;
use App\Models\User;
use App\Models\Supplier;
use App\Modules\Purchasing\Services\PurchasingService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class PurchasingCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\BranchSeeder::class);
        $user->givePermissionTo('purchasing.create', 'purchasing.approve', 'purchasing.receive', 'purchasing.view');
        $this->actingAs($user);
        return $user;
    }
    public function test_create_po_via_service(): void
    {
        $this->authUser();
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $svc = new PurchasingService();
        $po = $svc->createPo(1, $supplier->id, 100000, auth()->id());
        $this->assertNotNull($po->po_number);
        $this->assertEquals('draft', $po->status);
    }
    public function test_approve_po(): void
    {
        $this->authUser();
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $svc = new PurchasingService();
        $po = $svc->createPo(1, $supplier->id, 100000, auth()->id());
        $svc->approve($po);
        $this->assertEquals('approved', $po->fresh()->status);
    }
    public function test_receive_po(): void
    {
        $this->authUser();
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $svc = new PurchasingService();
        $po = $svc->createPo(1, $supplier->id, 100000, auth()->id());
        $svc->approve($po);
        $gr = $svc->receive($po, auth()->id());
        $this->assertEquals('received', $po->fresh()->status);
        $this->assertNotNull($gr);
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
}
