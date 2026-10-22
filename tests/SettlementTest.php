<?php

namespace SotersFolio\RexPay\Tests;

use PHPUnit\Framework\TestCase;
use SotersFolio\RexPay\Merchant\Settlement;

class SettlementTest extends TestCase
{
    public function testStandardSettlementCalculation(): void
    {
        $engine = new Settlement(0.0);
        $split = $engine->calculate(10000.00);

        $this->assertEquals(10000.00, $split['gross_amount']);
        $this->assertEquals(0.00, $split['platform_fee']);
        $this->assertEquals(150.00, $split['gateway_fee']);
        $this->assertEquals(9850.00, $split['net_disbursement']);
    }

    public function testSettlementFeeCap(): void
    {
        $engine = new Settlement(0.0);
        $split = $engine->calculate(200000.00);

        $this->assertEquals(2000.00, $split['gateway_fee']);
        $this->assertEquals(198000.00, $split['net_disbursement']);
    }

    public function testCustomPlatformFee(): void
    {
        $engine = new Settlement(1.0); // 1% platform fee
        $split = $engine->calculate(10000.00);

        $this->assertEquals(100.00, $split['platform_fee']);
        $this->assertEquals(150.00, $split['gateway_fee']);
        $this->assertEquals(9750.00, $split['net_disbursement']);
    }
}