<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\User;
use App\Modules\Inventory\Services\StockService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_throws_when_insufficient(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('inventory.adjust');
        $this->actingAs($user);
        $svc = new StockService;
        $this->expectException(\RuntimeException::class);
        $svc->issue(1, 1, 5);
    }
}
