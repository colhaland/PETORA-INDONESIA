<?php

declare(strict_types=1);

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Modules\Attendance\Services\AttendanceService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('attendance.view', 'attendance.create', 'attendance.update', 'attendance.manage');
        $this->actingAs($user);

        return $user;
    }

    public function test_checkin(): void
    {
        $this->authUser();
        $svc = new AttendanceService;
        $r = $svc->checkIn(auth()->id());
        $this->assertArrayHasKey('checked_in_at', $r);
    }

    public function test_checkout(): void
    {
        $this->authUser();
        $svc = new AttendanceService;
        $r = $svc->checkOut(auth()->id());
        $this->assertArrayHasKey('checked_out_at', $r);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/attendance')->assertStatus(200);
    }
}
