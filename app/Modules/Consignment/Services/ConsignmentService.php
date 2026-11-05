<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Services;

use App\Support\AuditService;
use Illuminate\Support\Facades\Gate;

class ConsignmentService
{
    public function createContract(int $supplierId, float $commission): array
    {
        Gate::authorize('consignment.manage');
        $record = ['supplier_id' => $supplierId, 'commission' => $commission, 'status' => 'active'];
        AuditService::log('consignment.contract_create', 'Supplier', $supplierId);

        return $record;
    }

    public function settle(int $contractId): void
    {
        Gate::authorize('consignment.manage');
        AuditService::log('consignment.settle', 'Contract', $contractId);
    }
}
