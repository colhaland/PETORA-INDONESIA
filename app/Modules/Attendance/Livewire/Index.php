<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Livewire;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('modules.attendance.index')->layout('layouts.app');
    }
}
