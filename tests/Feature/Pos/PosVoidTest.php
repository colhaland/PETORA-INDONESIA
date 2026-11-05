<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Models\User;
use App\Modules\Pos\Services\PosService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosVoidTest extends TestCase
{
    use RefreshDatabase;

    public function test_void_draft_order(): void
    {
        $this->seed([PermissionSeeder::class, BranchSeeder::class]);
        $user = User::factory()->create();
        $user->givePermissionTo('pos.create', 'pos.void');
        $user->branches()->sync([1]);
        $this->actingAs($user);
        $svc = new PosService;
        $order = $svc->createOrder(1, null, $user->id);
        $svc->voidOrder($order, 'Test void');
        $this->assertSame('void', $order->fresh()->status);
    }
}
