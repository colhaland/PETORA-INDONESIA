<?php
declare(strict_types=1);
namespace App\Modules\Loyalty\Services;
use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class LoyaltyService
{
    public function earn(int $customerId, float $orderValue): int
    {
        Gate::authorize('loyalty.adjust');
        $rate = (int) config('petora.loyalty.earn_per_amount', 10000);
        $points = (int) floor($orderValue / $rate);
        AuditService::log('loyalty.earn', Customer::class, $customerId, [], ['points' => $points]);
        return $points;
    }
    public function redeem(int $customerId, int $points): int
    {
        Gate::authorize('loyalty.adjust');
        $value = (int) (config('petora.loyalty.redeem_value', 10000) * ($points / config('petora.loyalty.redeem_points', 100)));
        AuditService::log('loyalty.redeem', Customer::class, $customerId, [], ['points' => $points, 'value' => $value]);
        return $value;
    }
}
