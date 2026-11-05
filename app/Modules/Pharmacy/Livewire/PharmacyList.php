<?php

declare(strict_types=1);

namespace App\Modules\Pharmacy\Livewire;

use Livewire\Component;

class PharmacyList extends Component
{
    public function render()
    {
        return view('modules.pharmacy.list')->layout('layouts.app');
    }
}
