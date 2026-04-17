<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Services\Api\V1\ReturnApiService;
use Illuminate\Http\Request;

class ReturnController extends ApiController
{
    public function __construct(private readonly ReturnApiService $service) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
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

        $query = ReturnRequest::query()->with([
            'order:id,order_number,user_id,vendor_id,status,total',
            'order.items.product',
            'user:id,name,email',
            'reason:id,name',
            'items.product:id,name',
            'refund',
        ]);

        if ($actor->role === 'vendor') {
            $vendorId = (int) optional($actor->vendor)->id;
            $query->whereHas('order', fn ($q) => $q->where('vendor_id', $vendorId));
        } elseif ($actor->role !== 'admin') {
            $query->where('user_id', $actor->id);
        }

        $return = $query->findOrFail($id);

        return $this->ok($return);
    }

    public function store(Request $request)
    {
        $actor = $request->user();

        if (! in_array($actor->role, ['user', 'customer'], true)) {
            return $this->fail('Only customers can create return requests.', null, 403);
        }

        $payload = $request->validate([
            'order_id' => ['required', 'integer', 'min:1'],
            'reason_id' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $order = Order::query()->forCustomer($actor->id)->findOrFail((int) $payload['order_id']);

        $return = $this->service->createForCustomer($actor, $order, $payload);

        return $this->created($return, 'Return request submitted successfully.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $actor = $request->user();

        if ($actor->role !== 'admin') {
            return $this->fail('Only admin can update return status.', null, 403);
        }

        $payload = $request->validate([
            'status' => ['required', 'in:approved,rejected,refund_processing,completed'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $return = ReturnRequest::query()->findOrFail($id);
        $updated = $this->service->transition($return, $payload['status'], $payload['notes'] ?? null, $actor);

        return $this->ok($updated, 'Return status updated.');
    }
}
