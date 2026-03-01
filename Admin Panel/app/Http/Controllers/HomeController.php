<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Payout;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Admin dashboard — protected by EnsureAdminGuard route middleware.
     * All data is queried here and passed to the view — no API calls needed.
     */
    public function index()
    {
        // ── Currency settings ─────────────────────────────────────────────────
        $currencySymbol = '₨';
        $currencyAtRight = false;
        $decimalDigits = 0;
        $placeholderImage = asset('assets/img/placeholder.png');

        try {
            $cur = Setting::where('key', 'default_currency')->first();
            if ($cur) {
                $data = is_string($cur->value) ? json_decode($cur->value, true) : (array) $cur->value;
                $currencySymbol  = $data['symbol']        ?? $currencySymbol;
                $currencyAtRight = (bool)($data['symbolAtRight'] ?? false);
                $decimalDigits   = (int)($data['decimal_degits'] ?? 0);
            }
            $ph = Setting::where('key', 'placeholder_image')->first();
            if ($ph) {
                $placeholderImage = $ph->value ?? $placeholderImage;
            }
        } catch (\Throwable) {}

        // ── Summary counts ────────────────────────────────────────────────────
        $totalOrders    = Order::count();
        $totalVendors   = Vendor::count();
        $totalProducts  = Product::count();
        $totalCustomers = User::where('role', 'user')->count();

        // ── Order status breakdown ────────────────────────────────────────────
        $statusCounts = Order::select('status', DB::raw('count(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $ordersPlaced    = $statusCounts->get('pending',    0);
        $ordersConfirmed = $statusCounts->get('confirmed',  0);
        $ordersShipped   = $statusCounts->get('shipped',    0);
        $ordersCompleted = $statusCounts->get('completed',  0);
        $ordersCanceled  = $statusCounts->get('cancelled',  0);
        $ordersFailed    = $statusCounts->get('payment_failed', 0);
        $ordersPending   = $statusCounts->get('processing', 0);

        // ── Earnings ─────────────────────────────────────────────────────────
        $totalEarnings   = Order::whereNotIn('status', ['cancelled', 'refunded', 'payment_failed'])
                                ->sum('total');

        // commission_rate lives on vendors, so join to compute it
        $adminCommission = Order::join('vendors', 'orders.vendor_id', '=', 'vendors.id')
                                ->whereNotIn('orders.status', ['cancelled', 'refunded', 'payment_failed'])
                                ->whereNull('orders.deleted_at')
                                ->sum(DB::raw('orders.total * COALESCE(vendors.commission_rate, 0) / 100'));

        // Monthly sales for chart (current year)
        $monthlyData = array_fill(0, 12, 0);
        $monthly = Order::whereYear('created_at', now()->year)
            ->whereNotIn('status', ['cancelled', 'refunded', 'payment_failed'])
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total) as total'))
            ->groupBy('month')
            ->pluck('total', 'month');
        foreach ($monthly as $month => $total) {
            $monthlyData[$month - 1] = round((float) $total, $decimalDigits);
        }

        // ── Top vendors ───────────────────────────────────────────────────────
        $topVendors = Vendor::withCount('reviews')
            ->withSum('reviews', 'rating')
            ->orderByDesc('rating')
            ->limit(5)
            ->get()
            ->map(fn ($v) => [
                'id'            => $v->id,
                'title'         => $v->title ?? $v->business_name ?? 'Vendor',
                'photo'         => $v->image ? asset('storage/' . $v->image) : null,
                'reviews_count' => $v->reviews_count,
                'reviews_sum'   => $v->reviews_sum_rating ?? 0,
            ]);

        // ── Recent orders ─────────────────────────────────────────────────────
        $recentOrders = Order::with(['vendor', 'items.product'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($o) => [
                'id'        => $o->id,
                'vendor_id' => $o->vendor_id,
                'vendor'    => ['title' => $o->vendor?->title ?? $o->vendor?->business_name ?? '—'],
                'total'     => $o->total,
                'status'    => $o->status,
                'products'  => $o->items->map(fn ($i) => [
                    'quantity'     => $i->quantity,
                    'price'        => $i->price,
                    'extras_price' => 0,
                ])->toArray(),
                'discount'        => $o->discount_amount ?? 0,
                'delivery_charge' => $o->delivery_fee ?? 0,
                'tax_amount'      => $o->tax_amount ?? 0,
                'tip_amount'      => 0,
                'admin_commission'=> 0,
            ]);

        // ── Recent payouts ────────────────────────────────────────────────────
        $recentPayouts = [];
        try {
            $recentPayouts = \App\Models\Payout::with('vendor')
                ->latest('paid_date')
                ->limit(10)
                ->get()
                ->map(fn ($p) => [
                    'id'        => $p->id,
                    'vendor_id' => $p->vendor_id,
                    'vendor'    => ['title' => $p->vendor?->title ?? $p->vendor?->business_name ?? '—'],
                    'amount'    => $p->amount,
                    'paid_date' => $p->paid_date,
                    'note'      => $p->note ?? '',
                ])->toArray();
        } catch (\Throwable) {}

        // ── Format currency helper (used in view) ─────────────────────────────
        $fmt = fn ($val) => $currencyAtRight
            ? number_format((float)$val, $decimalDigits) . $currencySymbol
            : $currencySymbol . number_format((float)$val, $decimalDigits);

        return view('admin.home', compact(
            'currencySymbol', 'currencyAtRight', 'decimalDigits', 'placeholderImage',
            'totalOrders', 'totalVendors', 'totalProducts', 'totalCustomers',
            'ordersPlaced', 'ordersConfirmed', 'ordersShipped', 'ordersCompleted',
            'ordersCanceled', 'ordersFailed', 'ordersPending',
            'totalEarnings', 'adminCommission', 'monthlyData',
            'topVendors', 'recentOrders', 'recentPayouts', 'fmt',
        ));
    }

    public function welcome()
    {
        return view('customer.welcome');
    }

    public function dashboard()
    {
        return $this->index();
    }

    public function users()
    {
        return view('admin.settings.users.index');
    }
}

