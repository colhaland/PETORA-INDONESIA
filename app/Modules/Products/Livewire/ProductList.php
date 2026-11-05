<?php
declare(strict_types=1);
namespace App\Modules\Products\Livewire;
use App\Models\Product;
use Livewire\Component;
class ProductList extends Component
{
    public function render()
    {
        return view('modules.products.list', ['products' => Product::latest()->paginate(20)])->layout('layouts.app');
    }
}
