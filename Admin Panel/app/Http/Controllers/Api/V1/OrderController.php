<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Services\Api\V1\OrderApiService;
use Illuminate\Http\Request;

class OrderController extends ApiController
{
    public function __construct(private readonly OrderApiService $service) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'dispute_status' => ['nullable', 'string', 'max:50'],
            'min_total' => ['nullable', 'numeric', 'min:0'],
            'max_total' => ['nullable', 'numeric', 'min:0'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = $this->service->listForActor($request->user(), $filters, (int) ($filters['limit'] ?? 20));

        return $this->paginated($paginator, $paginator->items());
    }

    public function show(Request $request, int $id)
    {
        $actor = $request->user();
        $query = Order::query()->with(['user:id,name,email', 'vendor:id,title,author_id', 'items.product']);

        if ($actor->role === 'admin') {
            $order = $query->findOrFail($id);
        } elseif ($actor->role === 'vendor') {
            $order = $query->forVendor((int) optional($actor->vendor)->id)->findOrFail($id);
        } else {
            $order = $query->forCustomer($actor->id)->findOrFail($id);
        }

        return $this->ok($order);
    }
}
