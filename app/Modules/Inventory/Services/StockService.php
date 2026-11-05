<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Models\GoodsReceiptItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Support\AuditService;
use App\Support\BranchAuthorization;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StockService
{
    public function receive(
        int $warehouseId,
        int $productId,
        float $qty,
        ?string $batch = null,
        ?int $userId = null,
        ?float $unitCost = null,
        ?string $expiryDate = null,
    ): void {
        Gate::authorize('inventory.adjust');
        $this->assertPositiveQuantity($qty);
        if ($unitCost !== null && (! is_finite($unitCost) || $unitCost < 0)) {
            throw new \InvalidArgumentException('Harga modal tidak boleh negatif.');
        }

        DB::transaction(function () use ($warehouseId, $productId, $qty, $batch, $userId, $unitCost, $expiryDate) {
            $warehouse = Warehouse::query()->findOrFail($warehouseId);
            if ($warehouse->status !== 'active') {
                throw new \RuntimeException('Gudang tidak aktif.');
            }
            BranchAuthorization::assertCanAccess((int) $warehouse->branch_id);
            $product = Product::query()->where('status', 'active')->lockForUpdate()->findOrFail($productId);
            $this->assertTrackingMetadata($product->track_inventory, $product->track_batch, $product->track_expiry, $batch, $expiryDate);
            $balance = $this->increaseBalance($warehouseId, $productId, $qty, $batch, $expiryDate);

            $this->recordMovement(
                $warehouseId,
                $productId,
                'purchase_in',
                $qty,
                (string) ($unitCost ?? 0),
                'manual_receive',
                null,
                $userId,
                $batch,
                $expiryDate,
            );
            AuditService::log('stock.receive', StockBalance::class, $balance->id, [], ['qty' => $qty, 'product_id' => $productId]);
        });
    }

    public function issue(int $warehouseId, int $productId, float $qty, string $method = 'fifo', ?int $userId = null): array
    {
        Gate::authorize('inventory.adjust');
        $this->assertPositiveQuantity($qty);

        return DB::transaction(function () use ($warehouseId, $productId, $qty, $method, $userId) {
            $warehouse = Warehouse::query()->findOrFail($warehouseId);
            if ($warehouse->status !== 'active') {
                throw new \RuntimeException('Gudang tidak aktif.');
            }
            BranchAuthorization::assertCanAccess((int) $warehouse->branch_id);
            $product = Product::query()->where('status', 'active')->lockForUpdate()->findOrFail($productId);
            if (! $product->track_inventory) {
                throw new \InvalidArgumentException('Produk ini tidak dikonfigurasi untuk pelacakan stok.');
            }

            return $this->issueProduct(
                $warehouseId,
                $product,
                $qty,
                $method,
                $product->track_expiry,
                'manual_issue',
                null,
                $userId,
            );
        });
    }

    public function receivePurchasedItem(GoodsReceiptItem $receiptItem, int $branchId, ?int $userId): void
    {
        Gate::authorize('purchasing.receive');
        BranchAuthorization::assertCanAccess($branchId);
        DB::transaction(function () use ($receiptItem, $branchId, $userId) {
            $receiptItem = GoodsReceiptItem::query()->lockForUpdate()->findOrFail($receiptItem->id);
            if ($receiptItem->stock_applied_at !== null) {
                return;
            }

            $receiptItem->load(['purchaseOrderItem.product', 'goodsReceipt.purchaseOrder', 'warehouse']);
            if ($receiptItem->goodsReceipt->status !== 'received') {
                throw new \RuntimeException('Receipt belum berstatus diterima.');
            }
            if ((int) $receiptItem->goodsReceipt->purchaseOrder->branch_id !== $branchId) {
                abort(403);
            }
            $purchaseOrderItem = $receiptItem->purchaseOrderItem;
            $product = $purchaseOrderItem->product;
            if ($product === null) {
                throw new \RuntimeException('Produk receipt tidak lagi tersedia.');
            }

            if (! $purchaseOrderItem->inventory_managed) {
                $receiptItem->forceFill(['stock_applied_at' => now()])->save();

                return;
            }

            $warehouse = $receiptItem->warehouse;
            if ((int) $warehouse->branch_id !== $branchId || $warehouse->status !== 'active') {
                throw new \InvalidArgumentException('Gudang receipt harus aktif dan berada pada cabang PO.');
            }

            $this->assertPositiveQuantity((float) $receiptItem->quantity);
            $this->assertTrackingMetadata(
                $purchaseOrderItem->inventory_managed,
                $purchaseOrderItem->track_batch,
                $purchaseOrderItem->track_expiry,
                $receiptItem->batch_number,
                $receiptItem->expiry_date?->toDateString(),
            );
            $balance = $this->increaseBalance(
                (int) $warehouse->id,
                (int) $product->id,
                (float) $receiptItem->quantity,
                $receiptItem->batch_number,
                $receiptItem->expiry_date?->toDateString(),
            );

            $this->recordMovement(
                (int) $warehouse->id,
                (int) $product->id,
                'purchase_in',
                (float) $receiptItem->quantity,
                (string) $receiptItem->unit_cost,
                'goods_receipt',
                (int) $receiptItem->id,
                $userId,
                $receiptItem->batch_number,
                $receiptItem->expiry_date?->toDateString(),
            );
            $receiptItem->forceFill(['stock_applied_at' => now()])->save();
            AuditService::log('stock.purchase_receive', StockBalance::class, $balance->id, [], [
                'goods_receipt_item_id' => $receiptItem->id,
                'product_id' => $product->id,
                'quantity' => $receiptItem->quantity,
            ]);
        });
    }

    public function issueForPaidOrder(Order $order, ?int $userId): void
    {
        Gate::authorize('pos.create');
        BranchAuthorization::assertCanAccess((int) $order->branch_id);
        $items = $order->items()->with('product')->lockForUpdate()->get();
        if ($items->contains(fn ($item) => $item->inventory_managed && $item->product === null)) {
            throw new \RuntimeException('Produk order tidak lagi tersedia untuk pembayaran.');
        }
        $trackedItems = $items->filter(fn ($item) => $item->inventory_managed);

        if ($trackedItems->isEmpty()) {
            return;
        }
        if ($order->stock_issued_at !== null) {
            throw new \RuntimeException('Stok order ini sudah dikeluarkan.');
        }
        if ($order->warehouse_id === null) {
            throw new \RuntimeException('Pilih gudang cabang sebelum membayar produk yang dilacak stoknya.');
        }

        $warehouse = Warehouse::query()
            ->whereKey($order->warehouse_id)
            ->where('branch_id', $order->branch_id)
            ->where('status', 'active')
            ->firstOrFail();

        foreach ($trackedItems->groupBy('product_id') as $productItems) {
            $product = Product::query()
                ->where('status', 'active')
                ->lockForUpdate()
                ->find($productItems->first()->product_id);
            if ($product === null || $product->status !== 'active') {
                throw new \RuntimeException('Produk order tidak lagi aktif.');
            }
            $quantity = (float) $productItems->sum('quantity');
            $this->issueProduct(
                (int) $warehouse->id,
                $product,
                $quantity,
                $productItems->first()->track_expiry ? 'fefo' : 'fifo',
                $productItems->first()->track_expiry,
                'order',
                (int) $order->id,
                $userId,
            );
        }

        $order->update(['stock_issued_at' => now()]);
    }

    private function increaseBalance(
        int $warehouseId,
        int $productId,
        float $quantity,
        ?string $batchNumber,
        ?string $expiryDate,
    ): StockBalance {
        $batchNumber = $this->normalizeBatch($batchNumber);
        $now = now();

        DB::table('stock_balances')->insertOrIgnore([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'batch_number' => $batchNumber,
            'expiry_date' => $expiryDate,
            'quantity' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $balance = StockBalance::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->when(
                $expiryDate === null,
                fn ($query) => $query->whereNull('expiry_date'),
                fn ($query) => $query->whereDate('expiry_date', $expiryDate),
            )
            ->when(
                $batchNumber === null,
                fn ($query) => $query->whereNull('batch_number'),
                fn ($query) => $query->where('batch_number', $batchNumber),
            )
            ->lockForUpdate()
            ->firstOrFail();

        $balance->increment('quantity', $quantity);

        return $balance->fresh();
    }

    private function issueProduct(
        int $warehouseId,
        Product $product,
        float $quantity,
        string $method,
        bool $trackExpiry,
        string $referenceType,
        ?int $referenceId,
        ?int $userId,
    ): array {
        $this->assertPositiveQuantity($quantity);
        if (! $product->track_inventory) {
            throw new \InvalidArgumentException('Produk ini tidak dikonfigurasi untuk pelacakan stok.');
        }

        $balances = StockBalance::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->when(
                $trackExpiry,
                fn ($query) => $query->whereNotNull('expiry_date')->whereDate('expiry_date', '>=', now()->toDateString()),
            )
            ->orderBy($method === 'fefo' ? 'expiry_date' : 'created_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $available = round((float) $balances->sum('quantity'), 3);
        $allowNegative = (bool) config('petora.stock.allow_negative', false);
        if (! $allowNegative && $available + 0.0001 < $quantity) {
            throw new \RuntimeException('Stok tidak mencukupi.');
        }

        $remaining = $quantity;
        $issued = [];
        foreach ($balances as $balance) {
            if ($remaining <= 0.0001) {
                break;
            }

            $taken = min($remaining, (float) $balance->quantity);
            $balance->decrement('quantity', $taken);
            $remaining = round($remaining - $taken, 3);
            $issued[] = ['balance_id' => $balance->id, 'quantity' => $taken];
            $this->recordMovement(
                $warehouseId,
                (int) $product->id,
                'sale_out',
                $taken,
                '0',
                $referenceType,
                $referenceId,
                $userId,
                $balance->batch_number,
                $balance->expiry_date?->toDateString(),
            );
        }

        if ($remaining > 0.0001 && ! $allowNegative) {
            throw new \RuntimeException('Stok tidak mencukupi.');
        }

        if ($remaining > 0.0001) {
            $this->recordMovement(
                $warehouseId,
                (int) $product->id,
                'sale_out',
                $remaining,
                '0',
                $referenceType,
                $referenceId,
                $userId,
            );
        }

        AuditService::log('stock.issue', StockBalance::class, null, [], [
            'qty' => $quantity,
            'product_id' => $product->id,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);

        return $issued;
    }

    private function recordMovement(
        int $warehouseId,
        int $productId,
        string $type,
        float $quantity,
        string $unitCost,
        string $referenceType,
        ?int $referenceId,
        ?int $userId,
        ?string $batchNumber = null,
        ?string $expiryDate = null,
    ): StockMovement {
        return StockMovement::create([
            'movement_no' => DocumentNumberService::next('STM', 'SYS'),
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'batch_number' => $this->normalizeBatch($batchNumber),
            'expiry_date' => $expiryDate,
            'type' => $type,
            'quantity' => number_format($quantity, 3, '.', ''),
            'unit_cost' => $unitCost,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => $userId,
        ]);
    }

    private function assertTrackingMetadata(
        bool $inventoryManaged,
        bool $trackBatch,
        bool $trackExpiry,
        ?string $batchNumber,
        ?string $expiryDate,
    ): void {
        if (! $inventoryManaged) {
            throw new \InvalidArgumentException('Produk ini tidak dikonfigurasi untuk pelacakan stok.');
        }
        if ($trackBatch && $this->normalizeBatch($batchNumber) === null) {
            throw new \InvalidArgumentException('Nomor batch wajib untuk produk ini.');
        }
        if ($trackExpiry && $expiryDate === null) {
            throw new \InvalidArgumentException('Tanggal kedaluwarsa wajib untuk produk ini.');
        }
        if ($trackExpiry && $expiryDate < now()->toDateString()) {
            throw new \InvalidArgumentException('Tanggal kedaluwarsa produk tidak boleh berada di masa lalu.');
        }
        if (strlen((string) $this->normalizeBatch($batchNumber)) > 255) {
            throw new \InvalidArgumentException('Nomor batch terlalu panjang.');
        }
    }

    private function assertPositiveQuantity(float $quantity): void
    {
        if (! is_finite($quantity) || $quantity <= 0 || abs($quantity - round($quantity, 3)) > 0.0000001) {
            throw new \InvalidArgumentException('Jumlah stok harus positif dengan maksimal tiga angka desimal.');
        }
    }

    private function normalizeBatch(?string $batchNumber): ?string
    {
        $batchNumber = trim((string) $batchNumber);

        return $batchNumber === '' ? null : $batchNumber;
    }
}
