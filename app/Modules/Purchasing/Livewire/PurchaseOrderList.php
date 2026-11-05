<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Livewire;

use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Modules\Purchasing\Services\PurchasingService;
use App\Support\BranchAuthorization;
use App\Support\RecordFinder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PurchaseOrderList extends Component
{
    public ?int $receivingPoId = null;

    public array $receiptLines = [];

    #[Locked]
    public ?string $receiptKey = null;

    public function approve(int $poId, PurchasingService $service): void
    {
        try {
            $service->approve(RecordFinder::forClass(PurchaseOrder::class, $poId));
        } catch (\RuntimeException $exception) {
            $this->addError('po', $exception->getMessage());
        }
    }

    public function beginReceiving(int $poId): void
    {
        $po = RecordFinder::forClass(PurchaseOrder::class, $poId);
        $po->load(['items.product', 'items.receiptItems']);
        $warehouses = Warehouse::query()
            ->where('branch_id', $po->branch_id)
            ->where('status', 'active')
            ->get();
        $mainWarehouses = $warehouses->where('is_main', true);
        $defaultWarehouseId = $mainWarehouses->count() === 1
            ? $mainWarehouses->first()->id
            : ($warehouses->count() === 1 ? $warehouses->first()->id : null);

        $this->receiptLines = [];
        foreach ($po->items as $item) {
            $remaining = round((float) $item->quantity - (float) $item->receiptItems->sum('quantity'), 3);
            if ($remaining <= 0) {
                continue;
            }

            $this->receiptLines[] = [
                'purchase_order_item_id' => $item->id,
                'quantity' => number_format($remaining, 3, '.', ''),
                'warehouse_id' => $defaultWarehouseId,
                'batch_number' => '',
                'expiry_date' => '',
            ];
        }

        $this->receivingPoId = $po->id;
        $this->receiptKey = (string) Str::uuid();
        $this->resetValidation();
    }

    public function removeReceiptLine(int $index): void
    {
        if (array_key_exists($index, $this->receiptLines)) {
            unset($this->receiptLines[$index]);
            $this->receiptLines = array_values($this->receiptLines);
        }
    }

    public function receive(PurchasingService $service): void
    {
        $this->validate([
            'receivingPoId' => ['required', 'integer', Rule::exists('purchase_orders', 'id')],
            'receiptKey' => 'required|string|max:100',
            'receiptLines' => 'required|array|min:1',
            'receiptLines.*.purchase_order_item_id' => 'required|integer',
            'receiptLines.*.quantity' => 'required|numeric|min:0.001|max:999999999',
            'receiptLines.*.warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'receiptLines.*.batch_number' => 'nullable|string|max:255',
            'receiptLines.*.expiry_date' => 'nullable|date',
        ]);

        try {
            $service->receive(
                RecordFinder::forClass(PurchaseOrder::class, $this->receivingPoId),
                array_values($this->receiptLines),
                $this->receiptKey,
                (int) auth()->id(),
            );
            $this->receivingPoId = null;
            $this->receiptLines = [];
            $this->receiptKey = null;
            $this->resetValidation();
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->addError('receiptLines', $exception->getMessage());
        }
    }

    public function cancelReceiving(): void
    {
        $this->receivingPoId = null;
        $this->receiptLines = [];
        $this->receiptKey = null;
        $this->resetValidation();
    }

    public function render()
    {
        $branchId = BranchAuthorization::currentId();
        $receivingPo = $this->receivingPoId === null
            ? null
            : RecordFinder::forClass(PurchaseOrder::class, $this->receivingPoId)->load(['items.product']);

        return view('modules.purchasing.list', [
            'purchaseOrders' => PurchaseOrder::query()
                ->with(['supplier', 'items.product', 'receipts'])
                ->latest()
                ->get(),
            'receivingPo' => $receivingPo,
            'warehouses' => Warehouse::query()
                ->where('branch_id', $branchId)
                ->where('status', 'active')
                ->orderByDesc('is_main')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}
