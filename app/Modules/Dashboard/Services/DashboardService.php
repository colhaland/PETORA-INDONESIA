<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Services;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Support\BranchAuthorization;
use Illuminate\Support\Facades\Gate;

class DashboardService
{
    public function summary(): array
    {
        Gate::authorize('dashboard.view');
        $branchId = BranchAuthorization::currentId();
        $paidOrdersToday = Order::query()
            ->where('branch_id', $branchId)
            ->whereIn('status', [Order::STATUS_PAID, Order::STATUS_COMPLETED])
            ->whereDate('paid_at', today());

        return [
            'branch_name' => Branch::query()->findOrFail($branchId)->name,
            'orders_today' => (clone $paidOrdersToday)->count(),
            'revenue_today' => (float) $paidOrdersToday->sum('total'),
            'customers' => Customer::query()
                ->whereIn('id', Order::query()
                    ->where('branch_id', $branchId)
                    ->whereNotNull('customer_id')
                    ->select('customer_id'))
                ->count(),
        ];
    }
}
