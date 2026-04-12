<?php

namespace App\Services\Shared;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WalletTransaction;
use App\Services\Shared\VendorSettlementService;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function __construct(private readonly VendorSettlementService $settlement)
    {
    }

    // -------------------------------------------------------------------------
    // Credit / Debit
    // -------------------------------------------------------------------------

    public function creditUser(User $user, float $amount, string $description, ?Order $order = null): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $description, $order) {
            $user->increment('wallet_amount', $amount);
            $user->refresh();

            return WalletTransaction::create([
                'user_id'     => $user->id,
                'order_id'    => $order?->id,
                'type'        => 'credit',
                'amount'      => $amount,
                'balance'     => $user->wallet_amount,
                'description' => $description,
            ]);
        });
    }

    public function debitUser(User $user, float $amount, string $description, ?Order $order = null): WalletTransaction
    {
        if ($user->wallet_amount < $amount) {
            throw new \RuntimeException('Insufficient wallet balance.');
        }

        return DB::transaction(function () use ($user, $amount, $description, $order) {
            $user->decrement('wallet_amount', $amount);
            $user->refresh();

            return WalletTransaction::create([
                'user_id'     => $user->id,
                'order_id'    => $order?->id,
                'type'        => 'debit',
                'amount'      => $amount,
                'balance'     => $user->wallet_amount,
                'description' => $description,
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // Vendor settlement after delivery
    // -------------------------------------------------------------------------

    public function settleVendorPayout(Order $order): WalletTransaction
    {
        $vendor      = $order->vendor()->with('author')->firstOrFail();
        $vendorOwner = $vendor->author;

        $commission  = $order->total * ($vendor->commission_rate / 100);
        $vendorShare = round($order->total - $commission, 2);

        return DB::transaction(function () use ($order, $vendor, $vendorOwner, $vendorShare) {
            $this->settlement->recordSettlement(
                order: $order,
                vendor: $vendor,
                grossAmount: (float) $order->total,
                commissionRate: (float) $vendor->commission_rate,
            );

            $vendorOwner->increment('wallet_amount', $vendorShare);
            $vendorOwner->refresh();

            return WalletTransaction::create([
                'user_id'     => $vendorOwner->id,
                'order_id'    => $order->id,
                'type'        => 'credit',
                'amount'      => $vendorShare,
                'balance'     => $vendorOwner->wallet_amount,
                'description' => "Order #{$order->order_number} settlement (commission: {$vendor->commission_rate}%)",
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // Admin manual payout
    // -------------------------------------------------------------------------

    public function processVendorPayout(Vendor $vendor, float $amount, string $method, int $adminId): \App\Models\Payout
    {
        $vendorOwner = $vendor->author;

        if ($vendorOwner->wallet_amount < $amount) {
            throw new \RuntimeException('Vendor wallet balance insufficient for payout.');
        }

        return DB::transaction(function () use ($vendor, $vendorOwner, $amount, $method, $adminId) {
            $this->debitUser(
                $vendorOwner,
                $amount,
                "Payout to vendor via {$method}",
            );

            return \App\Models\Payout::create([
                'vendor_id'      => $vendor->id,
                'admin_id'       => $adminId,
                'amount'         => $amount,
                'method'         => $method,
                'payment_status' => 'success',
            ]);
        });
    }
}


