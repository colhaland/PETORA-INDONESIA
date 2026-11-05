<?php
declare(strict_types=1);
namespace Tests\Feature\Pets;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class PetTest extends TestCase
{
    use RefreshDatabase;
    public function test_pet_list_loads(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/pets')->assertStatus(200);
    }
}
