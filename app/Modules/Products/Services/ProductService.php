<?php

declare(strict_types=1);

namespace App\Modules\Products\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\StockBalance;
use App\Support\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProductService
{
    public function create(array $data): Product
    {
        Gate::authorize('products.create');
        $p = Product::create($data);
        AuditService::log('product.create', Product::class, $p->id, [], $p->toArray());

        return $p;
    }

    public function update(Product $product, array $data): Product
    {
        Gate::authorize('products.update');

        return DB::transaction(function () use ($product, $data) {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $trackingValue = array_key_exists('track_inventory', $data)
                ? filter_var($data['track_inventory'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : $product->track_inventory;
            if ($trackingValue === null) {
                throw new \InvalidArgumentException('Pengaturan pelacakan stok tidak valid.');
            }
            $data['track_inventory'] = $trackingValue;

            if ($product->track_inventory !== $trackingValue) {
                $hasOpenOrder = OrderItem::query()
                    ->where('product_id', $product->id)
                    ->whereHas('order', fn ($query) => $query->whereIn('status', [
                        Order::STATUS_DRAFT,
                        Order::STATUS_OPEN,
                        Order::STATUS_PARTIALLY_PAID,
                    ]))
                    ->exists();
                $hasOpenPurchaseOrder = PurchaseOrderItem::query()
                    ->where('product_id', $product->id)
                    ->whereHas('purchaseOrder', fn ($query) => $query->whereIn('status', ['draft', 'approved', 'partially_received']))
                    ->exists();
                $hasStock = ! $trackingValue && StockBalance::query()
                    ->where('product_id', $product->id)
                    ->where('quantity', '<>', 0)
                    ->exists();

                if ($hasStock || $hasOpenOrder || $hasOpenPurchaseOrder) {
                    throw new \RuntimeException('Pelacakan stok tidak dapat diubah saat stok atau transaksi terkait masih terbuka.');
                }
            }

            $product->update($data);

            return $product;
        });
    }

    public function delete(Product $product): void
    {
        Gate::authorize('products.delete');
        $product->delete();
        AuditService::log('product.delete', Product::class, $product->id);
    }
}
