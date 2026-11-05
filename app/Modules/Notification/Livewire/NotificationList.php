<?php

declare(strict_types=1);

namespace App\Modules\Notification\Livewire;

use App\Models\Notification as NotificationModel;
use Livewire\Component;

class NotificationList extends Component
{
    public function render()
    {
        return view('modules.notification.list', ['notifications' => NotificationModel::latest()->paginate(20)])->layout('layouts.app');
    }
}
