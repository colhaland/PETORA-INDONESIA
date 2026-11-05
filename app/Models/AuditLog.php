<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'actor_id', 'action', 'subject_type', 'subject_id', 'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit log records are immutable.'));
        static::deleting(fn () => throw new LogicException('Audit log records are immutable.'));
    }
}
