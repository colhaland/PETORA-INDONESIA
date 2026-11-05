<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Gate;

class ReportsService
{
    public function salesSummary(): array
    {
        Gate::authorize('reports.view');

        return [
            'total_orders' => Order::count(),
            'total_revenue' => (float) Order::sum('total'),
        ];
    }

    public function customerSummary(): array
    {
        Gate::authorize('reports.view');

        return [
            'total_customers' => Customer::count(),
        ];
    }
}
