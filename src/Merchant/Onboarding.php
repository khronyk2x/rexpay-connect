<?php

namespace SotersFolio\RexPay\Merchant;

use SotersFolio\RexPay\Contracts\MerchantProviderInterface;
use SotersFolio\RexPay\RexPay;

class Onboarding implements MerchantProviderInterface
{
    protected array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?: require __DIR__ . '/../../config/rexpay.php';
    }

    /**
     * Guided onboarding — returns instructions for manual signup.
     * When partner API access is available, this method will
     * programmatically create the account instead.
     */
    public function provision(array $merchantData): array
    {
        $kycResult = $this->validateKyc($merchantData);
        if (!$kycResult['valid']) {
            return [
                'status'  => 'KYC_INCOMPLETE',
                'action'  => 'fix_errors',
                'errors'  => $kycResult['errors']
            ];
        }

        return [
            'status'       => 'MANUAL_SIGNUP_REQUIRED',
            'action'       => 'redirect_to_signup',
            'signup_url'   => $this->getSignupUrl(),
            'instructions' => [
                'Visit the RexPay signup page and create a merchant account.',
                'Complete KYC verification on the RexPay dashboard.',
                'Copy your Username and Secret Key from Settings.',
                'Return here and enter your credentials to connect.',
            ],
            'kyc_preview'  => $kycResult['normalized']
        ];
    }

    /**
     * Test merchant credentials by attempting a ping to the API.
     */
    public function validateCredentials(string $username, string $secretKey): array
    {
        $mode = $this->config['mode'] ?? 'test';

        try {
            $client = new RexPay($username, $secretKey, $mode);
            $reachable = $client->ping();

            return [
                'valid'     => $reachable,
                'mode'      => $mode,
                'message'   => $reachable
                    ? 'Credentials accepted. RexPay endpoint is reachable.'
                    : 'Could not reach RexPay API. Check credentials or try again.'
            ];
        } catch (\Exception $e) {
            return [
                'valid'   => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    public function getSignupUrl(): string
    {
        return $this->config['onboarding']['signup_url'] ?? 'https://myrexpay.ng/auth/register';
    }

    /**
     * Validate merchant KYC data.
     */
    public function validateKyc(array $data): array
    {
        $errors = [];

        $required = ['business_name', 'email', 'phone', 'account_number', 'bank_code', 'bvn'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "Field '{$field}' is required.";
            }
        }

        if (!empty($data['bvn']) && !preg_match('/^[0-9]{11}$/', $data['bvn'])) {
            $errors['bvn'] = 'BVN must be exactly 11 numeric digits.';
        }

        if (!empty($data['account_number']) && !preg_match('/^[0-9]{10}$/', $data['account_number'])) {
            $errors['account_number'] = 'Account number must be a 10-digit NUBAN number.';
        }

        if (!empty($data['phone']) && !preg_match('/^[0-9+]{10,15}$/', $data['phone'])) {
            $errors['phone'] = 'Phone number format is invalid.';
        }

        $isValid = empty($errors);

        $normalized = $isValid ? [
            'merchantName'      => trim($data['business_name']),
            'contactEmail'      => strtolower(trim($data['email'])),
            'contactPhone'      => trim($data['phone']),
            'bankAccountNumber' => trim($data['account_number']),
            'bankCode'          => trim($data['bank_code']),
            'bvn'               => trim($data['bvn']),
            'businessRegNumber' => trim($data['cac_rc_number'] ?? ''),
            'kycStatus'         => 'VERIFIED',
            'verifiedAt'        => date('Y-m-d H:i:s')
        ] : [];

        return [
            'valid'      => $isValid,
            'errors'     => $errors,
            'normalized' => $normalized
        ];
    }
}
