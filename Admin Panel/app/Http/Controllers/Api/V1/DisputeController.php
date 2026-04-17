<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Services\Api\V1\DisputeApiService;
use Illuminate\Http\Request;

class DisputeController extends ApiController
{
    public function __construct(private readonly DisputeApiService $service) {}

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

    public function store(Request $request)
    {
        $actor = $request->user();

        if (! in_array($actor->role, ['user', 'customer'], true)) {
            return $this->fail('Only customers can open disputes.', null, 403);
        }

        $payload = $request->validate([
            'order_id' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $order = Order::query()->forCustomer($actor->id)->findOrFail((int) $payload['order_id']);
        $dispute = $this->service->open($actor, $order, $payload['reason']);

        return $this->created($dispute, 'Dispute opened successfully.');
    }

    public function escalate(Request $request, int $orderId)
    {
        $actor = $request->user();

        if (! in_array($actor->role, ['user', 'customer'], true)) {
            return $this->fail('Only customers can escalate disputes.', null, 403);
        }

        $payload = $request->validate([
            'escalation_reason' => ['required', 'string', 'max:2000'],
        ]);

        $order = Order::query()->forCustomer($actor->id)->findOrFail($orderId);
        $dispute = $this->service->escalate($actor, $order, $payload['escalation_reason']);

        return $this->ok($dispute, 'Dispute escalated successfully.');
    }

    public function vendorRespond(Request $request, int $orderId)
    {
        $actor = $request->user();

        if ($actor->role !== 'vendor') {
            return $this->fail('Only vendors can respond to disputes.', null, 403);
        }

        $payload = $request->validate([
            'response' => ['required', 'string', 'max:2000'],
        ]);

        $vendorId = (int) optional($actor->vendor)->id;
        $order = Order::query()->where('vendor_id', $vendorId)->findOrFail($orderId);
        $dispute = $this->service->vendorRespond($actor, $order, $payload['response']);

        return $this->ok($dispute, 'Dispute response submitted successfully.');
    }

    public function resolve(Request $request, int $orderId)
    {
        $actor = $request->user();

        if ($actor->role !== 'admin') {
            return $this->fail('Only admin can resolve disputes.', null, 403);
        }

        $payload = $request->validate([
            'status' => ['required', 'in:resolved,rejected,cancelled'],
            'resolution' => ['required', 'string', 'max:2000'],
        ]);

        $order = Order::query()->findOrFail($orderId);
        $dispute = $this->service->resolve($actor, $order, $payload['status'], $payload['resolution']);

        return $this->ok($dispute, 'Dispute resolved successfully.');
    }
}
