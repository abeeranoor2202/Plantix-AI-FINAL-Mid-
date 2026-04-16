<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        $productId = (int) $this->route('id');
        $vendorId = (int) ($this->input('vendor_id') ?: Product::query()->whereKey($productId)->value('vendor_id'));

        return [
            'vendor_id'       => 'sometimes|required|exists:vendors,id',
            'category_id'     => 'sometimes|required|exists:categories,id',
            'brand_id'        => 'nullable|exists:brands,id',
            'name'            => 'sometimes|required|string|max:255',
            'sku'             => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('vendor_id', $vendorId))
                    ->ignore($productId),
            ],
            'short_description' => 'nullable|string|max:255',
            'unit'            => 'nullable|string|max:50',
            'description'     => 'nullable|string|max:5000',
            'price'           => 'sometimes|required|numeric|min:0',
            'discount_price'  => 'nullable|numeric|min:0',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery.*'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'       => 'boolean',
            'is_featured'     => 'boolean',
            'is_returnable'   => 'boolean',
            'is_refundable'   => 'boolean',
            'return_window_days' => 'nullable|integer|min:0|max:365',
            'tax_rate'        => 'nullable|numeric|min:0|max:100',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'sort_order'      => 'integer|min:0',
            'stock_quantity'  => 'nullable|integer|min:0',
            'track_stock'     => 'boolean',
            'attribute_values' => 'nullable|array',
        ];
    }

    public function prepareForValidation(): void
    {
        $merge = [
            'track_stock' => $this->boolean('track_stock', true),
        ];

        if ($this->hasAny(['is_active', 'is_featured', 'is_returnable', 'is_refundable'])) {
            $merge['is_active'] = $this->boolean('is_active');
            $merge['is_featured'] = $this->boolean('is_featured');
            $merge['is_returnable'] = $this->boolean('is_returnable');
            $merge['is_refundable'] = $this->boolean('is_refundable');
        }

        $this->merge($merge);
    }
}
