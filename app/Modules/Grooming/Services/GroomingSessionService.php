<?php

declare(strict_types=1);

namespace App\Modules\Grooming\Services;

use App\Models\GroomingSession;
use App\Support\AuditService;
use Illuminate\Support\Facades\Gate;

class GroomingSessionService
{
    public function create(array $data): GroomingSession
    {
        Gate::authorize('grooming.create');
        $data['status'] = $data['status'] ?? 'pending';
        $s = GroomingSession::create($data);
        AuditService::log('grooming_session.create', GroomingSession::class, $s->id);

        return $s;
    }

    public function checkIn(GroomingSession $session): void
    {
        Gate::authorize('grooming.checkin');
        $session->update(['status' => 'checked_in', 'started_at' => now()]);
        AuditService::log('grooming.checkin', GroomingSession::class, $session->id);
    }

    public function complete(GroomingSession $session): void
    {
        Gate::authorize('grooming.qc');
        $session->update(['status' => 'completed', 'completed_at' => now()]);
        AuditService::log('grooming.complete', GroomingSession::class, $session->id);
    }
}
