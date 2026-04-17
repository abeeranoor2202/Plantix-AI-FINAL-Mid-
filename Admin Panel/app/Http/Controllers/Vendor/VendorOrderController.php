<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorOrderDisputeResponseRequest;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\User;
use App\Notifications\Order\OrderDisputeResponseNotification;
use App\Services\Shared\CartCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
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
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
            'dispute_status' => ['nullable', 'string', 'max:50'],
            'min_total' => ['nullable', 'numeric', 'min:0'],
            'max_total' => ['nullable', 'numeric', 'min:0'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = Order::with(['user', 'items'])
                      ->withCount('items as order_items_count')
                      ->forVendor($this->vendorId())
                      ->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($orderQuery) use ($term): void {
                $orderQuery->where('order_number', 'like', '%' . $term . '%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%')
                    );
            });
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

    public function respondDispute(VendorOrderDisputeResponseRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();

        try {
            [$order, $dispute] = DB::transaction(function () use ($id, $validated) {
                $order = Order::forVendor($this->vendorId())
                    ->lockForUpdate()
                    ->findOrFail($id);

                $dispute = OrderDispute::where('order_id', $order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! in_array($dispute->status, [Order::DISPUTE_PENDING, Order::DISPUTE_ESCALATED], true)) {
                    throw new \DomainException('This dispute is no longer open for vendor response.');
                }

                $dispute->update([
                    'vendor_response' => $validated['response'],
                    'status' => Order::DISPUTE_VENDOR_RESPONDED,
                    'responded_at' => now(),
                ]);

                $order->update([
                    'dispute_status' => Order::DISPUTE_VENDOR_RESPONDED,
                    'vendor_dispute_response' => $validated['response'],
                ]);

                return [$order, $dispute];
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['response' => $e->getMessage()]);
        }

        if ($order->user) {
            $order->user->notify(new OrderDisputeResponseNotification($order, $validated['response'], route('order.details', $order->id)));
        }

        $adminRecipients = User::where('role', 'admin')->get();
        if ($adminRecipients->isNotEmpty()) {
            Notification::send($adminRecipients, new OrderDisputeResponseNotification($order, $validated['response'], route('admin.orders.show', $order->id)));
        }

        return back()->with('success', 'Dispute response submitted.');
    }
}
