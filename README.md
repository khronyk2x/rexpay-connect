# sotersfolio/rexpay-connect

PHP SDK and merchant connector for **RexPay** (Global Accelerex).

One package to handle payments, verification, merchant onboarding, webhooks, and health monitoring. Works standalone, with Laravel, or any PHP framework.

## Install

```bash
composer require sotersfolio/rexpay-connect
```

## Quick Start

```php
use SotersFolio\RexPay\RexPay;

$rexpay = new RexPay('your_username', 'your_secret_key', 'test');

// Create a payment
$response = $rexpay->payment->create([
    'reference'   => 'ORDER_' . uniqid(),
    'amount'      => 5000.00,
    'currency'    => 'NGN',
    'userId'      => 'customer@email.com',
    'callbackUrl' => 'https://yoursite.com/callback',
]);

// Verify a transaction
$status = $rexpay->transaction->verify('ORDER_abc123');

// Register public key
$rexpay->publicKey->register('your_public_key_string');
```

## Merchant Onboarding

```php
use SotersFolio\RexPay\Merchant\Onboarding;

$onboarding = new Onboarding();

// Validate merchant KYC
$kyc = $onboarding->validateKyc([
    'business_name'  => 'My Store',
    'email'          => 'store@example.com',
    'phone'          => '08012345678',
    'account_number' => '0123456789',
    'bank_code'      => '058',
    'bvn'            => '12345678901',
]);

// Start guided onboarding (returns signup instructions)
$result = $onboarding->provision($kycData);

// Validate credentials after merchant creates their RexPay account
$check = $onboarding->validateCredentials('merchant_username', 'merchant_secret');
```

## Webhooks

```php
use SotersFolio\RexPay\Api\Webhook;

$webhook = new Webhook('your_secret_key');
$result = $webhook->processEvent(
    file_get_contents('php://input'),
    $_SERVER['HTTP_X_REXPAY_SIGNATURE'] ?? null
);

if ($result['status'] === 'PROCESSED') {
    // Handle successful payment
}
```

## Settlement Calculator

```php
use SotersFolio\RexPay\Merchant\Settlement;

$settlement = new Settlement(0.0); // 0% platform fee
$split = $settlement->calculate(10000.00);
// => platform_fee: 0, gateway_fee: 150, net: 9850
```

## Health Check

```php
use SotersFolio\RexPay\Health\Monitor;

$monitor = new Monitor();
$report = $monitor->check('test');
// => checks: { pgs: ONLINE, cps: ONLINE, public_key: ONLINE }
```

## Laravel

Add to `.env`:
```
REXPAY_USERNAME=your_username
REXPAY_SECRET_KEY=your_secret_key
REXPAY_MODE=test
```

The service provider auto-registers. Use the facade:
```php
$rexpay = app('rexpay');
$rexpay->payment->create([...]);
```

Publish config:
```bash
php artisan vendor:publish --tag=rexpay-config
```

## Running Tests

```bash
php tests/run_tests.php
```

## License

MIT
