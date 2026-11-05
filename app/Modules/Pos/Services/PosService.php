<?php

declare(strict_types=1);

namespace App\Modules\Pos\Services;

use App\Models\Branch;
use App\Models\CashShift;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Support\AuditService;
use App\Support\BranchAuthorization;
use App\Support\DocumentNumberService;
use App\Support\Money;
use App\Support\SettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PosService
{
    public function createOrder(int $branchId, ?int $customerId, int $userId): Order
    {
        Gate::authorize('pos.create');
        BranchAuthorization::assertCanAccess($branchId);
        $branch = Branch::query()->findOrFail($branchId);
        $branchCode = $branch->code;
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

    public function addItem(Order $order, int $productId, float $qty): OrderItem
    {
        Gate::authorize('pos.create');
        if (! is_finite($qty) || $qty <= 0) {
            throw new \InvalidArgumentException('Jumlah produk harus lebih besar dari nol.');
        }

        return DB::transaction(function () use ($order, $productId, $qty): OrderItem {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            BranchAuthorization::assertCanAccess((int) $lockedOrder->branch_id);
            if (! in_array($lockedOrder->status, [Order::STATUS_DRAFT, Order::STATUS_OPEN], true)
                || $lockedOrder->payments()->exists()) {
                throw new \RuntimeException('Order yang sudah dibayar atau ditutup tidak dapat diubah.');
            }

            $product = Product::query()->where('status', 'active')->findOrFail($productId);
            $price = Money::of($product->sell_price, 2);
            if (Money::cmp($price, (string) $product->min_price, 2) < 0 || ! Money::isPositive($price, 2)) {
                throw new \RuntimeException('Harga jual produk tidak valid; periksa harga katalog.');
            }

            $quantity = number_format($qty, 3, '.', '');
            $item = $lockedOrder->items()->create([
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $price,
                'total' => Money::mul($quantity, $price, 2),
            ]);

            $subtotal = '0.00';
            foreach ($lockedOrder->items()->get() as $row) {
                $subtotal = Money::add($subtotal, (string) $row->total, 2);
            }
            $taxEnabled = filter_var(SettingsService::get('tax.enabled', config('petora.tax.enabled')), FILTER_VALIDATE_BOOLEAN);
            $taxRate = (float) SettingsService::get('tax.rate', config('petora.tax.rate', 0));
            $taxIncluded = config('petora.tax.price_mode') === 'inclusive';
            $tax = '0.00';
            $total = $subtotal;
            if ($taxEnabled) {
                $tax = $taxIncluded
                    ? Money::of((float) $subtotal * $taxRate / (100 + $taxRate), 2)
                    : Money::of((float) $subtotal * ($taxRate / 100), 2);
                if (! $taxIncluded) {
                    $total = Money::add($subtotal, $tax, 2);
                }
            }

            $lockedOrder->update(['subtotal' => $subtotal, 'tax' => $tax, 'total' => $total]);
            AuditService::log('pos.add_item', Order::class, $lockedOrder->id, [], [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $price,
                'total' => $item->total,
            ]);

            return $item;
        });
    }

    public function pay(Order $order, string $method, float $amount, ?string $idempotencyKey = null): Payment
    {
        Gate::authorize('pos.create');
        if (! is_string($idempotencyKey) || trim($idempotencyKey) === '' || strlen($idempotencyKey) > 100) {
            throw new \InvalidArgumentException('Kunci idempotensi pembayaran wajib diisi.');
        }
        if (! in_array($method, ['cash', 'card', 'bank_transfer', 'e_wallet'], true)) {
            throw new \InvalidArgumentException('Metode pembayaran tidak valid.');
        }

        return DB::transaction(function () use ($order, $method, $amount, $idempotencyKey) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $amountS = Money::of($amount, 2);
            if (! Money::isPositive($amountS, 2)) {
                throw new \InvalidArgumentException('Nominal pembayaran harus lebih besar dari nol.');
            }

            $existingPayment = Payment::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existingPayment) {
                if ($existingPayment->order_id !== $order->id
                    || $existingPayment->method !== $method
                    || Money::cmp((string) $existingPayment->amount, $amountS, 2) !== 0) {
                    throw new \RuntimeException('Kunci idempotensi sudah digunakan untuk pembayaran lain.');
                }

                return $existingPayment;
            }

            if (! in_array($order->status, [Order::STATUS_DRAFT, Order::STATUS_OPEN, Order::STATUS_PARTIALLY_PAID], true)) {
                throw new \RuntimeException('Status order tidak mengizinkan pembayaran.');
            }

            $paid = '0.00';
            foreach ($order->payments()->lockForUpdate()->get() as $existing) {
                $paid = Money::add($paid, (string) $existing->amount, 2);
            }
            $total = Money::of($order->total, 2);
            $balance = Money::sub($total, $paid, 2);
            if (! Money::isPositive($balance, 2) || Money::cmp($amountS, $balance, 2) > 0) {
                throw new \InvalidArgumentException('Nominal pembayaran melebihi saldo order.');
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $method,
                'amount' => $amountS,
                'idempotency_key' => $idempotencyKey,
            ]);
            $paid = Money::add($paid, $amountS, 2);
            $updates = [];
            if (Money::cmp($paid, $total, 2) >= 0 && Money::isPositive($total, 2)) {
                $updates['status'] = 'paid';
                $updates['paid_at'] = $order->paid_at ?: now();
            } elseif (Money::isPositive($paid, 2)) {
                $updates['status'] = 'partially_paid';
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
        BranchAuthorization::assertCanAccess($branchId);
        if ($opening < 0) {
            throw new \InvalidArgumentException('Saldo pembukaan tidak boleh negatif.');
        }
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
