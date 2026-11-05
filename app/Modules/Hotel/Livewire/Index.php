<?php
declare(strict_types=1);
namespace App\Modules\Hotel\Livewire;
use Livewire\Component;
class Index extends Component
{
    public function render()
    {
        return view('modules.hotel.index')->layout('layouts.app');
    }
}
