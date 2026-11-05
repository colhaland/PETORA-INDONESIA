<?php

declare(strict_types=1);

namespace App\Modules\Users\Livewire;

use App\Models\User;
use Livewire\Component;

class UserList extends Component
{
    public function render()
    {
        return view('modules.users.list', ['users' => User::latest()->paginate(20)])->layout('layouts.app');
    }
}
