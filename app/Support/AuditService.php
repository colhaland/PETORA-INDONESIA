<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Schema;

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
        ];
        try {
            $table = (new AuditLog)->getTable();
            if (Schema::hasColumn($table, 'user_agent')) {
                $data['user_agent'] = substr((string) request()?->userAgent(), 0, 65535) ?: null;
            }
            if (Schema::hasColumn($table, 'actor_id')) {
                $data['actor_id'] = auth()->id();
            }
            if (Schema::hasColumn($table, 'auditable_type')) {
                $data['auditable_type'] = $subjectType;
            }
            if (Schema::hasColumn($table, 'auditable_id')) {
                $data['auditable_id'] = $subjectId;
            }
        } catch (\Throwable) {
            // keep base payload for minimal schemas/tests
        }
        AuditLog::create($data);
    }
}
