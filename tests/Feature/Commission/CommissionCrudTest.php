<?php

declare(strict_types=1);

namespace Tests\Feature\Commission;

use App\Models\User;
use App\Modules\Commission\Services\CommissionService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('commission.view', 'commission.manage', 'commission.approve', 'commission.pay');
        $this->actingAs($user);

        return $user;
    }

    public function test_calculate_percentage(): void
    {
        $this->authUser();
        $svc = new CommissionService;
        $this->assertEquals(10.0, $svc->calculate(100, 10, 'percentage'));
    }

    public function test_calculate_nominal(): void
    {
        $this->authUser();
        $svc = new CommissionService;
        $this->assertEquals(5.0, $svc->calculate(100, 5, 'nominal'));
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/commission')->assertStatus(200);
    }
}
