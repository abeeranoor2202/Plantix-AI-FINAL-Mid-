<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Product::with(['vendor', 'category', 'stock'])
            ->withTrashed(false);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        $sort  = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        return $query->orderBy($sort, $order)->paginate($perPage);
    }

    public function findById(int $id): Product
    {
        return Product::with(['vendor', 'category', 'images', 'attributes', 'stock'])
                      ->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function featured(int $limit = 8): Collection
    {
        return Product::with(['vendor', 'category', 'primaryImage'])
            ->active()
            ->featured()
            ->inStock()
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    public function byVendor(int $vendorId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginate(array_merge($filters, ['vendor_id' => $vendorId]), $perPage);
    }
}
