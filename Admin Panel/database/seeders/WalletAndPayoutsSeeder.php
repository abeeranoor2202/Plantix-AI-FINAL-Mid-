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
        $customers = DB::table('users')->where('role', 'user')->get();
        $vendors  = DB::table('vendors')->get();
        $orders   = DB::table('orders')->where('payment_status', 'paid')->get();
        $adminId  = DB::table('users')->where('role', 'admin')->value('id');

        // ── Wallet Transactions ──────────────────────────────────
        foreach ($customers as $user) {
            $balance = (float) $user->wallet_amount;

            // 2 credit transactions per customer (top-up / refund)
            for ($i = 0; $i < 2; $i++) {
                $credit  = round(rand(200, 2000) / 10) * 10;
                $balance += $credit;
                DB::table('wallet_transactions')->insert([
                    'user_id'     => $user->id,
                    'order_id'    => null,
                    'type'        => 'credit',
                    'amount'      => $credit,
                    'balance'     => $balance,
                    'description' => 'Wallet top-up via EasyPaisa/JazzCash',
                    'created_at'  => $now->copy()->subDays(rand(1, 45)),
                    'updated_at'  => $now->copy()->subDays(rand(1, 45)),
                ]);
            }

            // 1 debit transaction per customer (order payment)
            if ($balance > 500) {
                $debit   = round(rand(200, (int)min($balance * 0.5, 3000)) / 10) * 10;
                $balance -= $debit;

                $userOrder = $orders->where('user_id', $user->id)->first();

                DB::table('wallet_transactions')->insert([
                    'user_id'     => $user->id,
                    'order_id'    => $userOrder ? $userOrder->id : null,
                    'type'        => 'debit',
                    'amount'      => $debit,
                    'balance'     => $balance,
                    'description' => 'Payment for agri-input order',
                    'created_at'  => $now->copy()->subDays(rand(0, 20)),
                    'updated_at'  => $now->copy()->subDays(rand(0, 20)),
                ]);
            }
        }

        // ── Payouts ──────────────────────────────────────────────
        $payoutMethods = ['bank_transfer', 'easypaisa', 'jazzcash'];
        $payoutStatuses = ['pending', 'success', 'success', 'failed']; // weighted towards success

        foreach ($vendors as $vendor) {
            $numPayouts = rand(1, 3);
            for ($p = 0; $p < $numPayouts; $p++) {
                $status = $payoutStatuses[array_rand($payoutStatuses)];
                DB::table('payouts')->insert([
                    'vendor_id'       => $vendor->id,
                    'admin_id'        => $adminId,
                    'amount'          => round(rand(5000, 50000) / 100) * 100,
                    'method'          => $payoutMethods[array_rand($payoutMethods)],
                    'payment_status'  => $status,
                    'transaction_ref' => $status === 'success' ? 'TXN-' . strtoupper(substr(md5(uniqid()), 0, 10)) : null,
                    'notes'           => $status === 'failed' ? 'Bank account number mismatch – please reverify.' : null,
                    'created_at'      => $now->copy()->subDays(rand(1, 30)),
                    'updated_at'      => $now,
                ]);
            }
        }

        // ── Payout Requests ──────────────────────────────────────
        $prStatuses = ['pending', 'approved', 'rejected'];
        foreach ($vendors as $vendor) {
            $prStatus = $prStatuses[array_rand($prStatuses)];
            DB::table('payout_requests')->insert([
                'vendor_id'    => $vendor->id,
                'amount'       => round(rand(10000, 80000) / 100) * 100,
                'method'       => $payoutMethods[array_rand($payoutMethods)],
                'status'       => $prStatus,
                'admin_note'   => $prStatus === 'rejected' ? 'Insufficient balance or documentation issue.' : null,
                'reviewed_by'  => in_array($prStatus, ['approved', 'rejected']) ? $adminId : null,
                'reviewed_at'  => in_array($prStatus, ['approved', 'rejected']) ? $now->copy()->subDays(rand(0, 5)) : null,
                'created_at'   => $now->copy()->subDays(rand(1, 15)),
                'updated_at'   => $now,
            ]);
        }

        $this->command->info('WalletAndPayoutsSeeder: ' . DB::table('wallet_transactions')->count() . ' wallet transactions, ' . DB::table('payouts')->count() . ' payouts, ' . DB::table('payout_requests')->count() . ' payout requests inserted.');
    }
}
