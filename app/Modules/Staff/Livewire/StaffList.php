<?php

declare(strict_types=1);

namespace App\Modules\Staff\Livewire;

use App\Models\User;
use Livewire\Component;

class StaffList extends Component
{
    public function render()
    {
        return view('modules.staff.list', ['staff' => User::latest()->paginate(20)])->layout('layouts.app');
    }
}
