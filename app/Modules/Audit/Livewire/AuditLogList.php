<?php

declare(strict_types=1);

namespace App\Modules\Audit\Livewire;

use App\Models\AuditLog;
use Livewire\Component;

class AuditLogList extends Component
{
    public function render()
    {
        return view('modules.audit.list', ['logs' => AuditLog::latest()->paginate(20)])->layout('layouts.app');
    }
}
