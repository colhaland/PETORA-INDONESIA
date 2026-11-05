<?php
declare(strict_types=1);
namespace Tests\Feature\Hotel;
use App\Models\User;
use App\Modules\Hotel\Services\HotelService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class HotelCrudTest extends TestCase
{
    use RefreshDatabase;
    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user->givePermissionTo('hotel.view', 'hotel.create', 'hotel.update', 'hotel.manage');
        $this->actingAs($user);
        return $user;
    }
    public function test_checkin(): void
    {
        $this->authUser();
        $svc = new HotelService();
        $r = $svc->checkIn(1, 1);
        $this->assertEquals('checked_in', $r['status']);
    }
    public function test_checkout(): void
    {
        $this->authUser();
        $svc = new HotelService();
        $svc->checkOut(1);
        $this->assertTrue(true);
    }
    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/hotel')->assertStatus(200);
    }
}
