<?php
declare(strict_types=1);
namespace App\Modules\Accounting\Livewire;
use Livewire\Component;
class Index extends Component
{
    public function render()
    {
        return view('modules.accounting.index')->layout('layouts.app');
    }
}
