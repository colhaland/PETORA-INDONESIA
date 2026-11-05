<?php
declare(strict_types=1);
namespace App\Modules\Notification\Livewire;
use Livewire\Component;
class Index extends Component
{
    public function render()
    {
        return view('modules.notification.index')->layout('layouts.app');
    }
}
