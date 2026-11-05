<?php
declare(strict_types=1);
namespace Tests\Feature\Promotion;
use App\Modules\Promotion\Services\PromotionService;
use Tests\TestCase;
class PromotionServiceTest extends TestCase
{
    public function test_apply_discount(): void
    {
        $svc = new PromotionService();
        $this->assertEquals(90.0, $svc->applyDiscount(100, 10));
    }
}
