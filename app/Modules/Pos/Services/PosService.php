<?php
declare(strict_types=1);
namespace App\Modules\Pos\Services;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\CashShift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class PosService
{
    public function createOrder(int $branchId, ?int $customerId, int $userId): Order
    {
        Gate::authorize('pos.create');
        $order = Order::create([
            'order_number' => 'INV/PST/' . date('Ym') . '/' . str_pad((string) (Order::count() + 1), 6, '0', STR_PAD_LEFT),
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'created_by' => $userId,
            'status' => 'draft',
        ]);
        AuditService::log('pos.create', Order::class, $order->id);
        return $order;
    }
    public function addItem(Order $order, string $description, float $qty, float $unitPrice): OrderItem
    {
        Gate::authorize('pos.create');
        $item = OrderItem::create([
            'order_id' => $order->id,
            'description' => $description,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'total' => $qty * $unitPrice,
        ]);
        $order->update(['subtotal' => $order->items()->sum('total'), 'total' => $order->items()->sum('total')]);
        return $item;
    }
    public function pay(Order $order, string $method, float $amount): Payment
    {
        Gate::authorize('pos.create');
        return DB::transaction(function () use ($order, $method, $amount) {
            $payment = Payment::create(['order_id' => $order->id, 'method' => $method, 'amount' => $amount]);
            $paid = (float) $order->payments()->sum('amount');
            if ($paid >= (float) $order->total) {
                $order->update(['status' => 'paid']);
            } else {
                $order->update(['status' => 'partially_paid']);
            }
            AuditService::log('pos.pay', Order::class, $order->id);
            return $payment;
        });
    }
    public function openShift(int $branchId, int $userId, float $opening = 0): CashShift
    {
        Gate::authorize('pos.cash_manage');
        return CashShift::create(['branch_id' => $branchId, 'user_id' => $userId, 'status' => 'open', 'opening_amount' => $opening, 'opened_at' => now()]);
    }
    public function closeShift(CashShift $shift, float $closing): void
    {
        Gate::authorize('pos.cash_close');
        $shift->update(['status' => 'closed', 'closing_amount' => $closing, 'closed_at' => now()]);
        AuditService::log('pos.close_shift', CashShift::class, $shift->id);
    }
}
