<?php

declare(strict_types=1);

namespace App\Modules\Staff\Livewire;

use App\Models\User;
use App\Support\RecordFinder;
use Livewire\Component;

class StaffDetail extends Component
{
    public User $staff;

    public function mount(int $id): void
    {
        $this->staff = RecordFinder::forClass(User::class, $id);
    }

    public function render()
    {
        return view('modules.staff.detail')->layout('layouts.app');
    }
}
