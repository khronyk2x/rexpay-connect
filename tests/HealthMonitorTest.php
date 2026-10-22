<?php

namespace SotersFolio\RexPay\Tests;

use PHPUnit\Framework\TestCase;
use SotersFolio\RexPay\Health\Monitor;

class HealthMonitorTest extends TestCase
{
    public function testHealthCheckStructure(): void
    {
        $monitor = new Monitor();
        $report = $monitor->check('test');

        $this->assertArrayHasKey('checks', $report);
        $this->assertArrayHasKey('pgs', $report['checks']);
        $this->assertArrayHasKey('cps', $report['checks']);
        $this->assertArrayHasKey('public_key', $report['checks']);
        $this->assertArrayHasKey('all_healthy', $report);
    }
}