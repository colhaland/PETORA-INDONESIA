<?php

declare(strict_types=1);

namespace App\Modules\Booking\Livewire;

use App\Models\Booking;
use App\Support\RecordFinder;
use Livewire\Component;

class BookingDetail extends Component
{
    public Booking $booking;

    public function mount(int $id): void
    {
        $this->booking = RecordFinder::forClass(Booking::class, $id);
    }

    public function render()
    {
        return view('modules.booking.detail')->layout('layouts.app');
    }
}
