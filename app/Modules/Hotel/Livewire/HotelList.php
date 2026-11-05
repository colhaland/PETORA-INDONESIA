<?php

declare(strict_types=1);

namespace App\Modules\Hotel\Livewire;

use Livewire\Component;

class HotelList extends Component
{
    public function render()
    {
        return view('modules.hotel.list')->layout('layouts.app');
    }
}
