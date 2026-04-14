<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payout;

class AdminPayoutsController extends Controller
{
    /**
     * Get recent payouts
     */
    public function recent(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 10);
            $payouts = Payout::query()
                ->whereIn('status', ['paid', 'pending', 'failed'])
                ->orderByDesc('paid_at')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->with(['vendor', 'expert', 'user'])
                ->get()
                ->map(function ($payout) {
                    $recipientName = $payout->vendor?->title
                        ?? $payout->expert?->user?->name
                        ?? $payout->user?->name
                        ?? 'Unknown';

                    return [
                        'id' => $payout->id,
                        'vendor_id' => $payout->vendor_id,
                        'expert_id' => $payout->expert_id,
                        'recipient_name' => $recipientName,
                        'payment_type' => $payout->payment_type,
                        'amount' => (float) $payout->amount,
                        'commission' => (float) $payout->commission,
                        'net_amount' => (float) $payout->net_amount,
                        'method' => $payout->method ?? 'stripe_connect',
                        'status' => $payout->status,
                        'paid_date' => $payout->paid_at ?? $payout->created_at,
                        'transaction_id' => $payout->stripe_transfer_id ?? $payout->id,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $payouts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payouts: ' . $e->getMessage()
            ], 500);
        }
    }
}
