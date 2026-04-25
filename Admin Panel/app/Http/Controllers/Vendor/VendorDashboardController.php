<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PlatformActivity;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\Vendor;
use Illuminate\View\View;

class VendorDashboardController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user   = auth('vendor')->user();
        $vendor = $user->vendor;

        if (! $vendor) {
            abort(403, 'No vendor profile found. Contact admin.');
        }

        $stats = [
            'total_orders'    => Order::forVendor($vendor->id)->count(),
            'pending_orders'  => Order::forVendor($vendor->id)->where('status', 'pending')->count(),
            'total_products'  => Product::where('vendor_id', $vendor->id)->count(),
            'low_stock'       => Product::where('vendor_id', $vendor->id)
                                        ->where('track_stock', true)
                                        ->where('stock_quantity', '<=', 5)
                                        ->count(),
            'today_revenue'   => Order::forVendor($vendor->id)
                                      ->whereDate('created_at', today())
                                      ->where('payment_status', 'paid')
                                      ->sum('total'),
            'month_revenue'   => Order::forVendor($vendor->id)
                                      ->whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->where('payment_status', 'paid')
                                      ->sum('total'),
        ];

        $recentOrders = Order::forVendor($vendor->id)
                             ->with(['user', 'items'])
                             ->latest()
                             ->limit(10)
                             ->get();

        $unifiedSummary = [

            ['label' => 'Total Orders', 'value' => $stats['total_orders'] ?? 0, 'icon' => 'fas fa-receipt'],
            ['label' => 'Total Products', 'value' => $stats['total_products'] ?? 0, 'icon' => 'fas fa-boxes'],
            ['label' => 'Month Revenue', 'value' => number_format((float) ($stats['month_revenue'] ?? 0), 2), 'icon' => 'fas fa-wallet'],
        ];

        $unifiedRecentActivity = PlatformActivity::with('actor')
            ->latest('created_at')
            ->where(function ($q) use ($user, $vendor) {
                $q->where('actor_user_id', $user->id)
                  ->orWhere('context->vendor_id', $vendor->id);
            })
            ->limit(8)
            ->get()
            ->map(fn ($entry) => [
                'time' => $entry->created_at?->format('d M, h:i A'),
                'title' => str($entry->action)->replace('.', ' ')->title()->toString(),
                'meta' => ($entry->actor?->name ?? ($entry->actor_role ?? 'system')) . ' • ' . ($entry->entity_type ?? 'n/a'),
            ])
            ->values()
            ->all();

        $unifiedPendingActions = [
            ['label' => 'Pending orders', 'count' => (int) ($stats['pending_orders'] ?? 0), 'href' => route('vendor.orders.index')],
            ['label' => 'Low stock items', 'count' => (int) ($stats['low_stock'] ?? 0), 'href' => route('vendor.inventory.index')],
            ['label' => 'Open returns', 'count' => ReturnRequest::whereHas('order', fn ($q) => $q->where('vendor_id', $vendor->id))->whereIn('status', ['pending', 'approved'])->count(), 'href' => route('vendor.returns.index')],
            ['label' => 'Published products', 'count' => Product::where('vendor_id', $vendor->id)->where('is_active', true)->count(), 'href' => route('vendor.products.index')],
        ];

        return view('vendor.dashboard', compact('vendor', 'stats', 'recentOrders', 'unifiedSummary', 'unifiedRecentActivity', 'unifiedPendingActions'));
    }
}
