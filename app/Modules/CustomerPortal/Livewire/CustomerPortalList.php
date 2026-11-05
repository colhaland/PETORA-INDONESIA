<?php
declare(strict_types=1);
namespace App\Modules\CustomerPortal\Livewire;
use Livewire\Component;
class CustomerPortalList extends Component
{
    public function render()
    {
        return view('modules.customer-portal.list')->layout('layouts.app');
    }
}
