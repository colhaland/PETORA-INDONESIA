<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Modules\Reports\Services\ReportsService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $this->seed(BranchSeeder::class);
        $user->givePermissionTo('reports.view', 'reports.export');
        $user->branches()->sync([$this->primaryBranchId()]);
        $this->actingAs($user);

        return $user;
    }

    public function test_sales_summary(): void
    {
        $this->authUser();
        $svc = new ReportsService;
        $r = $svc->salesSummary();
        $this->assertArrayHasKey('total_orders', $r);
        $this->assertArrayHasKey('total_revenue', $r);
    }

    public function test_sales_summary_includes_only_paid_orders_from_the_active_branch(): void
    {
        $this->authUser();
        $branchId = $this->primaryBranchId();
        $otherBranch = Branch::create(['code' => 'CBG', 'name' => 'Cabang Bogor', 'status' => 'active']);

        $this->order($branchId, 'paid', 100);
        $this->order($branchId, 'completed', 50);
        $this->order($branchId, 'draft', 900);
        $this->order($branchId, 'partially_paid', 800);
        $this->order($branchId, 'void', 700);
        $this->order($branchId, 'refunded', 600);
        $this->order((int) $otherBranch->id, 'paid', 500);

        $summary = (new ReportsService)->salesSummary();

        $this->assertSame(2, $summary['total_orders']);
        $this->assertSame(150.0, $summary['total_revenue']);
    }

    public function test_customer_summary(): void
    {
        $this->authUser();
        $svc = new ReportsService;
        $r = $svc->customerSummary();
        $this->assertArrayHasKey('total_customers', $r);
    }

    public function test_customer_summary_only_counts_customers_with_orders_in_the_active_branch(): void
    {
        $this->authUser();
        $branchId = $this->primaryBranchId();
        $otherBranch = Branch::create(['code' => 'CBG', 'name' => 'Cabang Bogor', 'status' => 'active']);
        $localCustomer = $this->customer('CUS-LOCAL');
        $otherCustomer = $this->customer('CUS-OTHER');
        $this->order($branchId, 'paid', 10, (int) $localCustomer->id);
        $this->order($branchId, 'completed', 10, (int) $localCustomer->id);
        $this->order((int) $otherBranch->id, 'paid', 10, (int) $otherCustomer->id);

        $summary = (new ReportsService)->customerSummary();

        $this->assertSame(1, $summary['total_customers']);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/reports')->assertStatus(200);
    }

    private function order(int $branchId, string $status, int $total, ?int $customerId = null): Order
    {
        return Order::create([
            'order_number' => fake()->unique()->bothify('INV-######'),
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'status' => $status,
            'subtotal' => $total,
            'total' => $total,
            'paid_at' => in_array($status, ['paid', 'completed'], true) ? now() : null,
        ]);
    }

    private function customer(string $customerNumber): Customer
    {
        return Customer::create(['customer_number' => $customerNumber, 'name' => $customerNumber]);
    }
}
