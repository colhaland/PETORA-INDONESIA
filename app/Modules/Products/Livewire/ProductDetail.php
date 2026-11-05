<?php

declare(strict_types=1);

namespace App\Modules\Products\Livewire;

use App\Models\Product;
use App\Support\RecordFinder;
use Livewire\Component;

class ProductDetail extends Component
{
    public Product $product;

    public function mount(int $id): void
    {
        $this->product = RecordFinder::forClass(Product::class, $id);
    }

    public function render()
    {
        return view('modules.products.detail')->layout('layouts.app');
    }
}
