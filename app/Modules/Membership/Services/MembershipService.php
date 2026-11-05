<?php
declare(strict_types=1);
namespace App\Modules\Membership\Services;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class MembershipService
{
    public function subscribe(int $customerId, string $tier): array
    {
        Gate::authorize('membership.manage');
        $record = ['customer_id' => $customerId, 'tier' => $tier, 'status' => 'active'];
        AuditService::log('membership.subscribe', 'Customer', $customerId);
        return $record;
    }
    public function cancel(int $customerId): void
    {
        Gate::authorize('membership.manage');
        AuditService::log('membership.cancel', 'Customer', $customerId);
    }
}
