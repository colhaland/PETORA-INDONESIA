<?php

declare(strict_types=1);

namespace Tests\Feature\Promotion;

use App\Models\User;
use App\Modules\Promotion\Services\PromotionService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('promotions.view', 'promotions.create', 'promotions.update', 'promotions.manage', 'promotions.approve');
        $this->actingAs($user);

        return $user;
    }

    public function test_apply_discount(): void
    {
        $this->authUser();
        $svc = new PromotionService;
        $this->assertEquals(90.0, $svc->applyDiscount(100, 10));
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/promotion')->assertStatus(200);
    }
}
