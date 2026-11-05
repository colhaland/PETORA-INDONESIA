<?php

declare(strict_types=1);

namespace App\Modules\Pos\Services;

use App\Models\Order;
use App\Models\Payment;

class PaymentService
{
    public function pay(Order $order, string $method, float $amount, string $idempotencyKey): Payment
    {
        return app(PosService::class)->pay($order, $method, $amount, $idempotencyKey);
    }
}
