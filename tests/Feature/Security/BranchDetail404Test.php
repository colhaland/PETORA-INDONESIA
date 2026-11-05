<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchDetail404Test extends TestCase
{
    use RefreshDatabase;

    public function test_booking_detail_from_other_branch_returns_404(): void
    {
        $this->seed(PermissionSeeder::class);
        $branchA = Branch::create(['name' => 'Cabang A', 'code' => 'A', 'timezone' => 'Asia/Jakarta']);
        $branchB = Branch::create(['name' => 'Cabang B', 'code' => 'B', 'timezone' => 'Asia/Jakarta']);
        $user = User::factory()->create();
        $user->givePermissionTo('bookings.view');
        $user->branches()->sync([$branchA->id]);
        $this->actingAs($user);

        $customer = Customer::create(['name' => 'X', 'customer_number' => 'CUS-B404-1']);
        $other = Booking::create([
            'booking_number' => 'BK/B404/1',
            'branch_id' => $branchB->id,
            'customer_id' => $customer->id,
            'type' => 'grooming',
            'scheduled_at' => now(),
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $this->get("/booking/{$other->id}")->assertNotFound();
    }
}
