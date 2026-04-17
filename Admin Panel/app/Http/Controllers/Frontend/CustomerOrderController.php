<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\OrderStatusHistory;
use App\Models\ReturnReason;
use App\Notifications\Order\OrderDisputeSubmittedNotification;
use App\Services\Shared\ReturnRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function __construct(
        private readonly ReturnRefundService $returnService,
    ) {}

    public function index(): View
    {
        $user   = auth('web')->user();
        $ordersQuery = Order::with(['vendor', 'items.product'])
                       ->forCustomer($user->id)
                       ->latest();

        if (request()->filled('dispute_status')) {
            $ordersQuery->where('dispute_status', request('dispute_status'));
        }

        $orders = $ordersQuery->paginate(10)->withQueryString();

        return view('customer.orders', compact('orders'));
    }

    public function show(int $id): View
    {
        $user  = auth('web')->user();
        $order = Order::with(['vendor', 'items.product', 'statusHistory', 'returnRequest', 'refund', 'dispute'])
                      ->forCustomer($user->id)
                      ->findOrFail($id);
        $canReturn = $this->returnService->orderIsReturnable($order);
        $returnReasons = ReturnReason::active()
            ->forVendorOrGlobal($order->vendor_id)
            ->orderBy('title')
            ->get();

        return view('customer.order-details', compact('order', 'canReturn', 'returnReasons'));
    }

    public function success(int $id): View
    {
        $user  = auth('web')->user();
        $order = Order::with(['vendor', 'items.product'])
                      ->forCustomer($user->id)
                      ->findOrFail($id);

        return view('customer.order-success', compact('order'));
    }

    public function requestReturn(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason_id' => 'required|exists:return_reasons,id',
            'notes'     => 'required|string|max:1000',
            'items'     => 'required|array',
            'items.*'   => 'nullable|integer|min:0',
        ]);

        $user  = auth('web')->user();
        $order = Order::forCustomer($user->id)
                      ->where('status', 'delivered')
                      ->findOrFail($id);

        abort_unless($this->returnService->orderIsReturnable($order), 403, 'This product is not returnable');

        $this->returnService->requestReturn($user, $order, $request->validated());

        return back()->with('success', 'Return request submitted. Admin will review it shortly.');
    }

    /**
     * Cancel a pending or confirmed order.
     * Route: POST /orders/{id}/cancel
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user  = auth('web')->user();
        $order = Order::forCustomer($user->id)
                      ->whereIn('status', ['pending', 'confirmed'])
                      ->findOrFail($id);

        $order->update(['status' => 'cancelled']);

        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'status'     => 'cancelled',
            'changed_by' => $user->id,
            'notes'      => $request->reason ?? 'Cancelled by customer.',
        ]);

        return back()->with('success', 'Your order has been cancelled successfully.');
    }

    public function dispute(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $user = auth('web')->user();
        $order = Order::forCustomer($user->id)
                      ->whereNotIn('status', ['cancelled', 'rejected', 'refunded'])
                      ->findOrFail($id);

        OrderDispute::updateOrCreate(
            ['order_id' => $order->id],
            [
                'user_id' => $user->id,
                'vendor_id' => $order->vendor_id,
                'status' => 'pending',
                'reason' => $request->reason,
                'escalated_at' => now(),
            ]
        );

        if ($order->vendor?->author) {
            $order->vendor->author->notify(new OrderDisputeSubmittedNotification($order, $request->reason, route('vendor.orders.show', $order->id)));
        }

        $adminRecipients = \App\Models\User::where('role', 'admin')->get();
        if ($adminRecipients->isNotEmpty()) {
            Notification::send($adminRecipients, new OrderDisputeSubmittedNotification($order, $request->reason, route('admin.orders.show', $order->id)));
        }

        $order->update([
            'dispute_status' => 'pending',
            'disputed_at' => now(),
            'dispute_reason' => $request->reason,
        ]);

        return back()->with('success', 'Your dispute has been submitted for review.');
    }
}

