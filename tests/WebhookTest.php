<?php

namespace SotersFolio\RexPay\Tests;

use PHPUnit\Framework\TestCase;
use SotersFolio\RexPay\Api\Webhook;

class WebhookTest extends TestCase
{
    private string $secret = 'test_secret_key';
    private Webhook $webhook;

    protected function setUp(): void
    {
        $this->webhook = new Webhook($this->secret);
    }

    public function testValidSignatureVerification(): void
    {
        $payload = '{"event":"payment.success","data":{"reference":"TXN_001","amount":5000}}';
        $validSig = hash_hmac('sha512', $payload, $this->secret);

        $this->assertTrue($this->webhook->verifySignature($payload, $validSig));
    }

    public function testInvalidSignatureRejected(): void
    {
        $payload = '{"event":"payment.success","data":{"reference":"TXN_001","amount":5000}}';
        $this->assertFalse($this->webhook->verifySignature($payload, 'invalid_signature_hash'));
    }

    public function testNullSignatureRejected(): void
    {
        $payload = '{"event":"payment.success","data":{"reference":"TXN_001","amount":5000}}';
        $this->assertFalse($this->webhook->verifySignature($payload, null));
    }

    public function testWebhookReplayProtection(): void
    {
        $payload = '{"event":"payment.success","data":{"reference":"TXN_IDEMPOTENT_001","amount":7500}}';
        $sig = hash_hmac('sha512', $payload, $this->secret);

        $first = $this->webhook->processEvent($payload, $sig);
        $this->assertSame('PROCESSED', $first['status']);

        $second = $this->webhook->processEvent($payload, $sig);
        $this->assertSame('DUPLICATE_EVENT', $second['status']);
    }
}