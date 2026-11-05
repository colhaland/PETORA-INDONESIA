<?php
declare(strict_types=1);
namespace App\Modules\Transport\Livewire;
use Livewire\Component;
class TransportList extends Component
{
    public function render()
    {
        return view('modules.transport.list')->layout('layouts.app');
    }
}
