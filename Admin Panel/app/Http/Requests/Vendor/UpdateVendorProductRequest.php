<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('vendor')->check();
    }

    public function rules(): array
    {
        $productId = (int) $this->route('id');
        $vendorId = (int) auth('vendor')->user()->vendor->id;

        return [
            'category_id'    => 'nullable|exists:categories,id',
            'name'           => 'sometimes|required|string|max:255',
            'sku'            => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('vendor_id', $vendorId))
                    ->ignore($productId),
            ],
            'description'    => 'nullable|string|max:5000',
            'price'          => 'sometimes|required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery.*'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'      => 'boolean',
            'is_returnable'  => 'boolean',
            'is_refundable'  => 'boolean',
            'return_window_days' => 'nullable|integer|min:0|max:365',
            'stock_quantity' => 'nullable|integer|min:0',
            'track_stock'    => 'boolean',
            'attribute_values' => 'nullable|array',
        ];
    }

    public function prepareForValidation(): void
    {
        $merge = [
            'track_stock' => $this->boolean('track_stock', true),
        ];

        if ($this->hasAny(['is_active', 'is_returnable', 'is_refundable'])) {
            $merge['is_active'] = $this->boolean('is_active');
            $merge['is_returnable'] = $this->boolean('is_returnable');
            $merge['is_refundable'] = $this->boolean('is_refundable');
        }

        $this->merge($merge);
    }
}
