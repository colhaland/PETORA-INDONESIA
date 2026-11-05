<?php
declare(strict_types=1);
namespace App\Modules\HomeGrooming\Livewire;
use Livewire\Component;
class HomeGroomingList extends Component
{
    public function render()
    {
        return view('modules.home-grooming.list')->layout('layouts.app');
    }
}
