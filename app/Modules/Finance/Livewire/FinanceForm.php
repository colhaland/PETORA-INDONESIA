<?php

declare(strict_types=1);

namespace App\Modules\Finance\Livewire;

use App\Models\Expense;
use App\Modules\Finance\Services\FinanceService;
use Livewire\Component;

class FinanceForm extends Component
{
    public ?int $expenseId = null;

    public int $branchId = 1;

    public string $category = '';

    public float $amount = 0;

    public ?string $description = null;

    protected function rules(): array
    {
        return ['branchId' => 'required|integer', 'category' => 'required|string|max:100', 'amount' => 'required|numeric|min:0'];
    }

    public function save(FinanceService $svc): void
    {
        $this->validate();
        if ($this->expenseId) {
            $svc->deleteExpense(Expense::findOrFail($this->expenseId));
        }
        $svc->createExpense(['branch_id' => $this->branchId, 'category' => $this->category, 'amount' => $this->amount, 'description' => $this->description, 'created_by' => auth()->id()]);
        session()->flash('success', 'Pengeluaran disimpan.');
        $this->redirect(route('finance.index'));
    }

    public function render()
    {
        return view('modules.finance.form')->layout('layouts.app');
    }
}
