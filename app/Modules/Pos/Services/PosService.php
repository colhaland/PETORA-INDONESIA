<?php

declare(strict_types=1);

namespace App\Modules\Pos\Services;

use App\Models\Branch;
use App\Models\CashShift;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class PosService
{
    public function createOrder(int $branchId, ?int $customerId, int $userId): Order
    {
        Gate::authorize('pos.create');
        $branchCode = Branch::query()->find($branchId)?->code ?? 'PST';
        $order = Order::create([
            'order_number' => DocumentNumberService::next('INV', $branchCode),
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'created_by' => $userId,
            'status' => 'draft',
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
        ]);
        AuditService::log('pos.create', Order::class, $order->id);

        return $order;
    }

    public function addItem(Order $order, string $description, float $qty, float $unitPrice): OrderItem
    {
        Gate::authorize('pos.create');
        $order->refresh();
        if ($order->isPaid() || ($order->status ?? 'draft') === 'paid') {
            throw new \RuntimeException('Order yang sudah dibayar tidak dapat diubah.');
        }
        $qtyS = number_format($qty, 3, '.', '');
        $priceS = Money::of($unitPrice, 2);
        $lineTotal = Money::mul($qtyS, $priceS, 2);
        $item = OrderItem::create([
            'order_id' => $order->id,
            'description' => $description,
            'quantity' => $qtyS,
            'unit_price' => $priceS,
            'total' => $lineTotal,
        ]);
        $subtotal = '0.00';
        foreach ($order->items()->get() as $row) {
            $subtotal = Money::add($subtotal, (string) $row->total, 2);
        }
        $order->update(['subtotal' => $subtotal, 'total' => $subtotal]);

        return $item;
    }

    public function pay(Order $order, string $method, float $amount, ?string $idempotencyKey = null): Payment
    {
        Gate::authorize('pos.create');

        return DB::transaction(function () use ($order, $method, $amount, $idempotencyKey) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $amountS = Money::of($amount, 2);
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $method,
                'amount' => $amountS,
            ]);
            $paid = '0.00';
            foreach ($order->payments()->get() as $p) {
                $paid = Money::add($paid, (string) $p->amount, 2);
            }
            $total = Money::of($order->total, 2);
            $updates = [];
            if (Money::cmp($paid, $total, 2) >= 0 && Money::isPositive($total, 2)) {
                $updates['status'] = 'paid';
                $updates['paid_at'] = $order->paid_at ?: now();
            } elseif (Money::isPositive($paid, 2)) {
                $updates['status'] = 'partially_paid';
            }
            if ($idempotencyKey && Schema::hasColumn('orders', 'idempotency_key') && empty($order->idempotency_key)) {
                $updates['idempotency_key'] = $idempotencyKey;
            }
            if ($updates !== []) {
                $order->update($updates);
            }
            AuditService::log('pos.pay', Order::class, $order->id, [], ['status' => $order->fresh()->status, 'paid' => $paid]);

            return $payment;
        });
    }

    public function openShift(int $branchId, int $userId, float $opening = 0): CashShift
    {
        Gate::authorize('pos.cash_manage');
        $shift = CashShift::create([
            'branch_id' => $branchId,
            'user_id' => $userId,
            'status' => 'open',
            'opening_amount' => round($opening, 2),
            'opened_at' => now(),
        ]);
        AuditService::log('cash.open', CashShift::class, $shift->id);

        return $shift;
    }

    public function closeShift(CashShift $shift, float $closing): void
    {
        Gate::authorize('pos.cash_close');
        $shift->update(['status' => 'closed', 'closing_amount' => round($closing, 2), 'closed_at' => now()]);
        AuditService::log('cash.close', CashShift::class, $shift->id, [], ['closing_amount' => round($closing, 2)]);
    }

    public function voidOrder(Order $order, ?string $reason = null): void
    {
        Gate::authorize('pos.void');
        $order = Order::query()->lockForUpdate()->findOrFail($order->id);
        if ($order->payments()->exists() && (float) $order->payments()->sum('amount') > 0) {
            throw new \RuntimeException('Order dengan pembayaran tidak dapat di-void. Gunakan refund.');
        }
        if (in_array($order->status, [Order::STATUS_PAID, Order::STATUS_COMPLETED, Order::STATUS_REFUNDED], true)) {
            throw new \RuntimeException('Order paid/completed/refunded tidak dapat di-void.');
        }
        $old = $order->status;
        $order->update(['status' => Order::STATUS_VOID]);
        AuditService::log('pos.void', Order::class, $order->id, ['status' => $old], ['status' => Order::STATUS_VOID, 'reason' => $reason]);
    }
}
