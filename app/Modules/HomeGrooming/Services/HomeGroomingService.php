<?php

declare(strict_types=1);

namespace App\Modules\HomeGrooming\Services;

use App\Support\AuditService;
use Illuminate\Support\Facades\Gate;

class HomeGroomingService
{
    public function book(int $customerId, string $address): array
    {
        Gate::authorize('home_grooming.create');
        $record = ['customer_id' => $customerId, 'address' => $address, 'status' => 'requested'];
        AuditService::log('home_grooming.book', 'Customer', $customerId);

        return $record;
    }

    public function assignGroomer(int $bookingId, int $groomerId): void
    {
        Gate::authorize('home_grooming.manage');
        AuditService::log('home_grooming.assign', 'Booking', $bookingId);
    }
}
