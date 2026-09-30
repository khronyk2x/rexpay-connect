<?php

namespace SotersFolio\RexPay\Api;

use SotersFolio\RexPay\RexPay;

class Transaction
{
    protected RexPay $client;

    public function __construct(RexPay $client)
    {
        $this->client = $client;
    }

    /**
     * Verify payment status via CPS.
     */
    public function verify(string $transactionReference): array
    {
        $url = $this->client->getEndpoints()['cps'] . '/getTransactionStatus';
        $payload = [
            'transactionReference' => $transactionReference,
            'authToken'            => $this->client->getAuthToken(),
            'mode'                 => $this->client->getMode()
        ];

        return $this->client->request('POST', $url, $payload);
    }
}
