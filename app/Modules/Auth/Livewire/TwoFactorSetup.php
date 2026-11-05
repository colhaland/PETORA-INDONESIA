<?php
declare(strict_types=1);
namespace App\Modules\Auth\Livewire;
use Livewire\Component;
class TwoFactorSetup extends Component
{
    public function render()
    {
        return view('modules.auth.two-factor')->layout('layouts.app');
    }
}
