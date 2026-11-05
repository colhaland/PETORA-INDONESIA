<?php

declare(strict_types=1);

namespace App\Modules\Pets\Livewire;

use App\Models\Pet;
use Livewire\Component;

class PetList extends Component
{
    public function render()
    {
        return view('modules.pets.list', ['pets' => Pet::latest()->paginate(20)])->layout('layouts.app');
    }
}
