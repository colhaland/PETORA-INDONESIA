<?php
declare(strict_types=1);
namespace App\Modules\Veterinary\Livewire;
use Livewire\Component;
class VisitList extends Component
{
    public function render()
    {
        return view('modules.veterinary.list')->layout('layouts.app');
    }
}
