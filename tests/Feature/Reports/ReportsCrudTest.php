<?php
declare(strict_types=1);
namespace Tests\Feature\Reports;
use App\Models\User;
use App\Modules\Reports\Services\ReportsService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class ReportsCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('reports.view', 'reports.export');
        $this->actingAs($user);
        return $user;
    }
    public function test_sales_summary(): void
    {
        $this->authUser();
        $svc = new ReportsService();
        $r = $svc->salesSummary();
        $this->assertArrayHasKey('total_orders', $r);
        $this->assertArrayHasKey('total_revenue', $r);
    }
    public function test_customer_summary(): void
    {
        $this->authUser();
        $svc = new ReportsService();
        $r = $svc->customerSummary();
        $this->assertArrayHasKey('total_customers', $r);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/reports')->assertStatus(200);
    }
}
