<?php

declare(strict_types=1);

namespace Tests\Feature\Booking;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_list_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('bookings.view');
        $this->actingAs($user)->get('/booking')->assertStatus(200);
    }
}
