<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\User;
use App\Notifications\Order\OrderDisputeResponseNotification;
use App\Services\Shared\CartCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Throwable;
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
                      ->withCount('items as order_items_count')
                      ->forVendor($this->vendorId())
                      ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('order_number', 'like', "%{$request->search}%");
        }

        if ($request->filled('dispute_status')) {
            $query->where('dispute_status', $request->dispute_status);
        }

        $orders   = $query->paginate(20)->withQueryString();
        $statuses = ['pending','confirmed','processing','shipped','delivered','completed','cancelled','rejected','return_requested','returned'];

        return view('vendor.orders.index', compact('orders', 'statuses'));
    }

    public function show(int $id): View
    {
        $order = Order::with([
                        'user',
                        'items.product',
                        'returnRequest',
                                                'dispute',
                        'statusHistory' => fn ($query) => $query->with('changedBy')->latest(),
                    ])
                      ->forVendor($this->vendorId())
                      ->findOrFail($id);

        return view('vendor.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,completed,cancelled,rejected,return_requested,returned',
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

    public function destroy(int $id): RedirectResponse
    {
        try {
            $order = Order::forVendor($this->vendorId())->findOrFail($id);
            $orderNumber = $order->order_number;

            $order->delete();

            return redirect()
                ->route('vendor.orders.index')
                ->with('success', "Order #{$orderNumber} deleted successfully.");
        } catch (Throwable) {
            return redirect()
                ->route('vendor.orders.index')
                ->with('error', 'Unable to delete the order right now. Please try again.');
        }
    }

    public function respondDispute(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'response' => 'required|string|max:1000',
        ]);

        $order = Order::forVendor($this->vendorId())->findOrFail($id);
        $dispute = OrderDispute::where('order_id', $order->id)->firstOrFail();

        $dispute->update([
            'vendor_response' => $request->response,
            'status' => 'vendor_responded',
            'responded_at' => now(),
        ]);

        if ($order->user) {
            $order->user->notify(new OrderDisputeResponseNotification($order, $request->response, route('order.details', $order->id)));
        }

        $adminRecipients = User::where('role', 'admin')->get();
        if ($adminRecipients->isNotEmpty()) {
            Notification::send($adminRecipients, new OrderDisputeResponseNotification($order, $request->response, route('admin.orders.show', $order->id)));
        }

        $order->update([
            'dispute_status' => 'vendor_responded',
            'vendor_dispute_response' => $request->response,
        ]);

        return back()->with('success', 'Dispute response submitted.');
    }
}
