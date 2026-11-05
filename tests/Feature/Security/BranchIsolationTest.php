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

class BranchIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_from_other_branch_is_not_accessible_via_scoped_query(): void
    {
        $this->seed(PermissionSeeder::class);
        $branchA = Branch::create(['name' => 'Cabang A', 'code' => 'A', 'timezone' => 'Asia/Jakarta']);
        $branchB = Branch::create(['name' => 'Cabang B', 'code' => 'B', 'timezone' => 'Asia/Jakarta']);
        $user = User::factory()->create();
        $user->givePermissionTo('bookings.view');
        $user->branches()->sync([$branchA->id]);
        $this->actingAs($user);

        $customer = Customer::create(['name' => 'X', 'customer_number' => 'CUS-ISO-001']);
        $other = Booking::create([
            'booking_number' => 'BK/ISO/1',
            'branch_id' => $branchB->id,
            'customer_id' => $customer->id,
            'type' => 'grooming',
            'scheduled_at' => now(),
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $this->assertNull(Booking::query()->find($other->id));
        $this->assertSame(0, Booking::query()->whereKey($other->id)->count());
    }

    public function test_super_admin_bypasses_branch_scope_for_bookings(): void
    {
        $this->seed(PermissionSeeder::class);
        $branchB = Branch::create(['name' => 'Cabang B', 'code' => 'B', 'timezone' => 'Asia/Jakarta']);
        $user = User::factory()->create();
        $user->givePermissionTo('branches.view_all');
        $this->actingAs($user);

        $customer = Customer::create(['name' => 'X', 'customer_number' => 'CUS-ISO-002']);
        $other = Booking::create([
            'booking_number' => 'BK/ISO/2',
            'branch_id' => $branchB->id,
            'customer_id' => $customer->id,
            'type' => 'grooming',
            'scheduled_at' => now(),
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $this->assertNotNull(Booking::query()->find($other->id));
    }
}
