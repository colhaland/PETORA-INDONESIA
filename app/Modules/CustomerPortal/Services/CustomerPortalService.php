<?php

declare(strict_types=1);

namespace App\Modules\CustomerPortal\Services;

use App\Support\AuditService;

class CustomerPortalService
{
    public function register(array $data): array
    {
        $record = ['name' => $data['name'] ?? '', 'email' => $data['email'] ?? '', 'status' => 'pending_verification'];
        AuditService::log('portal.register', 'Customer', null);

        return $record;
    }

    public function submitComplaint(int $customerId, string $subject, string $message): array
    {
        $record = ['customer_id' => $customerId, 'subject' => $subject, 'message' => $message, 'status' => 'open'];
        AuditService::log('portal.complaint', 'Customer', $customerId);

        return $record;
    }
}
