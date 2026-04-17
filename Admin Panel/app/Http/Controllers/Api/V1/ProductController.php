<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Services\Api\V1\ProductApiService;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function __construct(private readonly ProductApiService $service) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,draft'],
            'vendor_id' => ['nullable', 'integer', 'min:1'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'active' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = $this->service->listForActor($request->user(), $filters, (int) ($filters['limit'] ?? 20));

        return $this->paginated($paginator, $paginator->items());
    }

    public function show(Request $request, int $id)
    {
        $actor = $request->user();

        $query = Product::query()->with([
            'vendor:id,title,author_id',
            'category:id,title',
            'images:id,product_id,image,is_primary,sort_order',
        ]);

        if ($actor->role === 'vendor') {
            $vendorId = (int) optional($actor->vendor)->id;
            $query->where('vendor_id', $vendorId);
        }

        $product = $query->findOrFail($id);

        return $this->ok($product);
    }
}
