<?php

declare(strict_types=1);

namespace App\Modules\Pos\Services;

use App\Models\Order;
use App\Support\BranchAuthorization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderService
{
    public function createDraft(int $branchId, ?int $customerId, int $userId): Order
    {
        Gate::authorize('pos.create');
        BranchAuthorization::assertCanAccess($branchId);

        return DB::transaction(function () use ($branchId, $customerId, $userId) {
            $seq = (Order::where('branch_id', $branchId)->count() + 1);
            $number = 'INV/PST/'.date('Ym').'/'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);

            return Order::create([
                'order_number' => $number,
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'created_by' => $userId,
                'status' => 'draft',
            ]);
        });
    }
}
