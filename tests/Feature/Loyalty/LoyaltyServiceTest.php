<?php

declare(strict_types=1);

namespace Tests\Feature\Loyalty;

use App\Models\User;
use App\Modules\Loyalty\Services\LoyaltyService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_earn_points(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('loyalty.view', 'loyalty.adjust', 'loyalty.manage');
        $this->actingAs($user);
        $svc = new LoyaltyService;
        $this->assertEquals(1, $svc->earn(1, 10000));
    }

    public function test_redeem_points(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('loyalty.view', 'loyalty.adjust', 'loyalty.manage');
        $this->actingAs($user);
        $svc = new LoyaltyService;
        $this->assertEquals(10000, $svc->redeem(1, 100));
    }
}
