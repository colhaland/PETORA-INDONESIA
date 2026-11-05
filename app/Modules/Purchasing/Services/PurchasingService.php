<?php
declare(strict_types=1);
namespace App\Modules\Purchasing\Services;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class PurchasingService
{
    public function createPo(int $branchId, int $supplierId, float $total, ?int $userId = null): PurchaseOrder
    {
        Gate::authorize('purchasing.create');
        $po = PurchaseOrder::create([
            'po_number' => 'PO/PST/' . date('Ym') . '/' . str_pad((string) (PurchaseOrder::count() + 1), 6, '0', STR_PAD_LEFT),
            'branch_id' => $branchId,
            'supplier_id' => $supplierId,
            'status' => 'draft',
            'total' => $total,
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
