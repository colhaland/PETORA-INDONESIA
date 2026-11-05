<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Livewire;

use App\Models\Product;
use App\Models\Supplier;
use App\Modules\Purchasing\Services\PurchasingService;
use App\Support\BranchAuthorization;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PurchaseOrderForm extends Component
{
    public int $supplierId = 0;

    public array $items = [
        ['product_id' => '', 'quantity' => '1', 'unit_cost' => '0'],
    ];

    public function addItem(): void
    {
        $this->items[] = ['product_id' => '', 'quantity' => '1', 'unit_cost' => '0'];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1 || ! array_key_exists($index, $this->items)) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(PurchasingService $service): void
    {
        $this->validate([
            'supplierId' => ['required', 'integer', Rule::exists('suppliers', 'id')],
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('status', 'active')->whereNull('deleted_at'),
            ],
            'items.*.quantity' => 'required|numeric|min:0.001|max:999999999',
            'items.*.unit_cost' => 'required|numeric|min:0|max:999999999999',
        ]);

        try {
            $service->createPo(
                BranchAuthorization::currentId(),
                $this->supplierId,
                array_values($this->items),
                (int) auth()->id(),
            );
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->addError('items', $exception->getMessage());

            return;
        }

        session()->flash('success', 'PO disimpan.');
        $this->redirect(route('purchasing.index'));
    }

    public function render()
    {
        return view('modules.purchasing.form', [
            'suppliers' => Supplier::query()->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::query()->where('status', 'active')->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
