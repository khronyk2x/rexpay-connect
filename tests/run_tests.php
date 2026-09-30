<?php
/**
 * Test Suite for sotersfolio/rexpay-connect
 */

require_once __DIR__ . '/../src/Contracts/PaymentGatewayInterface.php';
require_once __DIR__ . '/../src/Contracts/MerchantProviderInterface.php';
require_once __DIR__ . '/../src/Api/Payment.php';
require_once __DIR__ . '/../src/Api/Transaction.php';
require_once __DIR__ . '/../src/Api/PublicKey.php';
require_once __DIR__ . '/../src/Api/Webhook.php';
require_once __DIR__ . '/../src/RexPay.php';
require_once __DIR__ . '/../src/Merchant/Onboarding.php';
require_once __DIR__ . '/../src/Merchant/Connection.php';
require_once __DIR__ . '/../src/Merchant/Settlement.php';
require_once __DIR__ . '/../src/Health/Monitor.php';

use SotersFolio\RexPay\RexPay;
use SotersFolio\RexPay\Api\Webhook;
use SotersFolio\RexPay\Merchant\Onboarding;
use SotersFolio\RexPay\Merchant\Connection;
use SotersFolio\RexPay\Merchant\Settlement;
use SotersFolio\RexPay\Health\Monitor;

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function run()
    {
        echo "=========================================================\n";
        echo "SOTERSFOLIO/REXPAY-CONNECT TEST SUITE (PHP " . PHP_VERSION . ")\n";
        echo "=========================================================\n\n";

        $this->testCoreClient();
        $this->testSettlement();
        $this->testSettlementFeeCap();
        $this->testKycValidation();
        $this->testKycRejection();
        $this->testGuidedOnboarding();
        $this->testCredentialValidation();
        $this->testConnection();
        $this->testWebhookSignature();
        $this->testWebhookIdempotency();
        $this->testHealthMonitor();

        echo "\n---------------------------------------------------------\n";
        echo "RESULTS: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "---------------------------------------------------------\n";

        if (!empty($this->errors)) {
            echo "\nFailures:\n";
            foreach ($this->errors as $err) {
                echo "  [FAIL] $err\n";
            }
            exit(1);
        }
    }

    private function assert($condition, string $name)
    {
        if ($condition) {
            $this->passed++;
            echo "  [PASS] $name\n";
        } else {
            $this->failed++;
            $this->errors[] = $name;
            echo "  [FAIL] $name\n";
        }
    }

    private function testCoreClient()
    {
        echo "1. Core Client\n";
        $client = new RexPay('testuser', 'testpass', 'test');
        $this->assert($client->getAuthToken() === base64_encode('testuser:testpass'), "Auth token generated correctly");
        $this->assert($client->getMode() === 'test', "Mode is test");
        $this->assert($client->getUsername() === 'testuser', "Username stored");
        $this->assert(!empty($client->getEndpoints()), "Endpoints loaded from config");
    }

    private function testSettlement()
    {
        echo "\n2. Settlement Calculator (0% Platform Fee)\n";
        $engine = new Settlement(0.0);
        $split = $engine->calculate(10000.00);

        $this->assert($split['gross_amount'] === 10000.00, "Gross = NGN 10,000");
        $this->assert($split['platform_fee'] === 0.00, "Platform fee = 0");
        $this->assert($split['gateway_fee'] === 150.00, "Gateway fee = 1.5% = NGN 150");
        $this->assert($split['net_disbursement'] === 9850.00, "Net = NGN 9,850");
    }

    private function testSettlementFeeCap()
    {
        echo "\n3. Gateway Fee Cap (NGN 2,000)\n";
        $engine = new Settlement(0.0);
        $split = $engine->calculate(200000.00);

        $this->assert($split['gateway_fee'] === 2000.00, "Fee capped at NGN 2,000");
        $this->assert($split['net_disbursement'] === 198000.00, "Net = NGN 198,000");
    }

    private function testKycValidation()
    {
        echo "\n4. KYC Validation (Valid Data)\n";
        $onboarding = new Onboarding();
        $result = $onboarding->validateKyc([
            'business_name'  => 'Apex Electronics',
            'email'          => 'apex@example.com',
            'phone'          => '08012345678',
            'account_number' => '0123456789',
            'bank_code'      => '058',
            'bvn'            => '12345678901'
        ]);

        $this->assert($result['valid'] === true, "Valid KYC passes");
        $this->assert($result['normalized']['bvn'] === '12345678901', "BVN normalized");
        $this->assert($result['normalized']['contactEmail'] === 'apex@example.com', "Email normalized");
    }

    private function testKycRejection()
    {
        echo "\n5. KYC Validation (Invalid Data)\n";
        $onboarding = new Onboarding();

        $result = $onboarding->validateKyc([
            'business_name'  => 'Test Store',
            'email'          => 'test@test.com',
            'phone'          => '08012345678',
            'account_number' => '0123456789',
            'bank_code'      => '058',
            'bvn'            => '12345'  // too short
        ]);

        $this->assert($result['valid'] === false, "Invalid BVN rejected");
        $this->assert(isset($result['errors']['bvn']), "BVN error returned");
    }

    private function testGuidedOnboarding()
    {
        echo "\n6. Guided Onboarding Flow\n";
        $onboarding = new Onboarding();
        $result = $onboarding->provision([
            'business_name'  => 'Shoverse Store',
            'email'          => 'store@shoverse.com',
            'phone'          => '08099887766',
            'account_number' => '0011223344',
            'bank_code'      => '044',
            'bvn'            => '11223344556'
        ]);

        $this->assert($result['status'] === 'MANUAL_SIGNUP_REQUIRED', "Returns guided flow");
        $this->assert($result['action'] === 'redirect_to_signup', "Action is redirect");
        $this->assert(!empty($result['signup_url']), "Signup URL provided");
        $this->assert(count($result['instructions']) === 4, "Four steps returned");
    }

    private function testCredentialValidation()
    {
        echo "\n7. Credential Validation\n";
        $onboarding = new Onboarding();
        $result = $onboarding->validateCredentials('testuser', 'testpass');
        // We just check the structure, not the actual API call
        $this->assert(isset($result['valid']), "Returns valid flag");
        $this->assert(isset($result['message']), "Returns message");
    }

    private function testConnection()
    {
        echo "\n8. Merchant Connection\n";
        $conn = new Connection(101, 'merchant_user', 'merchant_secret');
        $this->assert($conn->hasCredentials() === true, "Has credentials");
        $this->assert($conn->isActive === true, "Active by default");

        $arr = $conn->toArray();
        $this->assert($arr['merchant_id'] === 101, "Merchant ID stored");
        $this->assert($arr['has_credentials'] === true, "Credentials flag set");

        $noKey = new Connection(102, 'user_only');
        $this->assert($noKey->hasCredentials() === false, "Missing secret detected");
    }

    private function testWebhookSignature()
    {
        echo "\n9. Webhook HMAC-SHA512 Verification\n";
        $secret = 'test_secret';
        $webhook = new Webhook($secret);

        $payload = '{"event":"payment.success","data":{"reference":"TXN_001","amount":5000}}';
        $validSig = hash_hmac('sha512', $payload, $secret);

        $this->assert($webhook->verifySignature($payload, $validSig) === true, "Valid signature passes");
        $this->assert($webhook->verifySignature($payload, 'bad_sig') === false, "Bad signature rejected");
        $this->assert($webhook->verifySignature($payload, null) === false, "Null signature rejected");
    }

    private function testWebhookIdempotency()
    {
        echo "\n10. Webhook Replay Protection\n";
        $secret = 'test_secret';
        $webhook = new Webhook($secret);

        $payload = '{"event":"payment.success","data":{"reference":"TXN_002","amount":7500}}';
        $sig = hash_hmac('sha512', $payload, $secret);

        $first = $webhook->processEvent($payload, $sig);
        $this->assert($first['status'] === 'PROCESSED', "First event processed");

        $second = $webhook->processEvent($payload, $sig);
        $this->assert($second['status'] === 'DUPLICATE_EVENT', "Duplicate caught");
    }

    private function testHealthMonitor()
    {
        echo "\n11. Health Monitor\n";
        $monitor = new Monitor();
        $report = $monitor->check('test');

        $this->assert(isset($report['checks']['pgs']), "PGS checked");
        $this->assert(isset($report['checks']['cps']), "CPS checked");
        $this->assert(isset($report['checks']['public_key']), "Public key checked");
        $this->assert(isset($report['all_healthy']), "Overall health reported");
    }
}

$runner = new TestRunner();
$runner->run();
