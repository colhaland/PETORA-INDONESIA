<?php

declare(strict_types=1);

namespace App\Modules\Finance\Livewire;

use Livewire\Component;

class FinanceList extends Component
{
    public function render()
    {
        return view('modules.finance.list')->layout('layouts.app');
    }
}
