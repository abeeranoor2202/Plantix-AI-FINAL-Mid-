<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\User;
use App\Notifications\Order\OrderDisputeResolvedNotification;
use App\Services\Shared\CartCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function __construct(
        private readonly CartCheckoutService $checkout,
    ) {}

    public function index(Request $request): View
    {
        $query = Order::with(['user', 'vendor', 'items'])
                      ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$request->search}%")
                                                     ->orWhere('email', 'like', "%{$request->search}%"));
            });
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('dispute_status')) {
            $query->where('dispute_status', $request->dispute_status);
        }

        $orders = $query->paginate(20)->withQueryString();

        $statuses = ['draft','pending_payment','payment_failed','pending','confirmed','processing','shipped','delivered','completed','cancelled','rejected','return_requested','returned','refunded'];

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(int $id): View
    {
        $order = Order::with([
            'user', 'vendor', 'driver', 'coupon',
            'items.product', 'statusHistory.changedBy', 'returnRequest.reason', 'refund', 'dispute',
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:draft,pending_payment,payment_failed,pending,confirmed,processing,shipped,delivered,completed,cancelled,rejected,return_requested,returned,refunded',
            'notes'  => 'nullable|string|max:500',
        ]);

        $order = Order::findOrFail($id);
        /** @var \App\Models\User $admin */
        $admin = auth('admin')->user();

        $this->checkout->updateStatus($order, $request->status, $request->notes, $admin);

        return back()->with('success', "Order #{$order->order_number} status updated to {$request->status}.");
    }

    public function assignDriver(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
        ]);

        $order = Order::findOrFail($id);
        $driver = User::where('id', $request->driver_id)->where('role', 'driver')->firstOrFail();

        $order->update([
            'driver_id' => $driver->id,
            'status'    => 'driver_assigned',
        ]);

        return back()->with('success', "Driver {$driver->name} assigned.");
    }

    public function resolveDispute(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'resolution' => 'required|string|max:1000',
            'status' => 'required|in:resolved,rejected,refunded',
        ]);

        $order = Order::findOrFail($id);
        $dispute = OrderDispute::where('order_id', $order->id)->firstOrFail();

        /** @var \App\Models\User $admin */
        $admin = auth('admin')->user();

        $dispute->update([
            'status' => $request->status,
            'admin_notes' => $request->resolution,
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
        ]);

        $notifyCustomer = $order->user;
        $notifyVendor = $order->vendor?->author;

        if ($notifyCustomer) {
            $notifyCustomer->notify(new OrderDisputeResolvedNotification($order, $request->resolution, $request->status, route('order.details', $order->id)));
        }

        if ($notifyVendor) {
            $notifyVendor->notify(new OrderDisputeResolvedNotification($order, $request->resolution, $request->status, route('vendor.orders.show', $order->id)));
        }

        $adminRecipients = User::where('role', 'admin')->whereKeyNot($admin->id)->get();
        if ($adminRecipients->isNotEmpty()) {
            Notification::send($adminRecipients, new OrderDisputeResolvedNotification($order, $request->resolution, $request->status, route('admin.orders.show', $order->id)));
        }

        $order->update([
            'dispute_status' => $request->status,
            'dispute_resolved_by' => $admin->id,
            'dispute_resolved_at' => now(),
            'dispute_admin_notes' => $request->resolution,
        ]);

        return back()->with('success', 'Order dispute resolved successfully.');
    }
}

