<?php

namespace SotersFolio\RexPay\Tests;

use PHPUnit\Framework\TestCase;
use SotersFolio\RexPay\Merchant\Onboarding;

class OnboardingTest extends TestCase
{
    private Onboarding $onboarding;

    protected function setUp(): void
    {
        $this->onboarding = new Onboarding();
    }

    public function testValidKyc(): void
    {
        $result = $this->onboarding->validateKyc([
            'business_name'  => 'Apex Electronics',
            'email'          => 'apex@example.com',
            'phone'          => '08012345678',
            'account_number' => '0123456789',
            'bank_code'      => '058',
            'bvn'            => '12345678901'
        ]);

        $this->assertTrue($result['valid']);
        $this->assertSame('12345678901', $result['normalized']['bvn']);
        $this->assertSame('apex@example.com', $result['normalized']['contactEmail']);
    }

    public function testInvalidKycRejection(): void
    {
        $result = $this->onboarding->validateKyc([
            'business_name'  => 'Test Store',
            'email'          => 'test@test.com',
            'phone'          => '08012345678',
            'account_number' => '0123456789',
            'bank_code'      => '058',
            'bvn'            => '12345'
        ]);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('bvn', $result['errors']);
    }

    public function testGuidedOnboardingFlow(): void
    {
        $result = $this->onboarding->provision([
            'business_name'  => 'Shoverse Store',
            'email'          => 'store@shoverse.com',
            'phone'          => '08099887766',
            'account_number' => '0011223344',
            'bank_code'      => '044',
            'bvn'            => '11223344556'
        ]);

        $this->assertSame('MANUAL_SIGNUP_REQUIRED', $result['status']);
        $this->assertSame('redirect_to_signup', $result['action']);
        $this->assertNotEmpty($result['signup_url']);
        $this->assertCount(4, $result['instructions']);
    }

    public function testCredentialValidationStructure(): void
    {
        $result = $this->onboarding->validateCredentials('testuser', 'testpass');
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('message', $result);
    }
}