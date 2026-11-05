<?php

declare(strict_types=1);

namespace App\Modules\Staff\Livewire;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('modules.staff.index')->layout('layouts.app');
    }
}
