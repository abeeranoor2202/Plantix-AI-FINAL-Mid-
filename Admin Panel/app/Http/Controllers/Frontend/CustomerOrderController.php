<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CustomerOrderCancelRequest;
use App\Http\Requests\Frontend\CustomerOrderDisputeEscalateRequest;
use App\Http\Requests\Frontend\CustomerOrderDisputeRequest;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\OrderStatusHistory;
use App\Models\ReturnReason;
use App\Notifications\Order\OrderDisputeSubmittedNotification;
use App\Services\Shared\ReturnRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function __construct(
        private readonly ReturnRefundService $returnService,
    ) {}

    public function index(Request $request): View
    {
        $user   = auth('web')->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'dispute_status' => ['nullable', 'string', 'max:50'],
            'min_total' => ['nullable', 'numeric', 'min:0'],
            'max_total' => ['nullable', 'numeric', 'min:0'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $ordersQuery = Order::with(['vendor', 'items.product'])
                       ->forCustomer($user->id)
                       ->latest();

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $ordersQuery->where(function ($query) use ($term): void {
                $query->where('order_number', 'like', '%' . $term . '%')
                    ->orWhere('id', $term)
                    ->orWhereHas('items.product', fn ($productQuery) => $productQuery->where('name', 'like', '%' . $term . '%'));
            });
        }

        if (! empty($filters['status'])) {
            $ordersQuery->where('status', $filters['status']);
        }

        if (! empty($filters['dispute_status'])) {
            $ordersQuery->where('dispute_status', $filters['dispute_status']);
        }

        if (! empty($filters['min_total'])) {
            $ordersQuery->where('total', '>=', (float) $filters['min_total']);
        }

        if (! empty($filters['max_total'])) {
            $ordersQuery->where('total', '<=', (float) $filters['max_total']);
        }

        if (! empty($filters['date_from'])) {
            $ordersQuery->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $ordersQuery->whereDate('created_at', '<=', $filters['date_to']);
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
    public function cancel(CustomerOrderCancelRequest $request, int $id): RedirectResponse
    {
        $user  = auth('web')->user();
        try {
            DB::transaction(function () use ($user, $id, $request) {
                $order = Order::forCustomer($user->id)
                    ->lockForUpdate()
                    ->findOrFail($id);

                if (! $order->canCancel()) {
                    throw new \DomainException('This order can no longer be cancelled.');
                }

                $order->update(['status' => Order::STATUS_CANCELLED]);

                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => Order::STATUS_CANCELLED,
                    'changed_by' => $user->id,
                    'notes'      => $request->reason ?? 'Cancelled by customer.',
                ]);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Your order has been cancelled successfully.');
    }

    public function dispute(CustomerOrderDisputeRequest $request, int $id): RedirectResponse
    {
        $user = auth('web')->user();
        $validated = $request->validated();

        try {
            $order = DB::transaction(function () use ($user, $id, $validated) {
                $order = Order::forCustomer($user->id)
                    ->lockForUpdate()
                    ->findOrFail($id);

                if (! $order->canOpenDispute()) {
                    throw new \DomainException('This order cannot be disputed at its current stage.');
                }

                if ($order->hasOpenDispute()) {
                    throw new \DomainException('This order already has an active dispute.');
                }

                OrderDispute::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'user_id' => $user->id,
                        'vendor_id' => $order->vendor_id,
                        'status' => Order::DISPUTE_PENDING,
                        'reason' => $validated['reason'],
                        'escalation_reason' => null,
                        'escalated_at' => null,
                        'responded_at' => null,
                        'resolved_at' => null,
                        'admin_notes' => null,
                        'resolved_by' => null,
                        'refund_escalated_at' => null,
                        'refund_reference' => null,
                    ]
                );

                $order->update([
                    'dispute_status' => Order::DISPUTE_PENDING,
                    'disputed_at' => now(),
                    'dispute_reason' => $validated['reason'],
                ]);

                return $order;
            });
        } catch (\DomainException $e) {
            if (str_contains(strtolower($e->getMessage()), 'already has')) {
                return back()->with('info', $e->getMessage());
            }

            return back()->withErrors(['reason' => $e->getMessage()]);
        }

        if ($order->vendor?->author) {
            $order->vendor->author->notify(new OrderDisputeSubmittedNotification($order, $validated['reason'], route('vendor.orders.show', $order->id)));
        }

        $adminRecipients = \App\Models\User::where('role', 'admin')->get();
        if ($adminRecipients->isNotEmpty()) {
            Notification::send($adminRecipients, new OrderDisputeSubmittedNotification($order, $validated['reason'], route('admin.orders.show', $order->id)));
        }

        return back()->with('success', 'Your dispute has been submitted for review.');
    }

    public function escalateDispute(CustomerOrderDisputeEscalateRequest $request, int $id): RedirectResponse
    {
        $user = auth('web')->user();
        $validated = $request->validated();

        try {
            $order = DB::transaction(function () use ($user, $id, $validated) {
                $order = Order::forCustomer($user->id)
                    ->lockForUpdate()
                    ->findOrFail($id);

                $dispute = OrderDispute::where('order_id', $order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($dispute->status !== Order::DISPUTE_VENDOR_RESPONDED) {
                    throw new \DomainException('Only vendor-responded disputes can be escalated.');
                }

                $dispute->update([
                    'status' => Order::DISPUTE_ESCALATED,
                    'escalation_reason' => $validated['escalation_reason'],
                    'escalated_at' => now(),
                ]);

                $order->update([
                    'dispute_status' => Order::DISPUTE_ESCALATED,
                    'dispute_reason' => $validated['escalation_reason'],
                ]);

                return $order;
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $adminRecipients = \App\Models\User::where('role', 'admin')->get();
        if ($adminRecipients->isNotEmpty()) {
            Notification::send(
                $adminRecipients,
                new OrderDisputeSubmittedNotification(
                    $order,
                    'Escalated by customer: ' . $validated['escalation_reason'],
                    route('admin.orders.show', $order->id)
                )
            );
        }

        return back()->with('success', 'Dispute escalated to admin for final review.');
    }
}

