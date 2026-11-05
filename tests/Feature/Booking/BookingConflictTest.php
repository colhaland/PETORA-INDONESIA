<?php

declare(strict_types=1);

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\User;
use App\Modules\Booking\Services\BookingService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_conflict_is_rejected_when_window_overlaps(): void
    {
        $this->seed([PermissionSeeder::class, BranchSeeder::class]);
        $user = User::factory()->create();
        $user->givePermissionTo('bookings.create');
        $branchId = $this->primaryBranchId();
        $user->branches()->sync([$branchId]);
        $this->actingAs($user);

        $customer = Customer::create(['name' => 'A', 'customer_number' => 'CUS-000001']);
        Booking::create([
            'booking_number' => 'BK/TEST/1',
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'resource_id' => 10,
            'type' => 'grooming',
            'scheduled_at' => now()->addHour(),
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(2),
            'status' => 'confirmed',
            'created_by' => $user->id,
        ]);

        $svc = new BookingService;
        $this->expectException(\RuntimeException::class);
        $svc->create([
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'resource_id' => 10,
            'type' => 'grooming',
            'scheduled_at' => now()->addMinutes(90),
            'starts_at' => now()->addMinutes(90),
            'ends_at' => now()->addHours(3),
        ]);
    }
}
