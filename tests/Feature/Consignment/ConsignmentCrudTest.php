<?php
declare(strict_types=1);
namespace Tests\Feature\Consignment;
use App\Models\User;
use App\Modules\Consignment\Services\ConsignmentService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class ConsignmentCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('consignment.view', 'consignment.create', 'consignment.update', 'consignment.manage');
        $this->actingAs($user);
        return $user;
    }
    public function test_create_contract(): void
    {
        $this->authUser();
        $svc = new ConsignmentService();
        $r = $svc->createContract(1, 10);
        $this->assertEquals('active', $r['status']);
        $this->assertEquals(10, $r['commission']);
    }
    public function test_settle(): void
    {
        $this->authUser();
        $svc = new ConsignmentService();
        $svc->settle(1);
        $this->assertTrue(true);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/consignment')->assertStatus(200);
    }
}
