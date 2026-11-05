<?php
declare(strict_types=1);
namespace App\Modules\Promotion\Services;
class PromotionService
{
    public function applyDiscount(float $total, float $discount): float
    {
        return max(0, $total - $discount);
    }
}
