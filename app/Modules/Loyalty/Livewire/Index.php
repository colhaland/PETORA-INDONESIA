<?php

declare(strict_types=1);

namespace App\Modules\Loyalty\Livewire;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('modules.loyalty.index')->layout('layouts.app');
    }
}
