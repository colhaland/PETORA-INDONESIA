<?php

declare(strict_types=1);

namespace App\Modules\Booking\Livewire;

use Livewire\Component;

class BookingList extends Component
{
    public function render()
    {
        return view('modules.booking.list')->layout('layouts.app');
    }
}
