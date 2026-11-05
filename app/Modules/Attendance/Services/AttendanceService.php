<?php
declare(strict_types=1);
namespace App\Modules\Attendance\Services;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class AttendanceService
{
    public function checkIn(int $userId): array
    {
        Gate::authorize('attendance.view');
        $record = ['user_id' => $userId, 'checked_in_at' => now()->toDateTimeString()];
        AuditService::log('attendance.checkin', 'User', $userId);
        return $record;
    }
    public function checkOut(int $userId): array
    {
        Gate::authorize('attendance.view');
        $record = ['user_id' => $userId, 'checked_out_at' => now()->toDateTimeString()];
        AuditService::log('attendance.checkout', 'User', $userId);
        return $record;
    }
}
