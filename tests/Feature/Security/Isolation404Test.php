<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Isolation404Test extends TestCase
{
    use RefreshDatabase;

    public function test_customer_detail_returns_404_for_unknown_id(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('customers.view');
        $this->actingAs($user);
        $this->get('/customers/999999')->assertNotFound();
    }

    public function test_pet_detail_returns_404_for_unknown_id(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('pets.view');
        $this->actingAs($user);
        $this->get('/pets/999999')->assertNotFound();
    }

    public function test_product_detail_returns_404_for_unknown_id(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('products.view');
        $this->actingAs($user);
        $this->get('/products/999999')->assertNotFound();
    }

    public function test_booking_detail_returns_404_for_unknown_id(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('bookings.view');
        $this->actingAs($user);
        $this->get('/booking/999999')->assertNotFound();
    }

    public function test_veterinary_detail_returns_404_for_unknown_id(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('veterinary.view');
        $this->actingAs($user);
        $this->get('/veterinary/999999')->assertNotFound();
    }
}
