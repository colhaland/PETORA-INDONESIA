<?php

declare(strict_types=1);

namespace Tests\Feature\CustomerPortal;

use App\Models\User;
use App\Modules\CustomerPortal\Services\CustomerPortalService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('portal.view');
        $this->actingAs($user);

        return $user;
    }

    public function test_register(): void
    {
        $this->authUser();
        $svc = new CustomerPortalService;
        $r = $svc->register(['name' => 'Budi', 'email' => 'budi@test.com']);
        $this->assertEquals('pending_verification', $r['status']);
    }

    public function test_submit_complaint(): void
    {
        $this->authUser();
        $svc = new CustomerPortalService;
        $r = $svc->submitComplaint(1, 'Keluhan', 'Pesan');
        $this->assertEquals('open', $r['status']);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/customer-portal')->assertStatus(200);
    }
}
