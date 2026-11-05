<?php
declare(strict_types=1);
namespace App\Modules\Booking\Services;
use App\Models\Booking;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class BookingService
{
    public function create(array $data): Booking
    {
        Gate::authorize('bookings.create');
        $data['booking_number'] = 'BK/PST/' . date('Ym') . '/' . str_pad((string) (Booking::count() + 1), 6, '0', STR_PAD_LEFT);
        $data['status'] = $data['status'] ?? 'pending';
        $b = Booking::create($data);
        AuditService::log('booking.create', Booking::class, $b->id);
        return $b;
    }
    public function confirm(Booking $booking): void
    {
        Gate::authorize('bookings.confirm');
        $booking->update(['status' => 'confirmed']);
        AuditService::log('booking.confirm', Booking::class, $booking->id);
    }
    public function cancel(Booking $booking, ?string $reason = null): void
    {
        Gate::authorize('bookings.cancel');
        $booking->update(['status' => 'cancelled', 'notes' => $reason]);
        AuditService::log('booking.cancel', Booking::class, $booking->id);
    }
}
