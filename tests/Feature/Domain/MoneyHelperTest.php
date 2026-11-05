<?php

declare(strict_types=1);

namespace Tests\Feature\Domain;

use App\Support\Money;
use Tests\TestCase;

class MoneyHelperTest extends TestCase
{
    public function test_money_add_and_mul(): void
    {
        $this->assertSame('20000.00', Money::mul('2', '10000'));
        $this->assertSame('30000.00', Money::add('10000', '20000'));
        $this->assertSame(-1, Money::cmp('100', '200'));
    }
}
