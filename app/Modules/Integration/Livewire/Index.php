<?php

declare(strict_types=1);

namespace App\Modules\Integration\Livewire;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('modules.integration.index')->layout('layouts.app');
    }
}
