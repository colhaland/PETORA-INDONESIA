<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Services;

use App\Models\Branch;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PurchasingService
{
    public function createPo(int $branchId, int $supplierId, float $total, ?int $userId = null): PurchaseOrder
    {
        Gate::authorize('purchasing.create');
        $branchCode = Branch::query()->find($branchId)?->code ?? 'PST';
        $po = PurchaseOrder::create([
            'po_number' => DocumentNumberService::next('PO', $branchCode),
            'branch_id' => $branchId,
            'supplier_id' => $supplierId,
            'status' => 'draft',
            'total' => Money::of($total, 2),
            'created_by' => $userId,
        ]);
        AuditService::log('po.create', PurchaseOrder::class, $po->id);

        return $po;
    }

    public function approve(PurchaseOrder $po): void
    {
        Gate::authorize('purchasing.approve');
        $po->update(['status' => 'approved']);
        AuditService::log('po.approve', PurchaseOrder::class, $po->id);
    }

    public function receive(PurchaseOrder $po, ?int $userId = null): GoodsReceipt
    {
        Gate::authorize('purchasing.receive');

        return DB::transaction(function () use ($po, $userId) {
            $po->update(['status' => 'received']);
            $gr = GoodsReceipt::create(['purchase_order_id' => $po->id, 'status' => 'received', 'received_by' => $userId]);
            AuditService::log('po.receive', PurchaseOrder::class, $po->id);

            return $gr;
        });
    }
}
