<?php

declare(strict_types=1);

namespace App\Modules\Integration\Livewire;

use Livewire\Component;

class IntegrationList extends Component
{
    public function render()
    {
        return view('modules.integration.list')->layout('layouts.app');
    }
}
