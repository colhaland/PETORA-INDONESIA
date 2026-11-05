<?php

declare(strict_types=1);

namespace App\Modules\Veterinary\Livewire;

use App\Models\MedicalVisit;
use App\Support\RecordFinder;
use Livewire\Component;

class VisitDetail extends Component
{
    public MedicalVisit $visit;

    public function mount(int $id): void
    {
        $this->visit = RecordFinder::forClass(MedicalVisit::class, $id);
    }

    public function render()
    {
        return view('modules.veterinary.detail')->layout('layouts.app');
    }
}
