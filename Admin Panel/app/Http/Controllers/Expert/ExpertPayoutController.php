<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\Payout;
use App\Models\StripeAccount;
use App\Services\Shared\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpertPayoutController extends Controller
{
    public function __construct(private readonly StripeService $stripe)
    {
    }

    public function index(): View
    {
        $user = auth('expert')->user();
        $expert = $user->expert;

        $stripeAccount = StripeAccount::where('user_id', $user->id)->first();
        $payouts = Payout::where('expert_id', $expert?->id)->latest()->paginate(12);

        $totals = [
            'gross' => (float) Payout::where('expert_id', $expert?->id)->where('status', 'paid')->sum('amount'),
            'commission' => (float) Payout::where('expert_id', $expert?->id)->where('status', 'paid')->sum('commission'),
            'net' => (float) Payout::where('expert_id', $expert?->id)->where('status', 'paid')->sum('net_amount'),
        ];

        return view('expert.payouts.index', compact('expert', 'stripeAccount', 'payouts', 'totals'));
    }

    public function connect(): RedirectResponse
    {
        $user = auth('expert')->user();
        $expert = $user->expert;

        if (! $expert instanceof Expert) {
            return back()->withErrors(['payouts' => 'Expert profile not found.']);
        }

        $accountRow = StripeAccount::firstOrCreate(
            ['user_id' => $user->id],
            [
                'accountable_type' => Expert::class,
                'accountable_id' => $expert->id,
                'onboarding_status' => 'pending',
                'email' => $user->email,
                'country' => config('services.stripe.country', 'PK'),
            ]
        );

        if (empty($accountRow->stripe_account_id)) {
            $account = $this->stripe->createConnectAccount($user, 'express', [
                'expert_id' => (string) $expert->id,
            ]);

            $accountRow->update([
                'stripe_account_id' => $account->id,
                'charges_enabled' => (bool) $account->charges_enabled,
                'payouts_enabled' => (bool) $account->payouts_enabled,
                'details_submitted' => (bool) $account->details_submitted,
                'onboarding_status' => ((bool) $account->details_submitted && (bool) $account->payouts_enabled) ? 'completed' : 'pending',
                'last_onboarded_at' => now(),
            ]);

            $expert->update([
                'stripe_account_id' => $account->id,
                'stripe_account_status' => ((bool) $account->details_submitted && (bool) $account->payouts_enabled) ? 'completed' : 'pending',
            ]);
        }

        $link = $this->stripe->createConnectAccountLink(
            $accountRow->stripe_account_id,
            route('expert.payouts.connect', [], true),
            route('expert.payouts.return', [], true)
        );

        return redirect()->away($link->url);
    }

    public function connectReturn(): RedirectResponse
    {
        $user = auth('expert')->user();
        $expert = $user->expert;

        $accountRow = StripeAccount::where('user_id', $user->id)->first();
        if (! $accountRow || empty($accountRow->stripe_account_id)) {
            return redirect()->route('expert.payouts.index')->withErrors(['payouts' => 'No Stripe account found.']);
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

        if ($expert) {
            $expert->update([
                'stripe_account_id' => $account->id,
                'stripe_account_status' => $status,
            ]);
        }

        return redirect()->route('expert.payouts.index')->with('success', 'Stripe account status updated.');
    }
}