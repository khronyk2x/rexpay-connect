<?php

namespace SotersFolio\RexPay;

use SotersFolio\RexPay\Api\Payment;
use SotersFolio\RexPay\Api\Transaction;
use SotersFolio\RexPay\Api\PublicKey;
use SotersFolio\RexPay\Api\Webhook;

class RexPay
{
    protected string $username;
    protected string $secretKey;
    protected string $authToken;
    protected string $mode;
    protected array $endpoints;

    public Payment $payment;
    public Transaction $transaction;
    public PublicKey $publicKey;
    public Webhook $webhook;

    const VERSION = '3.0.0';

    public function __construct(string $username, string $secretKey, string $mode = 'test', ?array $customEndpoints = null)
    {
        $this->username = $username;
        $this->secretKey = $secretKey;
        $this->authToken = base64_encode("{$username}:{$secretKey}");
        $this->mode = $mode;

        $config = require __DIR__ . '/../config/rexpay.php';
        $envKey = ($this->mode === 'production') ? 'production' : 'sandbox';
        $this->endpoints = $customEndpoints ?: $config['endpoints'][$envKey];

        $this->payment     = new Payment($this);
        $this->transaction = new Transaction($this);
        $this->publicKey   = new PublicKey($this);
        $this->webhook     = new Webhook($secretKey);
    }

    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getEndpoints(): array
    {
        return $this->endpoints;
    }

    /**
     * Check if an API response indicates success (responseCode '00').
     */
    public static function isSuccessful($response): bool
    {
        if (is_array($response)) {
            return ($response['success'] ?? false) === true;
        }
        return is_object($response) && isset($response->responseCode) && $response->responseCode === '00';
    }

    /**
     * Quick connectivity test — tries to hit PGS endpoint.
     */
    public function ping(): bool
    {
        $ch = curl_init($this->endpoints['pgs']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code > 0;
    }

    /**
     * Low-level HTTP request used by all API classes.
     */
    public function request(string $method, string $url, array $data = []): array
    {
        $ch = curl_init($url);
        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . $this->authToken,
            'User-Agent: SotersFolio-RexPay/' . self::VERSION
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success'  => false,
                'httpCode' => $httpCode,
                'error'    => $curlError ?: 'Connection failed'
            ];
        }

        $decoded = json_decode($response, true);
        return [
            'success'  => ($httpCode >= 200 && $httpCode < 300),
            'httpCode' => $httpCode,
            'data'     => $decoded ?: ['raw' => $response]
        ];
    }
}
