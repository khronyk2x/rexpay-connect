<?php

namespace SotersFolio\RexPay\Contracts;

interface MerchantProviderInterface
{
    /**
     * Attempt to create or provision a merchant account.
     *
     * Returns the provisioning result. Implementations may:
     * - Return instructions for manual signup (guided flow)
     * - Programmatically create an account (partner/aggregator flow)
     */
    public function provision(array $merchantData): array;

    /**
     * Validate merchant credentials against the live API.
     */
    public function validateCredentials(string $username, string $secretKey): array;

    /**
     * Get the signup URL for manual registration.
     */
    public function getSignupUrl(): string;
}
