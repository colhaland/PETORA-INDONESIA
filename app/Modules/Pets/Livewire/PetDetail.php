<?php

declare(strict_types=1);

namespace App\Modules\Pets\Livewire;

use App\Models\Pet;
use App\Support\RecordFinder;
use Livewire\Component;

class PetDetail extends Component
{
    public Pet $pet;

    public function mount(int $id): void
    {
        $this->pet = RecordFinder::forClass(Pet::class, $id);
    }

    public function render()
    {
        return view('modules.pets.detail')->layout('layouts.app');
    }
}
