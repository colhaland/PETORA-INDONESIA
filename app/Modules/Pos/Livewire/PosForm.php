<?php

declare(strict_types=1);

namespace App\Modules\Pos\Livewire;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Pos\Services\PosService;
use App\Support\BranchAuthorization;
use App\Support\RecordFinder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PosForm extends Component
{
    public ?int $orderId = null;

    public ?int $warehouseId = null;

    public int $productId = 0;

    public float $quantity = 1;

    public string $paymentMethod = 'cash';

    public ?float $paymentAmount = null;

    #[Locked]
    public string $idempotencyKey;

    public function mount(PosService $service): void
    {
        $branchId = BranchAuthorization::currentId();
        $warehouses = Warehouse::query()
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->get();
        $mainWarehouses = $warehouses->where('is_main', true);
        $configuredMainId = Branch::query()->findOrFail($branchId)->main_warehouse_id;
        $defaultWarehouse = $warehouses->firstWhere('id', $configuredMainId)
            ?? ($mainWarehouses->count() === 1
            ? $mainWarehouses->first()
            : ($warehouses->count() === 1 ? $warehouses->first() : null));
        $this->warehouseId = $defaultWarehouse?->id;

        $order = $service->createOrder($branchId, null, (int) auth()->id(), $this->warehouseId);
        $this->orderId = $order->id;
        $this->idempotencyKey = (string) Str::uuid();
    }

    public function setWarehouse(PosService $service): void
    {
        $this->validate(['warehouseId' => ['required', 'integer']]);
        $service->setWarehouse($this->order(), $this->warehouseId);
    }

    public function addItem(PosService $service): void
    {
        $this->validate([
            'productId' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('status', 'active')->whereNull('deleted_at'),
            ],
            'quantity' => 'required|numeric|min:0.001|max:999999999',
        ]);

        try {
            $service->addItem($this->order(), $this->productId, $this->quantity);
            $this->reset('productId');
            $this->quantity = 1;
            $this->resetValidation();
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->addError('productId', $exception->getMessage());
        }
    }

    public function pay(PosService $service): void
    {
        $this->paymentAmount ??= (float) $this->order()->total;
        $this->validate([
            'paymentMethod' => 'required|in:cash,card,bank_transfer,e_wallet',
            'paymentAmount' => 'required|numeric|min:0.01',
        ]);

        try {
            $service->pay($this->order(), $this->paymentMethod, $this->paymentAmount, $this->idempotencyKey);
            $this->idempotencyKey = (string) Str::uuid();
            $this->paymentAmount = null;
            $this->resetValidation();
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->addError('paymentAmount', $exception->getMessage());
        }
    }

    public function render()
    {
        $branchId = BranchAuthorization::currentId();
        $order = $this->order()->load(['items.product', 'payments']);

        return view('modules.pos.form', [
            'order' => $order,
            'products' => Product::query()->where('status', 'active')->orderBy('name')->get(),
            'warehouses' => Warehouse::query()
                ->where('branch_id', $branchId)
                ->where('status', 'active')
                ->orderByDesc('is_main')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }

    private function order(): Order
    {
        if ($this->orderId === null) {
            abort(404);
        }

        return RecordFinder::forClass(Order::class, $this->orderId);
    }
}
