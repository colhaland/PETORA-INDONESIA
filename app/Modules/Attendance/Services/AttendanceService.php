<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Support\AuditService;
use Illuminate\Support\Facades\Gate;

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
