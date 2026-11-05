<?php
declare(strict_types=1);
namespace App\Modules\Products\Services;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
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
        $product->update($data);
        return $product;
    }
    public function delete(Product $product): void
    {
        Gate::authorize('products.delete');
        $product->delete();
        AuditService::log('product.delete', Product::class, $product->id);
    }
}
