<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Support\BranchAuthorization;
use Illuminate\Support\Facades\Gate;

class ReportsService
{
    public function salesSummary(): array
    {
        Gate::authorize('reports.view');
        $branchId = BranchAuthorization::currentId();
        $sales = Order::query()
            ->where('branch_id', $branchId)
            ->whereIn('status', [Order::STATUS_PAID, Order::STATUS_COMPLETED]);

        return [
            'total_orders' => (clone $sales)->count(),
            'total_revenue' => (float) $sales->sum('total'),
        ];
    }

    public function customerSummary(): array
    {
        Gate::authorize('reports.view');
        $branchId = BranchAuthorization::currentId();

        return [
            'total_customers' => Customer::query()
                ->whereIn('id', Order::query()
                    ->where('branch_id', $branchId)
                    ->whereNotNull('customer_id')
                    ->select('customer_id'))
                ->count(),
        ];
    }
}
