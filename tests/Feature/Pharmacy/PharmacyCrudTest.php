<?php

declare(strict_types=1);

namespace Tests\Feature\Pharmacy;

use App\Models\User;
use App\Modules\Pharmacy\Services\PharmacyService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('pharmacy.view', 'pharmacy.manage', 'pharmacy.dispense');
        $this->actingAs($user);

        return $user;
    }

    public function test_create_medicine_via_service(): void
    {
        $this->authUser();
        $svc = new PharmacyService;
        $m = $svc->create(['name' => 'Paracetamol', 'category' => 'medicine']);
        $this->assertEquals('Paracetamol', $m->name);
    }

    public function test_update_medicine(): void
    {
        $this->authUser();
        $svc = new PharmacyService;
        $m = $svc->create(['name' => 'Paracetamol', 'category' => 'medicine']);
        $svc->update($m, ['name' => 'Paracetamol 500mg']);
        $this->assertEquals('Paracetamol 500mg', $m->fresh()->name);
    }

    public function test_delete_medicine(): void
    {
        $this->authUser();
        $svc = new PharmacyService;
        $m = $svc->create(['name' => 'Paracetamol', 'category' => 'medicine']);
        $svc->delete($m);
        $this->assertDatabaseMissing('medicines', ['id' => $m->id]);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/pharmacy')->assertStatus(200);
    }

    public function test_create_page_loads(): void
    {
        $this->authUser();
        $this->get('/pharmacy/create')->assertStatus(200);
    }
}
