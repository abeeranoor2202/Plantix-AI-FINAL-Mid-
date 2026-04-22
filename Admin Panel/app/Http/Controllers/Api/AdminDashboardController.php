<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Validation\ValidationException;

class AdminDashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        try {
            $totalOrders = Order::count();
            $totalProducts = Product::count();
            $totalCustomers = User::where('role', 'customer')->count();
            $totalVendors = Vendor::count();
            
            $ordersPlaced    = Order::where('status', 'pending')->count();
            $ordersConfirmed = Order::whereIn('status', ['confirmed', 'processing'])->count();
            $ordersShipped   = Order::where('status', 'shipped')->count();
            $ordersCompleted = Order::where('status', 'delivered')->count();
            $ordersCanceled  = Order::whereIn('status', ['cancelled', 'rejected'])->count();
            $ordersFailed    = Order::where('status', 'return_requested')->count();
            $ordersPending   = Order::where('status', 'returned')->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_customers' => $totalCustomers,
                    'total_vendors' => $totalVendors,
                    'orders_placed' => $ordersPlaced,
                    'orders_confirmed' => $ordersConfirmed,
                    'orders_shipped' => $ordersShipped,
                    'orders_completed' => $ordersCompleted,
                    'orders_canceled' => $ordersCanceled,
                    'orders_failed' => $ordersFailed,
                    'orders_pending' => $ordersPending,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get earnings data
     */
    public function earnings(Request $request)
    {
        try {
            $completedOrders = Order::where('status', 'delivered')->get();
            $totalEarnings = 0;
            $adminCommission = 0;
            $monthlyData = array_fill(0, 12, 0);

            foreach ($completedOrders as $order) {
                $orderTotal = (float) ($order->total ?? 0);
                $totalEarnings += $orderTotal;
                $adminCommission += round($orderTotal * config('plantix.admin_commission_rate', 0.10), 2);
                
                $month = $order->created_at->month - 1;
                $monthlyData[$month] += $orderTotal;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_earnings' => $totalEarnings,
                    'admin_commission' => $adminCommission,
                    'monthly_data' => $monthlyData,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching earnings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's order count
     */
    public function userOrders(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
            ]);

            $userId = (int) $validated['user_id'];
            $count = Order::where('user_id', $userId)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching user orders: ' . $e->getMessage()
            ], 500);
        }
    }
}
