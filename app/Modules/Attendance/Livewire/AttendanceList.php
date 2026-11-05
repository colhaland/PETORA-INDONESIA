<?php
declare(strict_types=1);
namespace App\Modules\Attendance\Livewire;
use Livewire\Component;
class AttendanceList extends Component
{
    public function render()
    {
        return view('modules.attendance.list')->layout('layouts.app');
    }
}
