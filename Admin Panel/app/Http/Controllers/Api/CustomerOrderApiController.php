<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnReason;
use App\Services\Shared\ReturnRefundService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerOrderApiController extends Controller
{
    public function __construct(private readonly ReturnRefundService $returnService) {}

    // ── List orders ───────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['vendor', 'items.product'])
                       ->forCustomer($request->user()->id)
                       ->latest()
                       ->paginate(15);

        return response()->json([
            'success' => true,
            'orders'  => $orders->map(fn ($o) => $this->orderSummary($o)),
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    // ── Order detail ─────────────────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::with(['vendor', 'items.product', 'statusHistory', 'returnRequest', 'refund'])
                      ->forCustomer($request->user()->id)
                      ->findOrFail($id);

        return response()->json(['success' => true, 'order' => $this->orderDetail($order)]);
    }

    // ── Cancel order ─────────────────────────────────────────────────────────

    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = Order::forCustomer($request->user()->id)
                      ->whereIn('status', ['pending', 'confirmed'])
                      ->findOrFail($id);

        $order->update(['status' => 'cancelled']);
        $order->statusHistory()->create(['status' => 'cancelled', 'note' => 'Cancelled by customer.']);

        return response()->json(['success' => true, 'message' => 'Order cancelled successfully.']);
    }

    // ── Request return ───────────────────────────────────────────────────────

    public function requestReturn(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason_id' => 'required|exists:return_reasons,id',
            'notes'     => 'required|string|max:1000',
            'items'     => 'required|array',
            'items.*'   => 'nullable|integer|min:0',
        ]);

        $order = Order::forCustomer($request->user()->id)
                      ->where('status', 'delivered')
                      ->whereDoesntHave('returnRequest')
                      ->findOrFail($id);

        $this->returnService->requestReturn($request->user(), $order, $request->validated());

        return response()->json(['success' => true, 'message' => 'Return request submitted.']);
    }

    // ── Invoice download ─────────────────────────────────────────────────────

    public function invoice(Request $request, int $id): Response
    {
        $order = Order::with(['vendor', 'items.product', 'user'])
                      ->forCustomer($request->user()->id)
                      ->whereIn('status', ['processing', 'shipped', 'delivered', 'completed'])
                      ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.order-invoice', ['order' => $order])
                  ->setPaper('a4', 'portrait');

        return $pdf->download('invoice-' . $order->id . '.pdf');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function orderSummary(Order $order): array
    {
        return [
            'id'           => $order->id,
            'status'       => $order->status,
            'total'        => $order->total,
            'item_count'   => $order->items->count(),
            'vendor_name'  => optional($order->vendor)->store_name,
            'created_at'   => $order->created_at?->toISOString(),
        ];
    }

    private function orderDetail(Order $order): array
    {
        return array_merge($this->orderSummary($order), [
            'subtotal'       => $order->subtotal,
            'delivery_fee'   => $order->delivery_fee,
            'discount'       => $order->discount_amount,
            'address'        => $order->delivery_address,
            'notes'          => $order->notes,
            'items'          => $order->items->map(fn ($i) => [
                'id'       => $i->id,
                'name'     => optional($i->product)->name,
                'qty'      => $i->quantity,
                'price'    => $i->unit_price,
                'subtotal' => round($i->unit_price * $i->quantity, 2),
            ])->toArray(),
            'status_history' => $order->statusHistory->map(fn ($h) => [
                'status'     => $h->status,
                'note'       => $h->note,
                'created_at' => $h->created_at?->toISOString(),
            ])->toArray(),
        ]);
    }
}
