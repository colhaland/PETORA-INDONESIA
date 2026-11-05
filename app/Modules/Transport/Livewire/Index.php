<?php
declare(strict_types=1);
namespace App\Modules\Transport\Livewire;
use Livewire\Component;
class Index extends Component
{
    public function render()
    {
        return view('modules.transport.index')->layout('layouts.app');
    }
}
