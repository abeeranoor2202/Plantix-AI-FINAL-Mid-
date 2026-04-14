<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\StripeAccount;
use App\Models\Vendor;
use App\Services\Shared\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorPayoutController extends Controller
{
    public function __construct(private readonly StripeService $stripe)
    {
    }

    public function index(): View
    {
        $user = auth('vendor')->user();
        $vendor = $user->vendor;

        $stripeAccount = StripeAccount::where('user_id', $user->id)->first();
        $payouts = Payout::where('vendor_id', $vendor?->id)->latest()->paginate(12);

        $totals = [
            'gross' => (float) Payout::where('vendor_id', $vendor?->id)->where('status', 'paid')->sum('amount'),
            'commission' => (float) Payout::where('vendor_id', $vendor?->id)->where('status', 'paid')->sum('commission'),
            'net' => (float) Payout::where('vendor_id', $vendor?->id)->where('status', 'paid')->sum('net_amount'),
        ];

        return view('vendor.payouts.index', compact('vendor', 'stripeAccount', 'payouts', 'totals'));
    }

    public function connect(): RedirectResponse
    {
        $user = auth('vendor')->user();
        $vendor = $user->vendor;

        if (! $vendor instanceof Vendor) {
            return back()->withErrors(['payouts' => 'Vendor profile not found.']);
        }

        $accountRow = StripeAccount::firstOrCreate(
            ['user_id' => $user->id],
            [
                'accountable_type' => Vendor::class,
                'accountable_id' => $vendor->id,
                'onboarding_status' => 'pending',
                'email' => $user->email,
                'country' => config('services.stripe.country', 'PK'),
            ]
        );

        if (empty($accountRow->stripe_account_id)) {
            $account = $this->stripe->createConnectAccount($user, 'express', [
                'vendor_id' => (string) $vendor->id,
            ]);

            $accountRow->update([
                'stripe_account_id' => $account->id,
                'charges_enabled' => (bool) $account->charges_enabled,
                'payouts_enabled' => (bool) $account->payouts_enabled,
                'details_submitted' => (bool) $account->details_submitted,
                'onboarding_status' => ((bool) $account->details_submitted && (bool) $account->payouts_enabled) ? 'completed' : 'pending',
                'last_onboarded_at' => now(),
            ]);

            $vendor->update([
                'stripe_account_id' => $account->id,
            ]);
        }

        $link = $this->stripe->createConnectAccountLink(
            $accountRow->stripe_account_id,
            route('vendor.payouts.connect', [], true),
            route('vendor.payouts.return', [], true)
        );

        return redirect()->away($link->url);
    }

    public function connectReturn(): RedirectResponse
    {
        $user = auth('vendor')->user();
        $vendor = $user->vendor;

        $accountRow = StripeAccount::where('user_id', $user->id)->first();
        if (! $accountRow || empty($accountRow->stripe_account_id)) {
            return redirect()->route('vendor.payouts.index')->withErrors(['payouts' => 'No Stripe account found.']);
        }

        $account = $this->stripe->retrieveConnectAccount($accountRow->stripe_account_id);
        $status = ((bool) $account->details_submitted && (bool) $account->payouts_enabled) ? 'completed' : 'pending';

        $accountRow->update([
            'charges_enabled' => (bool) $account->charges_enabled,
            'payouts_enabled' => (bool) $account->payouts_enabled,
            'details_submitted' => (bool) $account->details_submitted,
            'onboarding_status' => $status,
            'last_onboarded_at' => now(),
        ]);

        if ($vendor) {
            $vendor->update([
                'stripe_account_id' => $account->id,
            ]);
        }

        return redirect()->route('vendor.payouts.index')->with('success', 'Stripe account status updated.');
    }
}