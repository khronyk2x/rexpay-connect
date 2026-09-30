<?php

namespace SotersFolio\RexPay\Api;

use SotersFolio\RexPay\RexPay;

class Payment
{
    protected RexPay $client;

    public function __construct(RexPay $client)
    {
        $this->client = $client;
    }

    /**
     * Initialize a payment session (v2).
     */
    public function create(array $payload): array
    {
        $url = $this->client->getEndpoints()['pgs'] . '/payment/v2/createPayment';
        $payload['authToken'] = $this->client->getAuthToken();
        $payload['mode'] = $this->client->getMode();

        return $this->client->request('POST', $url, $payload);
    }

    /**
     * Make a direct payment (v1) — card, USSD, or bank transfer.
     */
    public function charge(array $payload): array
    {
        $url = $this->client->getEndpoints()['pgs'] . '/payment/v1/makePayment';
        $payload['authToken'] = $this->client->getAuthToken();
        $payload['mode'] = $this->client->getMode();

        return $this->client->request('POST', $url, $payload);
    }

    /**
     * Get the checkout redirect URL for a payment reference.
     */
    public function getCheckoutUrl(string $reference): string
    {
        return $this->client->getEndpoints()['checkout'] . '?reference=' . urlencode($reference);
    }
}
