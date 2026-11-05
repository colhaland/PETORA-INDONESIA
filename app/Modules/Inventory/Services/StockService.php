<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StockService
{
    public function receive(int $warehouseId, int $productId, float $qty, ?string $batch = null, ?int $userId = null, ?float $unitCost = null): void
    {
        Gate::authorize('inventory.adjust');
        DB::transaction(function () use ($warehouseId, $productId, $qty, $batch, $userId, $unitCost) {
            $balance = StockBalance::firstOrCreate(
                ['warehouse_id' => $warehouseId, 'product_id' => $productId, 'batch_number' => $batch],
                ['quantity' => 0]
            );
            $balance->increment('quantity', $qty);
            StockMovement::create([
                'movement_no' => $this->movementNo(),
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'type' => 'purchase_in',
                'quantity' => round($qty, 3),
                'unit_cost' => round((float) ($unitCost ?? 0), 2),
                'reference_type' => 'manual_receive',
                'created_by' => $userId,
            ]);
            AuditService::log('stock.receive', StockBalance::class, $balance->id, [], ['qty' => $qty, 'product_id' => $productId]);
        });
    }

    public function issue(int $warehouseId, int $productId, float $qty, string $method = 'fifo', ?int $userId = null): array
    {
        Gate::authorize('inventory.adjust');
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $method, $userId) {
            $allowNegative = (bool) config('petora.stock.allow_negative', false);
            $balances = StockBalance::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->where('quantity', '>', 0)
                ->orderBy($method === 'fefo' ? 'expiry_date' : 'created_at')
                ->lockForUpdate()
                ->get();

            $available = round((float) $balances->sum('quantity'), 3);
            if (! $allowNegative && $available + 0.0001 < $qty) {
                throw new \RuntimeException('Stok tidak mencukupi.');
            }

            $remaining = $qty;
            $issued = [];
            foreach ($balances as $b) {
                if ($remaining <= 0) {
                    break;
                }
                $take = min($remaining, (float) $b->quantity);
                $b->decrement('quantity', $take);
                $remaining -= $take;
                $issued[] = ['balance_id' => $b->id, 'quantity' => $take];
                StockMovement::create([
                    'movement_no' => $this->movementNo(),
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'type' => 'sale_out',
                    'quantity' => round($take, 3),
                    'unit_cost' => 0,
                    'reference_type' => 'manual_issue',
                    'created_by' => $userId,
                ]);
            }
            if ($remaining > 0.0001 && ! $allowNegative) {
                throw new \RuntimeException('Stok tidak mencukupi.');
            }
            AuditService::log('stock.issue', StockBalance::class, null, [], ['qty' => $qty, 'product_id' => $productId]);
            return $issued;
        });
    }

    private function movementNo(): ?string
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('document_sequences')) {
                return null;
            }
            return DocumentNumberService::next('STM', 'SYS');
        } catch (\Throwable) {
            return null;
        }
    }
}
