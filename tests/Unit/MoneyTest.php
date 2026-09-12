<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_rounds_line_tax_using_half_up_scale_of_two(): void
    {
        $this->assertSame('39.98', Money::lineSubtotal('19.99', 2));
        $this->assertSame('7.20', Money::taxAmount('39.98', '18.00'));
        $this->assertSame('47.18', Money::add('39.98', '7.20'));
    }
}
