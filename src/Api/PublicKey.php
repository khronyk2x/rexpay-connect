<?php

namespace SotersFolio\RexPay\Api;

use SotersFolio\RexPay\RexPay;

class PublicKey
{
    protected RexPay $client;

    public function __construct(RexPay $client)
    {
        $this->client = $client;
    }

    /**
     * Register a merchant's public key to avoid NoSuchElementException.
     */
    public function register(string $publicKey, ?string $clientCode = null): array
    {
        $url = $this->client->getEndpoints()['public_key'];
        $payload = [
            'clientPublicKey' => $publicKey,
            'clientCode'      => $clientCode ?: $this->client->getUsername(),
            'authToken'       => $this->client->getAuthToken()
        ];

        return $this->client->request('POST', $url, $payload);
    }
}
