<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ReturnReason;
use App\Services\Shared\ReturnRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function __construct(
        private readonly ReturnRefundService $returnService,
    ) {}

    public function index(): View
    {
        $user   = auth('web')->user();
        $orders = Order::with(['vendor', 'items.product'])
                       ->forCustomer($user->id)
                       ->latest()
                       ->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function show(int $id): View
    {
        $user  = auth('web')->user();
        $order = Order::with(['vendor', 'items.product', 'statusHistory', 'returnRequest', 'refund'])
                      ->forCustomer($user->id)
                      ->findOrFail($id);
        $canReturn = $this->returnService->orderIsReturnable($order);

        return view('customer.order-details', compact('order', 'canReturn'));
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
            'return_reason_id' => 'nullable|exists:return_reasons,id',
            'description'      => 'required|string|max:1000',
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
}

