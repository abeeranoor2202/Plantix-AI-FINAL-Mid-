<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Shared\CartCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorOrderController extends Controller
{
    public function __construct(
        private readonly CartCheckoutService $checkout,
    ) {}

    private function vendorId(): int
    {
        return auth('vendor')->user()->vendor->id;
    }

    public function index(Request $request): View
    {
        $query = Order::with(['user', 'items'])
                      ->forVendor($this->vendorId())
                      ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('order_number', 'like', "%{$request->search}%");
        }

        $orders   = $query->paginate(20)->withQueryString();
        $statuses = ['pending','accepted','preparing','ready','driver_assigned','picked_up','delivered','rejected','cancelled'];

        return view('vendor.orders.index', compact('orders', 'statuses'));
    }

    public function show(int $id): View
    {
        $order = Order::with(['user', 'items.product', 'statusHistory', 'returnRequest'])
                      ->forVendor($this->vendorId())
                      ->findOrFail($id);

        return view('vendor.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,rejected,cancelled',
            'notes'  => 'nullable|string|max:500',
        ]);

        $order = Order::forVendor($this->vendorId())->findOrFail($id);

        if (! $order->canTransitionTo($request->status)) {
            return back()->withErrors([
                'status' => "Cannot transition order from '{$order->status}' to '{$request->status}'.",
            ]);
        }

        /** @var \App\Models\User $vendor */
        $vendor = auth('vendor')->user();

        $this->checkout->updateStatus($order, $request->status, $request->notes, $vendor);

        return back()->with('success', 'Order status updated.');
    }
}
