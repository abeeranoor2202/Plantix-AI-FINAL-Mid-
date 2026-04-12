<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'vendor_id'       => 'required|exists:vendors,id',
            'category_id'     => 'required|exists:categories,id',
            'brand_id'        => 'nullable|exists:brands,id',
            'name'            => 'required|string|max:255',
            'sku'             => 'nullable|string|max:100|unique:products,sku',
            'short_description' => 'nullable|string|max:255',
            'unit'            => 'nullable|string|max:50',
            'description'     => 'nullable|string|max:5000',
            'price'           => 'required|numeric|min:0',
            'discount_price'  => 'nullable|numeric|min:0|lt:price',
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
        $this->merge([
            'is_active'   => $this->boolean('is_active', true),
            'is_featured' => $this->boolean('is_featured', false),
            'is_returnable' => $this->boolean('is_returnable', true),
            'is_refundable' => $this->boolean('is_refundable', true),
            'track_stock' => $this->boolean('track_stock', true),
        ]);
    }
}
