<?php
declare(strict_types=1);
namespace App\Modules\Notification\Services;
use App\Models\Notification as NotificationModel;
use Illuminate\Support\Facades\Gate;
class NotificationService
{
    public function send(int $userId, string $type, string $data): NotificationModel
    {
        Gate::authorize('notifications.manage_templates');
        return NotificationModel::create(['user_id' => $userId, 'type' => $type, 'data' => $data]);
    }
    public function markRead(NotificationModel $n): void
    {
        $n->update(['read_at' => now()]);
    }
    public function unreadCount(int $userId): int
    {
        return NotificationModel::where('user_id', $userId)->whereNull('read_at')->count();
    }
}
