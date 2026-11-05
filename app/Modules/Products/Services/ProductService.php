<?php
declare(strict_types=1);
namespace App\Modules\Products\Services;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
class ProductService
{
    public function create(array $data): Product
    {
        Gate::authorize('products.create');
        return Product::create($data);
    }
    public function update(Product $product, array $data): Product
    {
        Gate::authorize('products.update');
        $product->update($data);
        return $product;
    }
}
