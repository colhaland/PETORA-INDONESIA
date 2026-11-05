<?php

declare(strict_types=1);

namespace App\Modules\Membership\Livewire;

use Livewire\Component;

class MembershipList extends Component
{
    public function render()
    {
        return view('modules.membership.list')->layout('layouts.app');
    }
}
