<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_page_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('pos.view');
        $this->actingAs($user)->get('/pos')->assertStatus(200);
    }
}
