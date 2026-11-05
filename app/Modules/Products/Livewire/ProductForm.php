<?php
declare(strict_types=1);
namespace App\Modules\Products\Livewire;
use App\Models\Product;
use Livewire\Component;
class ProductForm extends Component
{
    public string $name = '';
    public string $sku = '';
    public float $sellPrice = 0;
    public function save(): void
    {
        $this->validate(['name' => 'required', 'sku' => 'required']);
        Product::create(['name' => $this->name, 'sku' => $this->sku, 'sell_price' => $this->sellPrice]);
        session()->flash('success', 'Produk disimpan.');
        $this->redirect(route('products.index'));
    }
    public function render()
    {
        return view('modules.products.form')->layout('layouts.app');
    }
}
