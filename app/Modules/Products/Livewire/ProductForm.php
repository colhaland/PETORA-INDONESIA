<?php
declare(strict_types=1);
namespace App\Modules\Products\Livewire;
use App\Models\Product;
use App\Modules\Products\Services\ProductService;
use Livewire\Component;
class ProductForm extends Component
{
    public ?int $productId = null;
    public string $name = '';
    public string $sku = '';
    public float $sellPrice = 0;
    protected function rules(): array
    {
        return ['name' => 'required|string|max:255', 'sku' => 'required|string|max:100', 'sellPrice' => 'nullable|numeric|min:0'];
    }
    public function save(ProductService $svc): void
    {
        $this->validate();
        if ($this->productId) {
            $svc->update(Product::findOrFail($this->productId), ['name' => $this->name, 'sku' => $this->sku, 'sell_price' => $this->sellPrice]);
        } else {
            $svc->create(['name' => $this->name, 'sku' => $this->sku, 'sell_price' => $this->sellPrice]);
        }
        session()->flash('success', 'Produk disimpan.');
        $this->redirect(route('products.index'));
    }
    public function delete(ProductService $svc): void
    {
        if ($this->productId) { $svc->delete(Product::findOrFail($this->productId)); }
        session()->flash('success', 'Produk dihapus.');
        $this->redirect(route('products.index'));
    }
    public function render()
    {
        if ($this->productId && empty($this->name)) {
            $p = Product::find($this->productId);
            if ($p) { $this->name = $p->name; $this->sku = $p->sku; $this->sellPrice = (float) $p->sell_price; }
        }
        return view('modules.products.form')->layout('layouts.app');
    }
}
