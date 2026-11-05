<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Models\Expense;
use App\Support\AuditService;
use App\Support\Money;
use Illuminate\Support\Facades\Gate;

class FinanceService
{
    public function createExpense(array $data): Expense
    {
        Gate::authorize('finance.expense_create');
        if (array_key_exists('amount', $data)) {
            $data['amount'] = Money::of($data['amount'], 2);
        }
        $e = Expense::create($data);
        AuditService::log('expense.create', Expense::class, $e->id, [], ['amount' => $e->amount]);
        return $e;
    }

    public function approveExpense(Expense $expense): void
    {
        Gate::authorize('finance.expense_approve');
        AuditService::log('expense.approve', Expense::class, $expense->id);
    }

    public function deleteExpense(Expense $expense): void
    {
        Gate::authorize('finance.expense_create');
        $expense->delete();
        AuditService::log('expense.delete', Expense::class, $expense->id);
    }
}
