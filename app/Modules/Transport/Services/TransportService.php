<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Support\AuditService;
use Illuminate\Support\Facades\Gate;

class TransportService
{
    public function book(int $customerId, string $pickup, string $destination): array
    {
        Gate::authorize('transport.manage');
        $record = ['customer_id' => $customerId, 'pickup' => $pickup, 'destination' => $destination, 'status' => 'requested'];
        AuditService::log('transport.book', 'Customer', $customerId);

        return $record;
    }

    public function assignDriver(int $bookingId, int $driverId): void
    {
        Gate::authorize('transport.manage');
        AuditService::log('transport.assign', 'Booking', $bookingId);
    }
}
