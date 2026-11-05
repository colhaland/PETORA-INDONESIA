<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_list_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('inventory.view');
        $this->actingAs($user)->get('/inventory')->assertStatus(200);
    }
}
