<?php

declare(strict_types=1);

namespace Tests\Feature\Pets;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetTest extends TestCase
{
    use RefreshDatabase;

    public function test_pet_list_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('pets.view');
        $this->actingAs($user)->get('/pets')->assertStatus(200);
    }
}
