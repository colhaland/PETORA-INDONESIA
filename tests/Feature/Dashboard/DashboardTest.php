<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_active_branch_metrics_only(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(BranchSeeder::class);
        $branchId = $this->primaryBranchId();
        $user = User::factory()->create();
        $user->givePermissionTo('dashboard.view', 'reports.view', 'pos.create', 'customers.create');
        $user->branches()->sync([$branchId]);
        $this->actingAs($user);
        $customer = Customer::create(['customer_number' => 'CUS-DASH', 'name' => 'Pelanggan Dashboard']);
        $this->order($branchId, 'paid', 12345, $customer->id, now());
        $this->order($branchId, 'draft', 99000, null, null);
        $otherBranch = Branch::create(['code' => 'CBG', 'name' => 'Cabang Bogor', 'status' => 'active']);
        $this->order((int) $otherBranch->id, 'paid', 500000, null, now());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pusat')
            ->assertSee('Rp 12.345')
            ->assertViewHas('orders_today', 1)
            ->assertViewHas('revenue_today', 12345.0)
            ->assertViewHas('customers', 1);
    }

    public function test_dashboard_requires_an_authorized_active_branch(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('dashboard.view');
        $this->actingAs($user);

        $this->get(route('dashboard'))->assertForbidden();
    }

    private function order(int $branchId, string $status, int $total, ?int $customerId, mixed $paidAt): Order
    {
        return Order::create([
            'order_number' => fake()->unique()->bothify('INV-######'),
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'status' => $status,
            'subtotal' => $total,
            'total' => $total,
            'paid_at' => $paidAt,
        ]);
    }
}
