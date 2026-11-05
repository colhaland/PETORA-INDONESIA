<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Services;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class StockService
{
    public function receive(int $warehouseId, int $productId, float $qty, ?string $batch = null, ?int $userId = null): void
    {
        Gate::authorize('inventory.adjust');
        DB::transaction(function () use ($warehouseId, $productId, $qty, $batch, $userId) {
            $balance = StockBalance::firstOrCreate(
                ['warehouse_id' => $warehouseId, 'product_id' => $productId, 'batch_number' => $batch],
                ['quantity' => 0]
            );
            $balance->increment('quantity', $qty);
            StockMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'type' => 'purchase_in',
                'quantity' => $qty,
                'created_by' => $userId,
            ]);
            AuditService::log('stock.receive', StockBalance::class, $balance->id);
        });
    }
    public function issue(int $warehouseId, int $productId, float $qty, string $method = 'fifo', ?int $userId = null): array
    {
        Gate::authorize('inventory.adjust');
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $method, $userId) {
            $balances = StockBalance::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->where('quantity', '>', 0)
                ->orderBy($method === 'fefo' ? 'expiry_date' : 'created_at')
                ->lockForUpdate()
                ->get();
            $remaining = $qty;
            $issued = [];
            foreach ($balances as $b) {
                if ($remaining <= 0) break;
                $take = min($remaining, (float) $b->quantity);
                $b->decrement('quantity', $take);
                $remaining -= $take;
                $issued[] = ['balance_id' => $b->id, 'quantity' => $take];
                StockMovement::create([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'type' => 'sale_out',
                    'quantity' => $take,
                    'created_by' => $userId,
                ]);
            }
            if ($remaining > 0) {
                throw new \RuntimeException('Stok tidak mencukupi.');
            }
            return $issued;
        });
    }
}
