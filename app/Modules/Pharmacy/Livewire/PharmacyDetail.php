<?php

declare(strict_types=1);

namespace App\Modules\Pharmacy\Livewire;

use App\Models\Medicine;
use App\Support\RecordFinder;
use Livewire\Component;

class PharmacyDetail extends Component
{
    public Medicine $medicine;

    public function mount(int $id): void
    {
        $this->medicine = RecordFinder::forClass(Medicine::class, $id);
    }

    public function render()
    {
        return view('modules.pharmacy.detail')->layout('layouts.app');
    }
}
