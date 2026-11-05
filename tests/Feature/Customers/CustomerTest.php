<?php
declare(strict_types=1);
namespace Tests\Feature\Customers;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class CustomerTest extends TestCase
{
    use RefreshDatabase;
    public function test_customer_list_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('customers.view');
        $this->actingAs($user)->get('/customers')->assertStatus(200);
    }
}
