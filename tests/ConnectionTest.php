<?php

namespace SotersFolio\RexPay\Tests;

use PHPUnit\Framework\TestCase;
use SotersFolio\RexPay\Merchant\Connection;

class ConnectionTest extends TestCase
{
    public function testActiveConnectionWithCredentials(): void
    {
        $conn = new Connection(101, 'merchant_user', 'merchant_secret');
        $this->assertTrue($conn->hasCredentials());
        $this->assertTrue($conn->isActive);

        $arr = $conn->toArray();
        $this->assertSame(101, $arr['merchant_id']);
        $this->assertTrue($arr['has_credentials']);
    }

    public function testMissingCredentials(): void
    {
        $noKey = new Connection(102, 'user_only');
        $this->assertFalse($noKey->hasCredentials());
        $this->assertFalse($noKey->toArray()['has_credentials']);
    }
}