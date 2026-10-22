<?php

namespace SotersFolio\RexPay\Tests;

use PHPUnit\Framework\TestCase;
use SotersFolio\RexPay\Exceptions\RexPayRequestException;
use SotersFolio\RexPay\RexPay;

class ExceptionsTest extends TestCase
{
    public function testRexPayRequestExceptionProperties(): void
    {
        $e = new RexPayRequestException("Payment failed", 400, ['error' => 'invalid_amount']);
        $this->assertSame("Payment failed", $e->getMessage());
        $this->assertSame(400, $e->getHttpCode());
        $this->assertSame(['error' => 'invalid_amount'], $e->getResponseBody());
    }

    public function testRequestThrowsOnConnectionFailureWhenConfigured(): void
    {
        $client = new RexPay('user', 'pass', 'test', ['pgs' => 'http://127.0.0.1:99999/invalid']);
        $this->expectException(RexPayRequestException::class);
        $client->request('POST', 'http://127.0.0.1:99999/invalid', [], true);
    }
}