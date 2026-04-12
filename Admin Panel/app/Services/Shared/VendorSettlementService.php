<?php

namespace App\Services\Shared;

use App\Models\Commission;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\VendorEarning;

class VendorSettlementService
{
    public function recordSettlement(Order $order, Vendor $vendor, float $grossAmount, float $commissionRate): array
    {
        $commissionAmount = round($grossAmount * ($commissionRate / 100), 2);
        $netAmount = round($grossAmount - $commissionAmount, 2);

        $commission = Commission::updateOrCreate(
            ['order_id' => $order->id],
            [
                'vendor_id'         => $vendor->id,
                'gross_amount'      => $grossAmount,
                'commission_rate'   => $commissionRate,
                'commission_amount' => $commissionAmount,
                'net_amount'        => $netAmount,
                'status'            => 'settled',
                'calculated_at'     => now(),
                'settled_at'        => now(),
                'metadata'          => [
                    'order_number' => $order->order_number,
                ],
            ]
        );

        $earning = VendorEarning::updateOrCreate(
            ['order_id' => $order->id],
            [
                'vendor_id'         => $vendor->id,
                'gross_amount'      => $grossAmount,
                'commission_amount' => $commissionAmount,
                'net_amount'        => $netAmount,
                'paid_amount'       => $netAmount,
                'pending_amount'    => 0,
                'settlement_status' => 'settled',
                'payment_status'    => $order->payment_status === 'paid' ? 'paid' : 'pending',
                'settled_at'        => now(),
                'metadata'          => [
                    'commission_id' => $commission->id,
                    'order_number'  => $order->order_number,
                ],
            ]
        );

        $order->update([
            'paid_amount'       => $netAmount,
            'pending_amount'    => 0,
            'settlement_status' => 'settled',
        ]);

        return [$commission, $earning];
    }
}
