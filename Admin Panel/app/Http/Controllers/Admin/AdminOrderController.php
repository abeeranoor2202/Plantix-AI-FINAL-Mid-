<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminOrderDisputeResolveRequest;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\User;
use App\Notifications\Order\OrderDisputeResolvedNotification;
use App\Services\Shared\CartCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\OrderStatusHistory;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function __construct(
        private readonly CartCheckoutService $checkout,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
            'vendor_id' => ['nullable', 'integer'],
            'dispute_status' => ['nullable', 'string', 'max:50'],
            'min_total' => ['nullable', 'numeric', 'min:0'],
            'max_total' => ['nullable', 'numeric', 'min:0'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = Order::with(['user', 'vendor', 'items'])
                      ->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', '%' . $term . '%')
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $term . '%')
                                                     ->orWhere('email', 'like', '%' . $term . '%'))
                  ->orWhereHas('vendor', fn ($v) => $v->where('title', 'like', '%' . $term . '%'));
            });
        }

        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', (int) $filters['vendor_id']);
        }

        if (! empty($filters['dispute_status'])) {
            $query->where('dispute_status', $filters['dispute_status']);
        }

        if (! empty($filters['min_total'])) {
            $query->where('total', '>=', (float) $filters['min_total']);
        }

        if (! empty($filters['max_total'])) {
            $query->where('total', '<=', (float) $filters['max_total']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
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

    public function resolveDispute(AdminOrderDisputeResolveRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();

        /** @var \App\Models\User $admin */
        $admin = auth('admin')->user();

        try {
            [$order, $dispute] = DB::transaction(function () use ($id, $validated, $admin) {
                $order = Order::lockForUpdate()->findOrFail($id);
                $dispute = OrderDispute::where('order_id', $order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! in_array($dispute->status, [Order::DISPUTE_PENDING, Order::DISPUTE_VENDOR_RESPONDED, Order::DISPUTE_ESCALATED], true)) {
                    throw new \DomainException('Only active disputes can be resolved.');
                }

                if ($validated['status'] === 'refunded' && ! $order->adminCanForceTo(Order::STATUS_REFUNDED)) {
                    throw new \DomainException('This order cannot be refunded from its current state.');
                }

                $dispute->update([
                    'status' => $validated['status'],
                    'admin_notes' => $validated['resolution'],
                    'resolved_by' => $admin->id,
                    'resolved_at' => now(),
                    'refund_escalated_at' => $validated['status'] === 'refunded' ? now() : null,
                    'refund_reference' => $validated['status'] === 'refunded' ? ($validated['refund_reference'] ?: null) : null,
                ]);

                $order->update([
                    'dispute_status' => $validated['status'],
                    'dispute_resolved_by' => $admin->id,
                    'dispute_resolved_at' => now(),
                    'dispute_admin_notes' => $validated['resolution'],
                    'status' => $validated['status'] === 'refunded' ? Order::STATUS_REFUNDED : $order->status,
                    'payment_status' => $validated['status'] === 'refunded' ? 'pending_refund' : $order->payment_status,
                ]);

                if ($validated['status'] === 'refunded') {
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => Order::STATUS_REFUNDED,
                        'changed_by' => $admin->id,
                        'notes' => trim('Dispute refund escalation: ' . $validated['resolution']),
                    ]);
                }

                return [$order, $dispute];
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $notifyCustomer = $order->user;
        $notifyVendor = $order->vendor?->author;

        if ($notifyCustomer) {
            $notifyCustomer->notify(new OrderDisputeResolvedNotification($order, $validated['resolution'], $validated['status'], route('order.details', $order->id)));
        }

        if ($notifyVendor) {
            $notifyVendor->notify(new OrderDisputeResolvedNotification($order, $validated['resolution'], $validated['status'], route('vendor.orders.show', $order->id)));
        }

        $adminRecipients = User::where('role', 'admin')->whereKeyNot($admin->id)->get();
        if ($adminRecipients->isNotEmpty()) {
            Notification::send($adminRecipients, new OrderDisputeResolvedNotification($order, $validated['resolution'], $validated['status'], route('admin.orders.show', $order->id)));
        }

        return back()->with('success', 'Order dispute resolved successfully.');
    }
}

