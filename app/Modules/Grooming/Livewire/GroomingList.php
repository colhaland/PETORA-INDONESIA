<?php

declare(strict_types=1);

namespace App\Modules\Grooming\Livewire;

use Livewire\Component;

class GroomingList extends Component
{
    public function render()
    {
        return view('modules.grooming.list')->layout('layouts.app');
    }
}
