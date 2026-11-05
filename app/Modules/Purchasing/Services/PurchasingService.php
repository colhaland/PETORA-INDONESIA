<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Services;

use App\Models\Branch;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Modules\Inventory\Services\StockService;
use App\Support\AuditService;
use App\Support\BranchAuthorization;
use App\Support\DocumentNumberService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PurchasingService
{
    public function createPo(int $branchId, int $supplierId, array $items, ?int $userId = null): PurchaseOrder
    {
        Gate::authorize('purchasing.create');
        BranchAuthorization::assertCanAccess($branchId);
        $branch = Branch::query()->findOrFail($branchId);
        Supplier::query()->findOrFail($supplierId);
        if ($items === []) {
            throw new \InvalidArgumentException('PO harus memiliki setidaknya satu item.');
        }

        return DB::transaction(function () use ($branch, $branchId, $supplierId, $items, $userId) {
            $po = PurchaseOrder::create([
                'po_number' => DocumentNumberService::next('PO', $branch->code),
                'branch_id' => $branchId,
                'supplier_id' => $supplierId,
                'status' => 'draft',
                'total' => '0.00',
                'created_by' => $userId,
            ]);

            $total = '0.00';
            $seenProducts = [];
            foreach ($items as $item) {
                if (! is_array($item) || ! isset($item['product_id'], $item['quantity'], $item['unit_cost'])) {
                    throw new \InvalidArgumentException('Setiap item PO memerlukan produk, kuantitas, dan harga modal.');
                }

                $productId = filter_var($item['product_id'], FILTER_VALIDATE_INT);
                $quantity = (float) $item['quantity'];
                if (! is_numeric($item['unit_cost']) || ! is_finite((float) $item['unit_cost'])
                    || abs((float) $item['unit_cost'] - round((float) $item['unit_cost'], 2)) > 0.0000001
                    || abs($quantity - round($quantity, 3)) > 0.0000001) {
                    throw new \InvalidArgumentException('Kuantitas atau harga modal tidak memiliki presisi yang valid.');
                }
                $unitCost = Money::of($item['unit_cost'], 2);
                if ($productId === false || $productId <= 0 || ! is_finite($quantity) || $quantity <= 0) {
                    throw new \InvalidArgumentException('Produk dan kuantitas PO tidak valid.');
                }
                if (isset($seenProducts[$productId])) {
                    throw new \InvalidArgumentException('Produk yang sama hanya boleh dicantumkan satu kali dalam PO.');
                }
                if (Money::cmp($unitCost, '0.00', 2) < 0) {
                    throw new \InvalidArgumentException('Harga modal tidak boleh negatif.');
                }

                $product = Product::query()->where('status', 'active')->lockForUpdate()->findOrFail($productId);
                $quantity = number_format($quantity, 3, '.', '');
                $lineTotal = Money::multiplyQuantity($quantity, $unitCost);
                $po->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total' => $lineTotal,
                    'inventory_managed' => $product->track_inventory,
                    'track_batch' => $product->track_batch,
                    'track_expiry' => $product->track_expiry,
                ]);
                $seenProducts[$productId] = true;
                $total = Money::add($total, $lineTotal, 2);
            }

            $po->update(['total' => $total]);
            AuditService::log('po.create', PurchaseOrder::class, $po->id, [], [
                'supplier_id' => $supplierId,
                'total' => $total,
                'item_count' => count($items),
            ]);

            return $po->load('items');
        });
    }

    public function approve(PurchaseOrder $po): void
    {
        Gate::authorize('purchasing.approve');
        $po = PurchaseOrder::query()->lockForUpdate()->findOrFail($po->id);
        BranchAuthorization::assertCanAccess((int) $po->branch_id);
        if ($po->status !== 'draft' || ! $po->items()->exists()) {
            throw new \RuntimeException('Hanya PO draft yang memiliki item dapat disetujui.');
        }

        $po->update(['status' => 'approved']);
        AuditService::log('po.approve', PurchaseOrder::class, $po->id);
    }

    public function receive(PurchaseOrder $po, array $lines, string $idempotencyKey, ?int $userId = null): GoodsReceipt
    {
        Gate::authorize('purchasing.receive');
        BranchAuthorization::assertCanAccess((int) $po->branch_id);
        if (trim($idempotencyKey) === '' || strlen($idempotencyKey) > 100) {
            throw new \InvalidArgumentException('Kunci idempotensi receipt wajib diisi.');
        }
        if ($lines === []) {
            throw new \InvalidArgumentException('Receipt harus mencatat setidaknya satu kuantitas yang diterima.');
        }

        return DB::transaction(function () use ($po, $lines, $idempotencyKey, $userId) {
            $po = PurchaseOrder::query()->lockForUpdate()->findOrFail($po->id);
            $existingReceipt = GoodsReceipt::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existingReceipt !== null) {
                if ((int) $existingReceipt->purchase_order_id !== (int) $po->id) {
                    throw new \RuntimeException('Kunci idempotensi sudah digunakan untuk PO lain.');
                }

                return $existingReceipt->load('items');
            }
            if (! in_array($po->status, ['approved', 'partially_received'], true)) {
                throw new \RuntimeException('Hanya PO yang disetujui dan belum seluruhnya diterima dapat diproses.');
            }

            $purchaseItems = $po->items()->lockForUpdate()->get()->keyBy('id');
            $receivedByItem = [];
            foreach ($lines as $line) {
                if (! is_array($line) || ! isset($line['purchase_order_item_id'], $line['quantity'], $line['warehouse_id'])) {
                    throw new \InvalidArgumentException('Setiap baris receipt memerlukan item PO, kuantitas, dan gudang.');
                }
                $itemId = filter_var($line['purchase_order_item_id'], FILTER_VALIDATE_INT);
                $warehouseId = filter_var($line['warehouse_id'], FILTER_VALIDATE_INT);
                $quantity = (float) $line['quantity'];
                if (abs($quantity - round($quantity, 3)) > 0.0000001) {
                    throw new \InvalidArgumentException('Kuantitas receipt maksimal tiga angka desimal.');
                }
                if ($itemId === false || ! $purchaseItems->has($itemId)
                    || $warehouseId === false || $warehouseId <= 0
                    || ! is_finite($quantity) || $quantity <= 0) {
                    throw new \InvalidArgumentException('Baris receipt tidak valid.');
                }

                $warehouse = Warehouse::query()
                    ->whereKey($warehouseId)
                    ->where('branch_id', $po->branch_id)
                    ->where('status', 'active')
                    ->firstOrFail();
                BranchAuthorization::assertCanAccess((int) $warehouse->branch_id);
                $receivedByItem[$itemId] = ($receivedByItem[$itemId] ?? 0) + $quantity;

                $item = $purchaseItems->get($itemId);
                $previouslyReceived = (float) $item->receiptItems()->sum('quantity');
                if ($previouslyReceived + $receivedByItem[$itemId] > (float) $item->quantity + 0.0001) {
                    throw new \InvalidArgumentException('Kuantitas receipt melebihi sisa kuantitas PO.');
                }
            }

            $receipt = GoodsReceipt::create([
                'purchase_order_id' => $po->id,
                'status' => 'received',
                'received_by' => $userId,
                'idempotency_key' => $idempotencyKey,
            ]);

            foreach ($lines as $line) {
                $item = $purchaseItems->get((int) $line['purchase_order_item_id']);
                $receiptItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $item->id,
                    'warehouse_id' => (int) $line['warehouse_id'],
                    'quantity' => number_format((float) $line['quantity'], 3, '.', ''),
                    'unit_cost' => $item->unit_cost,
                    'batch_number' => $line['batch_number'] ?? null,
                    'expiry_date' => $line['expiry_date'] ?? null,
                ]);
                app(StockService::class)->receivePurchasedItem($receiptItem, (int) $po->branch_id, $userId);
            }

            $allReceived = $purchaseItems->every(function (PurchaseOrderItem $item): bool {
                $received = (float) $item->receiptItems()->sum('quantity');

                return $received + 0.0001 >= (float) $item->quantity;
            });
            $po->update(['status' => $allReceived ? 'received' : 'partially_received']);
            AuditService::log('po.receive', PurchaseOrder::class, $po->id, [], [
                'goods_receipt_id' => $receipt->id,
                'status' => $po->status,
            ]);

            return $receipt->load('items');
        });
    }
}
