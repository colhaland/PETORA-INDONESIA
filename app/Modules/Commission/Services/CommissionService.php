<?php

declare(strict_types=1);

namespace App\Modules\Commission\Services;

class CommissionService
{
    public function calculate(float $amount, float $rate, string $type = 'percentage'): float
    {
        return $type === 'percentage' ? $amount * ($rate / 100) : $rate;
    }
}
