<?php
declare(strict_types=1);
namespace App\Modules\HomeGrooming\Livewire;
use Livewire\Component;
class Index extends Component
{
    public function render()
    {
        return view('modules.home-grooming.index')->layout('layouts.app');
    }
}
