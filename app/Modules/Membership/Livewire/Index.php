<?php
declare(strict_types=1);
namespace App\Modules\Membership\Livewire;
use Livewire\Component;
class Index extends Component
{
    public function render()
    {
        return view('modules.membership.index')->layout('layouts.app');
    }
}
