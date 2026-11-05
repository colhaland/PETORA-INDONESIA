<?php
declare(strict_types=1);
namespace App\Modules\Reports\Livewire;
use Livewire\Component;
class ReportsIndex extends Component
{
    public function render()
    {
        return view('modules.reports.index')->layout('layouts.app');
    }
}
