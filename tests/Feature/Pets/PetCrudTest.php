<?php

declare(strict_types=1);

namespace Tests\Feature\Pets;

use App\Models\Customer;
use App\Models\User;
use App\Modules\Pets\Services\PetService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('pets.create', 'pets.update', 'pets.delete', 'pets.view');
        $this->actingAs($user);

        return $user;
    }

    public function test_create_pet_via_service(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $svc = new PetService;
        $p = $svc->create(['name' => 'Bobby', 'species' => 'dog', 'customer_id' => $c->id]);
        $this->assertNotNull($p->pet_code);
        $this->assertEquals('Bobby', $p->name);
    }

    public function test_update_pet(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $svc = new PetService;
        $p = $svc->create(['name' => 'Bobby', 'species' => 'dog', 'customer_id' => $c->id]);
        $svc->update($p, ['name' => 'Rocky']);
        $this->assertEquals('Rocky', $p->fresh()->name);
    }

    public function test_delete_pet(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $svc = new PetService;
        $p = $svc->create(['name' => 'Bobby', 'species' => 'dog', 'customer_id' => $c->id]);
        $svc->delete($p);
        $this->assertSoftDeleted('pets', ['id' => $p->id]);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/pets')->assertStatus(200);
    }

    public function test_create_page_loads(): void
    {
        $this->authUser();
        $this->get('/pets/create')->assertStatus(200);
    }
}
