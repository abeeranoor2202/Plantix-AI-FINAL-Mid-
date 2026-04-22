<?php

namespace App\Services\Dashboard;

use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class AlertingService
{
    public function forAdmin(): array
    {
        // Canonical return lifecycle table is `returns`; `return_requests` is legacy.
        $pendingReturns = (int) DB::table('returns')->where('status', 'requested')->count();
        $pendingDisputes = (int) DB::table('order_disputes')->where('status', 'open')->count();
        $pendingAppointments = (int) DB::table('appointments')
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_PENDING_EXPERT_APPROVAL,
            ])
            ->count();
        $lowStockProducts = (int) DB::table('products')
            ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
            ->where('product_stocks.quantity', '<=', DB::raw('products.low_stock_threshold'))
            ->whereNull('products.deleted_at')
            ->count();

        return [
            [
                'level' => $pendingReturns > 20 ? 'high' : 'medium',
                'key' => 'returns.pending',
                'label' => 'Pending return requests',
                'count' => $pendingReturns,
            ],
            [
                'level' => $pendingDisputes > 10 ? 'high' : 'medium',
                'key' => 'disputes.open',
                'label' => 'Open order disputes',
                'count' => $pendingDisputes,
            ],
            [
                'level' => $pendingAppointments > 25 ? 'high' : 'medium',
                'key' => 'appointments.pending',
                'label' => 'Pending appointments',
                'count' => $pendingAppointments,
            ],
            [
                'level' => $lowStockProducts > 30 ? 'high' : 'medium',
                'key' => 'stock.low',
                'label' => 'Low-stock products',
                'count' => $lowStockProducts,
            ],
        ];
    }
}
