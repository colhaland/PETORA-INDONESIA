<?php
declare(strict_types=1);
namespace Tests\Feature\Accounting;
use App\Models\User;
use App\Modules\Accounting\Services\AccountingService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class AccountingCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('accounting.view', 'accounting.create', 'accounting.update', 'accounting.manage');
        $this->actingAs($user);
        return $user;
    }
    public function test_balanced_journal(): void
    {
        $this->authUser();
        $svc = new AccountingService();
        $entries = [['debit' => 100, 'credit' => 0], ['debit' => 0, 'credit' => 100]];
        $this->assertTrue($svc->createJournal($entries));
    }
    public function test_unbalanced_journal_throws(): void
    {
        $this->authUser();
        $svc = new AccountingService();
        $entries = [['debit' => 100, 'credit' => 0], ['debit' => 0, 'credit' => 50]];
        $this->expectException(\RuntimeException::class);
        $svc->createJournal($entries);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/accounting')->assertStatus(200);
    }
}
