<?php

declare(strict_types=1);

namespace App\Modules\Hotel\Services;

use App\Support\AuditService;
use Illuminate\Support\Facades\Gate;

class HotelService
{
    public function checkIn(int $petId, int $roomId): array
    {
        Gate::authorize('hotel.manage');
        $record = ['pet_id' => $petId, 'room_id' => $roomId, 'status' => 'checked_in', 'checked_in_at' => now()->toDateTimeString()];
        AuditService::log('hotel.checkin', 'Pet', $petId);

        return $record;
    }

    public function checkOut(int $petId): void
    {
        Gate::authorize('hotel.manage');
        AuditService::log('hotel.checkout', 'Pet', $petId);
    }
}
