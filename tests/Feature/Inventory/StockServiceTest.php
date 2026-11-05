<?php
declare(strict_types=1);
namespace Tests\Feature\Inventory;
use App\Modules\Inventory\Services\StockService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class StockServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_issue_throws_when_insufficient(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('inventory.adjust');
        $this->actingAs($user);
        $svc = new StockService();
        $this->expectException(\RuntimeException::class);
        $svc->issue(1, 1, 5);
    }
}
