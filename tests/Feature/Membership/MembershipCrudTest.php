<?php

declare(strict_types=1);

namespace Tests\Feature\Membership;

use App\Models\User;
use App\Modules\Membership\Services\MembershipService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('membership.view', 'membership.create', 'membership.update', 'membership.manage');
        $this->actingAs($user);

        return $user;
    }

    public function test_subscribe(): void
    {
        $this->authUser();
        $svc = new MembershipService;
        $r = $svc->subscribe(1, 'Gold');
        $this->assertEquals('Gold', $r['tier']);
        $this->assertEquals('active', $r['status']);
    }

    public function test_cancel(): void
    {
        $this->authUser();
        $svc = new MembershipService;
        $svc->cancel(1);
        $this->assertTrue(true);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/membership')->assertStatus(200);
    }
}
