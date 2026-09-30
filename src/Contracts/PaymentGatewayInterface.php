<?php

namespace SotersFolio\RexPay\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Initialize a payment session.
     */
    public function create(array $payload): array;

    /**
     * Verify a transaction by reference.
     */
    public function verify(string $reference): array;
}
