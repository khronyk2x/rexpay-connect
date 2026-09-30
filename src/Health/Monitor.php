<?php

namespace SotersFolio\RexPay\Health;

class Monitor
{
    protected array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?: require __DIR__ . '/../../config/rexpay.php';
    }

    /**
     * Run connectivity diagnostics against all RexPay endpoints.
     */
    public function check(string $mode = 'test'): array
    {
        $envKey = ($mode === 'production') ? 'production' : 'sandbox';
        $endpoints = $this->config['endpoints'][$envKey];

        $results = [
            'mode'        => $mode,
            'timestamp'   => date('Y-m-d H:i:s'),
            'checks'      => [],
            'all_healthy' => true
        ];

        $results['checks']['pgs'] = $this->ping($endpoints['pgs'] . '/payment/v2/createPayment');
        $results['checks']['cps'] = $this->ping($endpoints['cps'] . '/getTransactionStatus');
        $results['checks']['public_key'] = $this->ping($endpoints['public_key']);

        foreach ($results['checks'] as $check) {
            if (!$check['reachable']) {
                $results['all_healthy'] = false;
            }
        }

        return $results;
    }

    protected function ping(string $url): array
    {
        $start = microtime(true);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $latencyMs = round((microtime(true) - $start) * 1000, 2);
        $err = curl_error($ch);
        curl_close($ch);

        $isReachable = ($httpCode > 0 && empty($err));

        return [
            'url'        => $url,
            'reachable'  => $isReachable,
            'http_code'  => $httpCode,
            'latency_ms' => $latencyMs,
            'status'     => $isReachable ? 'ONLINE' : 'UNREACHABLE',
            'error'      => $err ?: null
        ];
    }
}
