<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WalletAndPayoutsSeeder extends Seeder
{
    public function run(): void
    {
        $now      = Carbon::now();
        $adminId  = DB::table('users')->where('email', 'admin@plantix.com')->value('id') ?? 1;
        $vendors  = DB::table('vendors')->select('id')->pluck('id')->toArray();
        $customers = DB::table('users')->where('role', 'user')->limit(20)->pluck('id')->toArray();

        // ── Wallet Transactions ───────────────────────────────────────────────
        $balance = [];
        foreach ($customers as $userId) {
            $balance[$userId] = 0;
        }

        // Seed credits
        foreach ($customers as $idx => $userId) {
            $amount          = [500, 1000, 2000, 300, 750][$idx % 5];
            $balance[$userId] += $amount;
            DB::table('wallet_transactions')->insert([
                'user_id'     => $userId,
                'order_id'    => null,
                'type'        => 'credit',
                'amount'      => $amount,
                'balance'     => $balance[$userId],
                'description' => 'Welcome bonus credited to your wallet.',
                'created_at'  => $now->copy()->subDays(rand(30, 200)),
                'updated_at'  => $now,
            ]);
        }

        // Seed debits from delivered orders
        $deliveredOrders = DB::table('orders')
            ->where('payment_method', 'wallet')
            ->whereIn('status', ['delivered'])
            ->select('id', 'user_id', 'total')
            ->limit(10)
            ->get();

        foreach ($deliveredOrders as $order) {
            if (! isset($balance[$order->user_id])) {
                $balance[$order->user_id] = 0;
            }
            $debit = min($order->total, $balance[$order->user_id]);
            if ($debit <= 0) {
                continue;
            }
            $balance[$order->user_id] -= $debit;
            DB::table('wallet_transactions')->insert([
                'user_id'     => $order->user_id,
                'order_id'    => $order->id,
                'type'        => 'debit',
                'amount'      => $debit,
                'balance'     => max(0, $balance[$order->user_id]),
                'description' => 'Order #' . $order->id . ' payment deducted.',
                'created_at'  => $now->copy()->subDays(rand(1, 60)),
                'updated_at'  => $now,
            ]);
        }

        // ── Vendor Payouts ────────────────────────────────────────────────────
        $methods = ['stripe', 'bank_transfer'];
        foreach ($vendors as $idx => $vendorId) {
            // 2 payouts each
            foreach ([1, 2] as $n) {
                $amount = rand(5000, 80000);
                $paid   = $n === 1;
                DB::table('payouts')->insert([
                    'vendor_id'      => $vendorId,
                    'admin_id'       => $adminId,
                    'amount'         => $amount,
                    'method'         => $methods[$idx % count($methods)],
                    'payment_status' => $paid ? 'success' : 'pending',
                    'transaction_ref'=> $paid ? 'TXN' . strtoupper(substr(md5(uniqid()), 0, 10)) : null,
                    'notes'          => $paid ? 'Monthly settlement processed.' : 'Pending settlement.',
                    'created_at'     => $now->copy()->subDays(rand(10, 90)),
                    'updated_at'     => $now,
                ]);
            }
        }

        // ── Payout Requests ───────────────────────────────────────────────────
        foreach ($vendors as $idx => $vendorId) {
            DB::table('payout_requests')->insert([
                'vendor_id'   => $vendorId,
                'amount'      => rand(10000, 50000),
                'method'      => $methods[$idx % count($methods)],
                'status'      => ['pending', 'approved', 'rejected'][$idx % 3],
                'admin_note'  => 'Reviewed on schedule.',
                'reviewed_by' => $adminId,
                'reviewed_at' => $now->copy()->subDays(rand(1, 30)),
                'created_at'  => $now->copy()->subDays(rand(5, 40)),
                'updated_at'  => $now,
            ]);
        }
    }
}
