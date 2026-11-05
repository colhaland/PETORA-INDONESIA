<?php

declare(strict_types=1);

namespace App\Modules\Booking\Services;

use App\Models\Booking;
use App\Models\Branch;
use App\Support\AuditService;
use App\Support\BranchAuthorization;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class BookingService
{
    public function create(array $data): Booking
    {
        Gate::authorize('bookings.create');
        $branchId = (int) ($data['branch_id'] ?? 0);
        BranchAuthorization::assertCanAccess($branchId);
        $branchCode = $branchId ? (Branch::query()->find($branchId)?->code ?? 'PST') : 'PST';
        $data['booking_number'] = $data['booking_number'] ?? DocumentNumberService::next('BK', $branchCode);
        $data['status'] = $data['status'] ?? 'pending';
        $data['source'] = $data['source'] ?? 'staff';
        if (empty($data['starts_at']) && ! empty($data['scheduled_at'])) {
            $data['starts_at'] = $data['scheduled_at'];
        }
        $this->assertNoConflict($data);
        $b = Booking::create($data);
        AuditService::log('booking.create', Booking::class, $b->id);

        return $b;
    }

    public function confirm(Booking $booking): void
    {
        Gate::authorize('bookings.confirm');
        $old = $booking->status;
        $booking->update(['status' => 'confirmed']);
        AuditService::log('booking.confirm', Booking::class, $booking->id, ['status' => $old], ['status' => 'confirmed']);
    }

    public function cancel(Booking $booking, ?string $reason = null): void
    {
        Gate::authorize('bookings.cancel');
        $old = $booking->status;
        $updates = ['status' => 'cancelled'];
        if (Schema::hasColumn('bookings', 'cancelled_reason')) {
            $updates['cancelled_reason'] = $reason;
        }
        $updates['notes'] = $reason;
        $booking->update($updates);
        AuditService::log('booking.cancel', Booking::class, $booking->id, ['status' => $old], ['status' => 'cancelled', 'reason' => $reason]);
    }

    protected function assertNoConflict(array $data): void
    {
        $resourceId = $data['resource_id'] ?? null;
        $starts = $data['starts_at'] ?? $data['scheduled_at'] ?? null;
        $ends = $data['ends_at'] ?? null;
        $branchId = $data['branch_id'] ?? null;
        if (! $resourceId || ! $starts || ! $ends || ! $branchId) {
            return;
        }
        $conflict = Booking::query()
            ->where('branch_id', $branchId)
            ->where('resource_id', $resourceId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where('starts_at', '<', $ends)
            ->where('ends_at', '>', $starts)
            ->exists();
        if ($conflict) {
            throw new \RuntimeException('Jadwal bentrok dengan booking lain.');
        }
    }
}
