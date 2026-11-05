<?php
declare(strict_types=1);
namespace App\Modules\CustomerPortal\Livewire;
use Livewire\Component;
class Index extends Component
{
    public function render()
    {
        return view('modules.customer-portal.index')->layout('layouts.app');
    }
}
