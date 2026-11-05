<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AuditLog;

class AuditService
{
    public static function log(string $action, ?string $subjectType = null, ?int $subjectId = null, array $old = [], array $new = []): void
    {
        $data = [
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 65535) ?: null,
            'actor_id' => auth()->id(),
            'auditable_type' => $subjectType,
            'auditable_id' => $subjectId,
        ];
        AuditLog::create($data);
    }
}
