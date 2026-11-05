<?php
declare(strict_types=1);
namespace App\Modules\Purchasing\Livewire;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Modules\Purchasing\Services\PurchasingService;
use Livewire\Component;
class PurchaseOrderForm extends Component
{
    public ?int $poId = null;
    public int $supplierId = 0;
    public float $total = 0;
    public function save(PurchasingService $svc): void
    {
        $this->validate(['supplierId' => 'required|integer', 'total' => 'required|numeric|min:0']);
        $branchId = 1;
        if ($this->poId) {
            PurchaseOrder::findOrFail($this->poId)->update(['supplier_id' => $this->supplierId, 'total' => $this->total]);
        } else {
            $svc->createPo($branchId, $this->supplierId, $this->total, auth()->id());
        }
        session()->flash('success', 'PO disimpan.');
        $this->redirect(route('purchasing.index'));
    }
    public function render()
    {
        return view('modules.purchasing.form', ['suppliers' => Supplier::all()])->layout('layouts.app');
    }
}
