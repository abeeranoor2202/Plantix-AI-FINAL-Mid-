<?php

namespace App\Services\Api\V1;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductApiService
{
    public function listForActor(User $actor, array $filters, int $limit): LengthAwarePaginator
    {
        $query = Product::query()->with(['vendor:id,title,author_id', 'category:id,title'])
            ->select([
                'id',
                'vendor_id',
                'category_id',
                'name',
                'slug',
                'price',
                'discount_price',
                'status',
                'is_active',
                'stock_quantity',
                'track_stock',
                'created_at',
            ]);

        if ($actor->role === 'vendor') {
            $vendorId = (int) optional($actor->vendor)->id;
            $query->where('vendor_id', $vendorId);
        }

        if (! empty($filters['search'])) {
            $term = (string) $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('slug', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['vendor_id']) && $filters['vendor_id'] !== null && $actor->role === 'admin') {
            $query->where('vendor_id', (int) $filters['vendor_id']);
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== null) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (array_key_exists('active', $filters) && $filters['active'] !== null) {
            $query->where('is_active', (bool) $filters['active']);
        }

        return $query->latest()->paginate($limit);
    }
}
