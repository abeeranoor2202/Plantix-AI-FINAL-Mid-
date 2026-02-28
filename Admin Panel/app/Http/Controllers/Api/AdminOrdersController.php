<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOrdersController extends Controller
{
    /**
     * Paginated, filterable list of all orders.
     */
    public function index(Request $request)
    {
        try {
            $query = Order::with(['user', 'vendor', 'items']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }
            if ($request->filled('search')) {
                $query->where('order_number', 'like', '%' . $request->search . '%');
            }

            $orders = $query->orderByDesc('created_at')->paginate($request->get('per_page', 15));

            return response()->json(['success' => true, 'data' => $orders]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a single order with full detail.
     */
    public function show($id)
    {
        try {
            $order = Order::with(['user', 'vendor', 'items.product', 'payments', 'statusHistories'])->find($id);
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }
            return response()->json(['success' => true, 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the status of an order.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);

        try {
            $order = Order::find($id);
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            if (! $order->canTransitionTo($request->status)) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot transition order from '{$order->status}' to '{$request->status}'.",
                ], 422);
            }

            $old = $order->status;
            $order->status = $request->status;

            if ($request->status === 'delivered') {
                $order->delivered_at = now();
            }

            $order->save();

            // Record status history
            if (class_exists(OrderStatusHistory::class)) {
                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'old_status' => $old,
                    'new_status' => $request->status,
                    'changed_by' => Auth::id(),
                    'note'       => $request->note,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Order status updated.', 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel an order (admin force-cancel).
     */
    public function cancel(Request $request, $id)
    {
        $request->merge(['status' => 'cancelled']);
        return $this->updateStatus($request, $id);
    }

    /**
     * Get recent orders
     */
    public function recent(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $orders = Order::where('status', '!=', 'Order Completed')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->with(['vendor', 'items'])
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'vendor_id' => $order->vendor_id,
                        'vendor' => $order->vendor ? [
                            'id' => $order->vendor->id,
                            'title' => $order->vendor->title ?? $order->vendor->name,
                        ] : null,
                        'total_amount' => $order->total_amount ?? 0,
                        'status' => $order->status,
                        'products' => $order->items ?? [],
                        'admin_commission' => $order->admin_commission ?? 0,
                        'discount' => $order->discount ?? 0,
                        'tax_amount' => $order->tax_amount ?? 0,
                        'delivery_charge' => $order->delivery_charge ?? 0,
                        'tip_amount' => $order->tip_amount ?? 0,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching orders: ' . $e->getMessage()
            ], 500);
        }
    }
}
