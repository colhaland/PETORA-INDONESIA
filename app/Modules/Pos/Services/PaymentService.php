<?php

declare(strict_types=1);

namespace App\Modules\Pos\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function pay(Order $order, string $method, float $amount): Payment
    {
        return DB::transaction(function () use ($order, $method, $amount) {
            $payment = Payment::create(['order_id' => $order->id, 'method' => $method, 'amount' => $amount]);
            $paid = $order->payments()->sum('amount') + $amount;
            if ($paid >= (float) $order->total) {
                $order->update(['status' => 'paid']);
            } else {
                $order->update(['status' => 'partially_paid']);
            }

            return $payment;
        });
    }
}
