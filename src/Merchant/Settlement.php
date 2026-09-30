<?php

namespace SotersFolio\RexPay\Merchant;

class Settlement
{
    protected float $platformFeePercent;

    public function __construct(float $platformFeePercent = 0.0)
    {
        $this->platformFeePercent = $platformFeePercent;
    }

    /**
     * Calculate the fee split for a transaction.
     * Platform fee defaults to 0%. Gateway fee is RexPay's 1.5% capped at NGN 2,000.
     */
    public function calculate(float $grossAmount): array
    {
        $platformFee = round(($grossAmount * ($this->platformFeePercent / 100)), 2);

        $rawGatewayFee = $grossAmount * 0.015;
        $gatewayFee = round(min($rawGatewayFee, 2000.00), 2);

        $netDisbursement = round($grossAmount - $platformFee - $gatewayFee, 2);

        return [
            'gross_amount'      => $grossAmount,
            'platform_fee_rate' => $this->platformFeePercent,
            'platform_fee'      => $platformFee,
            'gateway_fee'       => $gatewayFee,
            'net_disbursement'  => max(0.00, $netDisbursement),
            'settlement_type'   => 'Direct to Merchant Bank Account',
            'payout_schedule'   => 'RexPay standard settlement'
        ];
    }
}
