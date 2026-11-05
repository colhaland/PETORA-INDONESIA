<?php
declare(strict_types=1);
namespace App\Modules\Accounting\Livewire;
use Livewire\Component;
class AccountingList extends Component
{
    public function render()
    {
        return view('modules.accounting.list')->layout('layouts.app');
    }
}
