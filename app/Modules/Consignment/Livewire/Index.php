<?php
declare(strict_types=1);
namespace App\Modules\Consignment\Livewire;
use Livewire\Component;
class Index extends Component
{
    public function render()
    {
        return view('modules.consignment.index')->layout('layouts.app');
    }
}
