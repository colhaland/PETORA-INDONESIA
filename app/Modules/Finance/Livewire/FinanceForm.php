<?php

declare(strict_types=1);

namespace App\Modules\Finance\Livewire;

use App\Models\Expense;
use App\Modules\Finance\Services\FinanceService;
use App\Support\BranchAuthorization;
use App\Support\RecordFinder;
use Livewire\Component;

class FinanceForm extends Component
{
    public ?int $expenseId = null;

    public string $category = '';

    public float $amount = 0;

    public ?string $description = null;

    protected function rules(): array
    {
        return ['category' => 'required|string|max:100', 'amount' => 'required|numeric|min:0'];
    }

    public function save(FinanceService $svc): void
    {
        $this->validate();
        if ($this->expenseId) {
            $svc->deleteExpense(RecordFinder::forClass(Expense::class, $this->expenseId));
        }
        $svc->createExpense(['branch_id' => BranchAuthorization::currentId(), 'category' => $this->category, 'amount' => $this->amount, 'description' => $this->description, 'created_by' => auth()->id()]);
        session()->flash('success', 'Pengeluaran disimpan.');
        $this->redirect(route('finance.index'));
    }

    public function render()
    {
        return view('modules.finance.form')->layout('layouts.app');
    }
}
