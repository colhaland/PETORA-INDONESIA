<?php
declare(strict_types=1);
namespace Tests\Feature\Integration;
use App\Models\User;
use App\Integrations\Adapters\MidtransAdapter;
use App\Modules\Integration\Services\IntegrationService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class IntegrationCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('integrations.view', 'integrations.manage', 'integrations.test');
        $this->actingAs($user);
        return $user;
    }
    public function test_test_adapter(): void
    {
        $this->authUser();
        $svc = new IntegrationService();
        $adapter = new MidtransAdapter();
        $result = $svc->testAdapter($adapter);
        $this->assertFalse($result);
    }
    public function test_is_configured(): void
    {
        $this->authUser();
        $svc = new IntegrationService();
        $adapter = new MidtransAdapter();
        $this->assertFalse($svc->isConfigured($adapter));
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/integration')->assertStatus(200);
    }
}
