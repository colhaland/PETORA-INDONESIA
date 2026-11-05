<?php

declare(strict_types=1);

namespace Tests\Feature\Commission;

use App\Modules\Commission\Services\CommissionService;
use Tests\TestCase;

class CommissionServiceTest extends TestCase
{
    public function test_calculate_percentage(): void
    {
        $svc = new CommissionService;
        $this->assertEquals(10.0, $svc->calculate(100, 10, 'percentage'));
    }
}
