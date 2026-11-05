<?php
declare(strict_types=1);
namespace App\Modules\Loyalty\Livewire;
use Livewire\Component;
class LoyaltyList extends Component
{
    public function render()
    {
        return view('modules.loyalty.list')->layout('layouts.app');
    }
}
