<?php

namespace SotersFolio\RexPay\Merchant;

class Connection
{
    public $merchantId;
    public string $username;
    public ?string $secretKey;
    public bool $publicKeyRegistered = false;
    public string $kycStatus = 'PENDING';
    public bool $isActive = false;
    public string $createdAt;

    public function __construct(
        $merchantId,
        string $username,
        ?string $secretKey = null
    ) {
        $this->merchantId = $merchantId;
        $this->username = $username;
        $this->secretKey = $secretKey;
        $this->isActive = true;
        $this->createdAt = date('Y-m-d H:i:s');
    }

    public function hasCredentials(): bool
    {
        return !empty($this->username) && !empty($this->secretKey);
    }

    public function toArray(): array
    {
        return [
            'merchant_id'           => $this->merchantId,
            'username'              => $this->username,
            'has_credentials'       => $this->hasCredentials(),
            'public_key_registered' => $this->publicKeyRegistered,
            'kyc_status'            => $this->kycStatus,
            'is_active'             => $this->isActive,
            'created_at'            => $this->createdAt
        ];
    }
}
