<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Livewire;

use Livewire\Component;

class PurchaseOrderList extends Component
{
    public function render()
    {
        return view('modules.purchasing.list')->layout('layouts.app');
    }
}
