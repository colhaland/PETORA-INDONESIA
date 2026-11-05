<?php

declare(strict_types=1);

namespace Tests\Feature\Booking;

use App\Models\Customer;
use App\Models\User;
use App\Modules\Booking\Services\BookingService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $this->seed(BranchSeeder::class);
        $user->givePermissionTo('bookings.create', 'bookings.confirm', 'bookings.cancel', 'bookings.view');
        $this->actingAs($user);

        return $user;
    }

    public function test_create_booking_via_service(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $svc = new BookingService;
        $b = $svc->create(['branch_id' => 1, 'customer_id' => $c->id, 'type' => 'grooming', 'scheduled_at' => now()]);
        $this->assertNotNull($b->booking_number);
        $this->assertEquals('pending', $b->status);
    }

    public function test_confirm_booking(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $svc = new BookingService;
        $b = $svc->create(['branch_id' => 1, 'customer_id' => $c->id, 'type' => 'grooming', 'scheduled_at' => now()]);
        $svc->confirm($b);
        $this->assertEquals('confirmed', $b->fresh()->status);
    }

    public function test_cancel_booking(): void
    {
        $this->authUser();
        $c = Customer::create(['name' => 'Budi', 'customer_number' => 'CUS-000001']);
        $svc = new BookingService;
        $b = $svc->create(['branch_id' => 1, 'customer_id' => $c->id, 'type' => 'grooming', 'scheduled_at' => now()]);
        $svc->cancel($b, 'Customer request');
        $this->assertEquals('cancelled', $b->fresh()->status);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/booking')->assertStatus(200);
    }

    public function test_create_page_loads(): void
    {
        $this->authUser();
        $this->get('/booking/create')->assertStatus(200);
    }
}
