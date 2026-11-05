<?php

declare(strict_types=1);

namespace App\Modules\Branches\Livewire;

use App\Models\Branch;
use Livewire\Component;

class BranchList extends Component
{
    public function render()
    {
        return view('modules.branches.list', ['branches' => Branch::latest()->paginate(20)])->layout('layouts.app');
    }
}
