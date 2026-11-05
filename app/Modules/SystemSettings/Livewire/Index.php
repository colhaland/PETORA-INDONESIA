<?php
declare(strict_types=1);
namespace App\Modules\SystemSettings\Livewire;
use Livewire\Component;
class Index extends Component
{
    public function render()
    {
        return view('modules.system-settings.index')->layout('layouts.app');
    }
}
