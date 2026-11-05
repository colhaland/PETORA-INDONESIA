<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderBranchIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_from_other_branch_is_hidden_by_scope(): void
    {
        $this->seed(PermissionSeeder::class);
        $branchA = Branch::create(['name' => 'Cabang A', 'code' => 'A', 'timezone' => 'Asia/Jakarta']);
        $branchB = Branch::create(['name' => 'Cabang B', 'code' => 'B', 'timezone' => 'Asia/Jakarta']);
        $user = User::factory()->create();
        $user->givePermissionTo('pos.view');
        $user->branches()->sync([$branchA->id]);
        $this->actingAs($user);

        $other = Order::create([
            'order_number' => 'ORD/ISO/1',
            'branch_id' => $branchB->id,
            'status' => 'draft',
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
        ]);

        $this->assertNull(Order::query()->find($other->id));
    }
}
