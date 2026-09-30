<?php

namespace SotersFolio\RexPay\Api;

class Webhook
{
    protected string $secretKey;
    protected array $processedReferences = [];

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Verify HMAC-SHA512 signature against raw payload.
     */
    public function verifySignature(string $rawPayload, ?string $signatureHeader): bool
    {
        if (empty($signatureHeader) || empty($this->secretKey)) {
            return false;
        }

        $expected = hash_hmac('sha512', $rawPayload, $this->secretKey);
        return hash_equals($expected, $signatureHeader);
    }

    /**
     * Process webhook event with signature verification and idempotency.
     */
    public function processEvent(string $rawPayload, ?string $signatureHeader): array
    {
        if (!$this->verifySignature($rawPayload, $signatureHeader)) {
            return [
                'status'  => 'INVALID_SIGNATURE',
                'message' => 'HMAC signature verification failed.'
            ];
        }

        $data = json_decode($rawPayload, true);
        if (!$data || !is_array($data)) {
            return [
                'status'  => 'MALFORMED_PAYLOAD',
                'message' => 'Unable to decode event JSON.'
            ];
        }

        $reference = $data['data']['reference'] ?? ($data['reference'] ?? null);
        if (!$reference) {
            return [
                'status'  => 'MISSING_REFERENCE',
                'message' => 'Event does not contain transaction reference.'
            ];
        }

        if (in_array($reference, $this->processedReferences)) {
            return [
                'status'    => 'DUPLICATE_EVENT',
                'reference' => $reference,
                'message'   => 'Transaction has already been processed.'
            ];
        }

        $this->processedReferences[] = $reference;

        return [
            'status'     => 'PROCESSED',
            'reference'  => $reference,
            'event'      => $data['event'] ?? 'payment.success',
            'amount'     => $data['data']['amount'] ?? ($data['amount'] ?? 0),
            'currency'   => $data['data']['currency'] ?? 'NGN',
            'timestamp'  => date('Y-m-d H:i:s')
        ];
    }
}
