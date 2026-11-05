<?php
declare(strict_types=1);
namespace Tests\Feature\Grooming;
use App\Models\User;
use App\Models\Pet;
use App\Models\Customer;
use App\Modules\Grooming\Services\GroomingSessionService;
use App\Modules\Grooming\Services\GroomingServiceService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class GroomingCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\BranchSeeder::class);
        $user->givePermissionTo('grooming.create', 'grooming.checkin', 'grooming.qc', 'grooming.view', 'grooming.manage');
        $this->actingAs($user);
        return $user;
    }
    public function test_create_service_via_service(): void
    {
        $this->authUser();
        $svc = new GroomingServiceService();
        $s = $svc->create(['name' => 'Grooming Kecil']);
        $this->assertEquals('Grooming Kecil', $s->name);
    }
    public function test_create_session_via_service(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $pet = Pet::create(['name' => 'Bobby', 'species' => 'dog', 'customer_id' => $c->id, 'pet_code' => 'PET-000001']);
        $svc = new GroomingSessionService();
        $s = $svc->create(['branch_id' => 1, 'pet_id' => $pet->id]);
        $this->assertEquals('pending', $s->status);
    }
    public function test_checkin_session(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $pet = Pet::create(['name' => 'Bobby', 'species' => 'dog', 'customer_id' => $c->id, 'pet_code' => 'PET-000001']);
        $svc = new GroomingSessionService();
        $s = $svc->create(['branch_id' => 1, 'pet_id' => $pet->id]);
        $svc->checkIn($s);
        $this->assertEquals('checked_in', $s->fresh()->status);
    }
    public function test_complete_session(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $pet = Pet::create(['name' => 'Bobby', 'species' => 'dog', 'customer_id' => $c->id, 'pet_code' => 'PET-000001']);
        $svc = new GroomingSessionService();
        $s = $svc->create(['branch_id' => 1, 'pet_id' => $pet->id]);
        $svc->complete($s);
        $this->assertEquals('completed', $s->fresh()->status);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/grooming')->assertStatus(200);
    }
    public function test_create_page_loads(): void
    {
        $this->authUser();
        $this->get('/grooming/create')->assertStatus(200);
    }
}
