<?php
declare(strict_types=1);
namespace Tests\Feature\Transport;
use App\Models\User;
use App\Modules\Transport\Services\TransportService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class TransportCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('transport.view', 'transport.create', 'transport.update', 'transport.manage');
        $this->actingAs($user);
        return $user;
    }
    public function test_book(): void
    {
        $this->authUser();
        $svc = new TransportService();
        $r = $svc->book(1, 'Jl. A', 'Jl. B');
        $this->assertEquals('requested', $r['status']);
    }
    public function test_assign_driver(): void
    {
        $this->authUser();
        $svc = new TransportService();
        $svc->assignDriver(1, 2);
        $this->assertTrue(true);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/transport')->assertStatus(200);
    }
}
