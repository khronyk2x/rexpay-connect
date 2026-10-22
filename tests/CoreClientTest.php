<?php

namespace SotersFolio\RexPay\Tests;

use PHPUnit\Framework\TestCase;
use SotersFolio\RexPay\RexPay;

class CoreClientTest extends TestCase
{
    private RexPay $client;

    protected function setUp(): void
    {
        $this->client = new RexPay('testuser', 'testpass', 'test');
    }

    public function testAuthTokenGeneration(): void
    {
        $expectedToken = base64_encode('testuser:testpass');
        $this->assertSame($expectedToken, $this->client->getAuthToken());
    }

    public function testClientMode(): void
    {
        $this->assertSame('test', $this->client->getMode());
    }

    public function testClientUsername(): void
    {
        $this->assertSame('testuser', $this->client->getUsername());
    }

    public function testEndpointsLoaded(): void
    {
        $endpoints = $this->client->getEndpoints();
        $this->assertNotEmpty($endpoints);
        $this->assertArrayHasKey('pgs', $endpoints);
        $this->assertArrayHasKey('cps', $endpoints);
    }

    public function testIsSuccessfulWithObject(): void
    {
        $successObj = (object)['responseCode' => '00'];
        $failObj = (object)['responseCode' => '99'];

        $this->assertTrue(RexPay::isSuccessful($successObj));
        $this->assertFalse(RexPay::isSuccessful($failObj));
    }

    public function testIsSuccessfulWithArray(): void
    {
        $this->assertTrue(RexPay::isSuccessful(['success' => true]));
        $this->assertFalse(RexPay::isSuccessful(['success' => false]));
    }
}