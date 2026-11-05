<?php
declare(strict_types=1);
namespace App\Modules\Commission\Livewire;
use Livewire\Component;
class CommissionList extends Component
{
    public function render()
    {
        return view('modules.commission.list')->layout('layouts.app');
    }
}
