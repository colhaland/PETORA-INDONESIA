<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Services;
use App\Models\StockBalance;
use Illuminate\Support\Facades\DB;
class StockService
{
    public function issue(int $warehouseId, int $productId, float $qty, string $method = 'fifo'): array
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $method) {
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
            }
            if ($remaining > 0) {
                throw new \RuntimeException('Stok tidak mencukupi.');
            }
            return $issued;
        });
    }
}
