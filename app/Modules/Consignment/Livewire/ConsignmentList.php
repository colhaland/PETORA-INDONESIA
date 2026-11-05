<?php
declare(strict_types=1);
namespace App\Modules\Consignment\Livewire;
use Livewire\Component;
class ConsignmentList extends Component
{
    public function render()
    {
        return view('modules.consignment.list')->layout('layouts.app');
    }
}
