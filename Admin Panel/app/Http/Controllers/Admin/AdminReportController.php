<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * AdminReportController
 *
 * All queries use indexed columns (status, created_at, vendor_id, product_id).
 * Heavy aggregations are scoped by a date range to stay performant at scale.
 *
 * Routes (all require admin guard):
 *   GET  /admin/reports                     → overview dashboard
 *   GET  /admin/reports/sales               → sales over time (JSON, date-range)
 *   GET  /admin/reports/top-products        → top products by revenue/qty (JSON)
 *   GET  /admin/reports/top-vendors         → top vendors by revenue (JSON)
 *   GET  /admin/reports/order-statuses      → order status breakdown (JSON)
 *   GET  /admin/reports/refunds             → refund summary (JSON)
 *   GET  /admin/reports/export              → CSV download (orders, date-range)
 */
class AdminReportController extends Controller
{
    // Default lookback window when no dates are supplied
    private const DEFAULT_DAYS = 30;

    // ─────────────────────────────────────────────────────────────────────────
    // Dashboard overview
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/reports
     *
     * Returns a blade view with high-level KPIs for the last 30 days
     * plus month-over-month comparison values for widgets.
     */
    public function index(Request $request): View
    {
        [$from, $to] = $this->parseDateRange($request, self::DEFAULT_DAYS);
        [$prevFrom, $prevTo] = $this->previousPeriod($from, $to);

        // ── Current period ────────────────────────────────────────────────────
        $current = $this->periodStats($from, $to);

        // ── Previous period (for % change) ───────────────────────────────────
        $previous = $this->periodStats($prevFrom, $prevTo);

        // ── All-time totals ───────────────────────────────────────────────────
        $totalOrders    = Order::count();
        $totalRevenue   = Order::whereIn('status', $this->revenueStatuses())
                               ->sum('grand_total');
        $totalCustomers = User::where('role', 'customer')->count();
        $totalRefunds   = Refund::where('status', 'processed')->sum('amount');

        // ── Pending action counts (for alert badges) ──────────────────────────
        $pendingOrders  = Order::where('status', Order::STATUS_PENDING)->count();
        $pendingReturns = \App\Models\ReturnRequest::where('status', 'pending')->count();

        return view('admin.reports.index', compact(
            'current', 'previous',
            'totalOrders', 'totalRevenue', 'totalCustomers', 'totalRefunds',
            'pendingOrders', 'pendingReturns',
            'from', 'to'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sales over time (JSON — consumed by Chart.js)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/reports/sales
     *
     * Query params:
     *   from     (Y-m-d)        default: 30 days ago
     *   to       (Y-m-d)        default: today
     *   group_by (day|week|month)  default: day
     *   vendor_id (int)         optional: filter to a specific vendor
     */
    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'from'      => 'nullable|date',
            'to'        => 'nullable|date|after_or_equal:from',
            'group_by'  => 'nullable|in:day,week,month',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
        ]);

        [$from, $to] = $this->parseDateRange($request, self::DEFAULT_DAYS);
        $groupBy     = $request->input('group_by', 'day');

        $query = Order::select(
                        DB::raw("{$this->dateExprSql($groupBy)} AS period"),
                        DB::raw('COUNT(*) AS order_count'),
                        DB::raw('SUM(grand_total) AS revenue'),
                        DB::raw('SUM(discount_amount) AS total_discount'),
                        DB::raw('SUM(tax_amount) AS total_tax')
                    )
                    ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
                    ->whereIn('status', $this->revenueStatuses())
                    ->groupBy('period')
                    ->orderBy('period');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        return response()->json([
            'from'    => $from->toDateString(),
            'to'      => $to->toDateString(),
            'data'    => $query->get(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Top products
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/reports/top-products
     *
     * Query params:
     *   from      (Y-m-d)   default: 30 days ago
     *   to        (Y-m-d)   default: today
     *   limit     (int)     default: 10, max: 50
     *   vendor_id (int)     optional
     *   sort_by   (revenue|qty)  default: revenue
     */
    public function topProducts(Request $request): JsonResponse
    {
        $request->validate([
            'from'      => 'nullable|date',
            'to'        => 'nullable|date|after_or_equal:from',
            'limit'     => 'nullable|integer|min:1|max:50',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'sort_by'   => 'nullable|in:revenue,qty',
        ]);

        [$from, $to] = $this->parseDateRange($request, self::DEFAULT_DAYS);
        $limit       = $request->integer('limit', 10);
        $sortBy      = $request->input('sort_by', 'revenue');

        $query = OrderItem::select(
                        'order_items.product_id',
                        DB::raw('SUM(order_items.quantity) AS total_qty'),
                        DB::raw('SUM(order_items.quantity * order_items.price) AS revenue')
                    )
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()])
                    ->whereIn('orders.status', $this->revenueStatuses())
                    ->groupBy('order_items.product_id')
                    ->orderByDesc($sortBy === 'qty' ? 'total_qty' : 'revenue')
                    ->limit($limit)
                    ->with('product:id,name,sku,vendor_id');

        if ($request->filled('vendor_id')) {
            $query->where('orders.vendor_id', $request->integer('vendor_id'));
        }

        return response()->json([
            'from'  => $from->toDateString(),
            'to'    => $to->toDateString(),
            'data'  => $query->get(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Top vendors
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/reports/top-vendors
     *
     * Query params:
     *   from   (Y-m-d)   default: 30 days ago
     *   to     (Y-m-d)   default: today
     *   limit  (int)     default: 10, max: 50
     */
    public function topVendors(Request $request): JsonResponse
    {
        $request->validate([
            'from'  => 'nullable|date',
            'to'    => 'nullable|date|after_or_equal:from',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        [$from, $to] = $this->parseDateRange($request, self::DEFAULT_DAYS);
        $limit       = $request->integer('limit', 10);

        $data = Order::select(
                        'vendor_id',
                        DB::raw('COUNT(*) AS order_count'),
                        DB::raw('SUM(grand_total) AS revenue'),
                        DB::raw('SUM(discount_amount) AS total_discount')
                    )
                    ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
                    ->whereIn('status', $this->revenueStatuses())
                    ->whereNotNull('vendor_id')
                    ->groupBy('vendor_id')
                    ->orderByDesc('revenue')
                    ->limit($limit)
                    ->with('vendor:id,name,email')
                    ->get();

        return response()->json([
            'from' => $from->toDateString(),
            'to'   => $to->toDateString(),
            'data' => $data,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Order status breakdown
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/reports/order-statuses
     *
     * Returns order counts grouped by status for a date range.
     */
    public function orderStatuses(Request $request): JsonResponse
    {
        $request->validate([
            'from'      => 'nullable|date',
            'to'        => 'nullable|date|after_or_equal:from',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
        ]);

        [$from, $to] = $this->parseDateRange($request, self::DEFAULT_DAYS);

        $query = Order::select('status', DB::raw('COUNT(*) AS count'))
                      ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
                      ->groupBy('status')
                      ->orderBy('count', 'desc');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        return response()->json([
            'from' => $from->toDateString(),
            'to'   => $to->toDateString(),
            'data' => $query->get(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Refund summary
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/reports/refunds
     *
     * Returns total refund amount + count, grouped by day/week/month
     * and broken down by method (stripe / wallet / bank_transfer).
     */
    public function refunds(Request $request): JsonResponse
    {
        $request->validate([
            'from'     => 'nullable|date',
            'to'       => 'nullable|date|after_or_equal:from',
            'group_by' => 'nullable|in:day,week,month',
        ]);

        [$from, $to] = $this->parseDateRange($request, self::DEFAULT_DAYS);
        $groupBy     = $request->input('group_by', 'day');

        // Summary by period
        $timeline = Refund::select(
                        DB::raw("{$this->dateExprSql($groupBy)} AS period"),
                        DB::raw('COUNT(*) AS count'),
                        DB::raw('SUM(amount) AS total_amount')
                    )
                    ->where('status', 'processed')
                    ->whereBetween('processed_at', [$from->startOfDay(), $to->endOfDay()])
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();

        // Breakdown by method
        $byMethod = Refund::select('method', DB::raw('COUNT(*) AS count'), DB::raw('SUM(amount) AS total_amount'))
                          ->where('status', 'processed')
                          ->whereBetween('processed_at', [$from->startOfDay(), $to->endOfDay()])
                          ->groupBy('method')
                          ->get();

        // Totals
        $totals = Refund::where('status', 'processed')
                        ->whereBetween('processed_at', [$from->startOfDay(), $to->endOfDay()])
                        ->selectRaw('COUNT(*) AS count, SUM(amount) AS total_amount')
                        ->first();

        return response()->json([
            'from'      => $from->toDateString(),
            'to'        => $to->toDateString(),
            'totals'    => $totals,
            'by_method' => $byMethod,
            'timeline'  => $timeline,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Monthly growth (revenue + orders)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/reports/monthly-growth
     *
     * Returns the last N months of revenue + order count.
     * Used for trend chart on dashboard.
     */
    public function monthlyGrowth(Request $request): JsonResponse
    {
        $request->validate([
            'months'    => 'nullable|integer|min:1|max:24',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
        ]);

        $months = $request->integer('months', 12);
        $from   = now()->subMonths($months - 1)->startOfMonth();
        $to     = now()->endOfMonth();

        $query = Order::select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m') AS month"),
                        DB::raw('COUNT(*) AS order_count'),
                        DB::raw('SUM(grand_total) AS revenue')
                    )
                    ->whereBetween('created_at', [$from, $to])
                    ->whereIn('status', $this->revenueStatuses())
                    ->groupBy('month')
                    ->orderBy('month');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        $rows = $query->get()->keyBy('month');

        // Fill in months with zero revenue so graph has no gaps
        $filled = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $key = $cursor->format('Y-m');
            $filled[] = $rows->has($key)
                ? $rows[$key]
                : ['month' => $key, 'order_count' => 0, 'revenue' => '0.00'];
            $cursor->addMonth();
        }

        return response()->json(['data' => $filled]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CSV export
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/reports/export
     *
     * Streams a CSV of orders within a date range.
     * Uses chunking so memory stays flat for large exports.
     *
     * Query params:
     *   from       (Y-m-d)
     *   to         (Y-m-d)
     *   status     (string)   optional filter
     *   vendor_id  (int)      optional filter
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $request->validate([
            'from'      => 'nullable|date',
            'to'        => 'nullable|date|after_or_equal:from',
            'status'    => 'nullable|string',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
        ]);

        [$from, $to] = $this->parseDateRange($request, self::DEFAULT_DAYS);

        $filename = 'orders_' . $from->toDateString() . '_to_' . $to->toDateString() . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'X-Accel-Buffering'   => 'no',
            'Cache-Control'       => 'no-cache',
        ];

        $csvHeaders = [
            'Order #', 'Date', 'Customer', 'Customer Email',
            'Vendor', 'Status', 'Payment Method', 'Payment Status',
            'Subtotal', 'Discount', 'Tax', 'Shipping', 'Grand Total',
        ];

        $query = Order::with(['user:id,name,email', 'vendor:id,name'])
                      ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
                      ->orderBy('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        return response()->streamDownload(function () use ($query, $csvHeaders) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel handles encoding correctly
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $csvHeaders);

            $query->chunk(500, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->order_number,
                        $order->created_at->format('Y-m-d H:i'),
                        optional($order->user)->name,
                        optional($order->user)->email,
                        optional($order->vendor)->name ?? 'N/A',
                        $order->status,
                        $order->payment_method,
                        $order->payment_status,
                        number_format($order->subtotal, 2),
                        number_format($order->discount_amount ?? 0, 2),
                        number_format($order->tax_amount ?? 0, 2),
                        number_format($order->shipping_amount ?? 0, 2),
                        number_format($order->grand_total, 2),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, $headers);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Parse from/to date range from request.
     * Returns Carbon instances [from, to].
     */
    private function parseDateRange(Request $request, int $defaultDays): array
    {
        $from = $request->filled('from')
            ? \Carbon\Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays($defaultDays)->startOfDay();

        $to = $request->filled('to')
            ? \Carbon\Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    /**
     * Returns the period immediately before [from, to] of equal length.
     */
    private function previousPeriod(\Carbon\Carbon $from, \Carbon\Carbon $to): array
    {
        $days    = $from->diffInDays($to) + 1;
        $prevTo  = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        return [$prevFrom, $prevTo];
    }

    /**
     * Aggregate KPIs for a given period.
     */
    private function periodStats(\Carbon\Carbon $from, \Carbon\Carbon $to): array
    {
        $base = Order::whereBetween('created_at', [$from, $to]);

        return [
            'order_count'    => (clone $base)->count(),
            'revenue'        => (clone $base)->whereIn('status', $this->revenueStatuses())->sum('grand_total'),
            'avg_order'      => (clone $base)->whereIn('status', $this->revenueStatuses())->avg('grand_total') ?? 0,
            'new_customers'  => User::where('role', 'customer')->whereBetween('created_at', [$from, $to])->count(),
            'refunds_issued' => Refund::where('status', 'processed')->whereBetween('processed_at', [$from, $to])->sum('amount'),
        ];
    }

    /**
     * Order statuses that count as revenue-generating (paid/fulfilled).
     */
    private function revenueStatuses(): array
    {
        return [
            Order::STATUS_CONFIRMED,
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_COMPLETED,
        ];
    }

    /**
     * MySQL DATE_FORMAT expression for Chart.js grouping.
     */
    private function dateExprSql(string $groupBy): string
    {
        return match ($groupBy) {
            'week'  => "DATE_FORMAT(created_at, '%x-W%v')",
            'month' => "DATE_FORMAT(created_at, '%Y-%m')",
            default => "DATE(created_at)",
        };
    }
}
