<?php
declare(strict_types=1);
namespace Tests\Feature\Loyalty;
use App\Models\User;
use App\Modules\Loyalty\Services\LoyaltyService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class LoyaltyCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('loyalty.view', 'loyalty.adjust', 'loyalty.manage');
        $this->actingAs($user);
        return $user;
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/loyalty')->assertStatus(200);
    }
}
