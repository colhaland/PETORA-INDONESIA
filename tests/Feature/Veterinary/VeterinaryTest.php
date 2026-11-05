<?php

declare(strict_types=1);

namespace Tests\Feature\Veterinary;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VeterinaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_veterinary_list_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('veterinary.view');
        $this->actingAs($user)->get('/veterinary')->assertStatus(200);
    }
}
