<?php
declare(strict_types=1);
namespace App\Support;
use App\Models\AuditLog;
class AuditService
{
    public static function log(string $action, ?string $subjectType = null, ?int $subjectId = null, array $old = [], array $new = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
        ]);
    }
}
