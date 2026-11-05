<?php

declare(strict_types=1);

namespace Tests\Feature\Veterinary;

use App\Models\Customer;
use App\Models\Pet;
use App\Models\User;
use App\Modules\Veterinary\Services\VeterinaryService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VeterinaryCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $this->seed(BranchSeeder::class);
        $user->givePermissionTo('veterinary.create', 'veterinary.update', 'veterinary.view', 'medical_records.finalize', 'medical_records.create', 'medical_records.update');
        $user->branches()->sync([1]);
        $this->actingAs($user);

        return $user;
    }

    public function test_create_visit_via_service(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $pet = Pet::create(['name' => 'Bobby', 'species' => 'dog', 'customer_id' => $c->id, 'pet_code' => 'PET-000001']);
        $svc = new VeterinaryService;
        $v = $svc->createVisit(['branch_id' => 1, 'pet_id' => $pet->id, 'doctor_id' => auth()->id(), 'subjective' => 'Demam']);
        $this->assertEquals('draft', $v->status);
    }

    public function test_finalize_visit(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $pet = Pet::create(['name' => 'Bobby', 'species' => 'dog', 'customer_id' => $c->id, 'pet_code' => 'PET-000001']);
        $svc = new VeterinaryService;
        $v = $svc->createVisit(['branch_id' => 1, 'pet_id' => $pet->id, 'doctor_id' => auth()->id()]);
        $svc->finalize($v);
        $this->assertEquals('final', $v->fresh()->status);
        $this->assertNotNull($v->fresh()->finalized_at);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/veterinary')->assertStatus(200);
    }

    public function test_create_page_loads(): void
    {
        $this->authUser();
        $this->get('/veterinary/create')->assertStatus(200);
    }
}
