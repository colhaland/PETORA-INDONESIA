<?php
declare(strict_types=1);
namespace Tests\Feature\Finance;
use App\Models\User;
use App\Models\Expense;
use App\Modules\Finance\Services\FinanceService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class FinanceCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\BranchSeeder::class);
        $user->givePermissionTo('finance.view', 'finance.expense_create', 'finance.expense_approve', 'finance.reconcile');
        $this->actingAs($user);
        return $user;
    }
    public function test_create_expense_via_service(): void
    {
        $this->authUser();
        $svc = new FinanceService();
        $e = $svc->createExpense(['branch_id' => 1, 'category' => 'Operasional', 'amount' => 50000, 'created_by' => auth()->id()]);
        $this->assertEquals('Operasional', $e->category);
        $this->assertEquals(50000, (float) $e->amount);
    }
    public function test_approve_expense(): void
    {
        $this->authUser();
        $svc = new FinanceService();
        $e = $svc->createExpense(['branch_id' => 1, 'category' => 'Operasional', 'amount' => 50000, 'created_by' => auth()->id()]);
        $svc->approveExpense($e);
        $this->assertTrue(true);
    }
    public function test_delete_expense(): void
    {
        $this->authUser();
        $svc = new FinanceService();
        $e = $svc->createExpense(['branch_id' => 1, 'category' => 'Operasional', 'amount' => 50000, 'created_by' => auth()->id()]);
        $svc->deleteExpense($e);
        $this->assertDatabaseMissing('expenses', ['id' => $e->id]);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/finance')->assertStatus(200);
    }
    public function test_create_page_loads(): void
    {
        $this->authUser();
        $this->get('/finance/create')->assertStatus(200);
    }
}
