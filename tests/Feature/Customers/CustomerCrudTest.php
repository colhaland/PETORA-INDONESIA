<?php
declare(strict_types=1);
namespace Tests\Feature\Customers;
use App\Models\Customer;
use App\Models\User;
use App\Modules\Customers\Services\CustomerService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('customers.create', 'customers.update', 'customers.delete', 'customers.view');
        $this->actingAs($user);
        return $user;
    }
    public function test_create_customer_via_service(): void
    {
        $this->authUser();
        $svc = new CustomerService();
        $c = $svc->create(['name' => 'Budi']);
        $this->assertNotNull($c->customer_number);
        $this->assertEquals('Budi', $c->name);
    }
    public function test_update_customer(): void
    {
        $this->authUser();
        $svc = new CustomerService();
        $c = $svc->create(['name' => 'Budi']);
        $svc->update($c, ['name' => 'Andi']);
        $this->assertEquals('Andi', $c->fresh()->name);
    }
    public function test_delete_customer(): void
    {
        $this->authUser();
        $svc = new CustomerService();
        $c = $svc->create(['name' => 'Budi']);
        $svc->delete($c);
        $this->assertSoftDeleted('customers', ['id' => $c->id]);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/customers')->assertStatus(200);
    }
    public function test_detail_page_loads(): void
    {
        $this->authUser();
        $svc = new CustomerService();
        $c = $svc->create(['name' => 'Budi']);
        $this->get('/customers/' . $c->id)->assertStatus(200);
    }
    public function test_create_page_loads(): void
    {
        $this->authUser();
        $this->get('/customers/create')->assertStatus(200);
    }
}
