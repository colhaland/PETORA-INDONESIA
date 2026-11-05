<?php
declare(strict_types=1);
namespace Tests\Feature\Staff;
use App\Models\User;
use App\Modules\Staff\Services\StaffService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class StaffCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('staff.view', 'staff.create', 'staff.update', 'staff.manage');
        $this->actingAs($user);
        return $user;
    }
    public function test_create_staff_via_service(): void
    {
        $this->authUser();
        $svc = new StaffService();
        $u = $svc->create(['name' => 'Budi', 'email' => 'budi@test.com', 'password' => bcrypt('secret')]);
        $this->assertEquals('Budi', $u->name);
    }
    public function test_update_staff(): void
    {
        $this->authUser();
        $svc = new StaffService();
        $u = $svc->create(['name' => 'Budi', 'email' => 'budi@test.com', 'password' => bcrypt('secret')]);
        $svc->update($u, ['name' => 'Andi']);
        $this->assertEquals('Andi', $u->fresh()->name);
    }
    public function test_deactivate_staff(): void
    {
        $this->authUser();
        $svc = new StaffService();
        $u = $svc->create(['name' => 'Budi', 'email' => 'budi@test.com', 'password' => bcrypt('secret')]);
        $svc->deactivate($u);
        $this->assertEquals('inactive', $u->fresh()->status);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/staff')->assertStatus(200);
    }
    public function test_create_page_loads(): void
    {
        $this->authUser();
        $this->get('/staff/create')->assertStatus(200);
    }
}
