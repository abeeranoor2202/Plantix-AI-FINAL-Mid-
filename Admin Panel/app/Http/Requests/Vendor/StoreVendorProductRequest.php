<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('vendor')->check();
    }

    public function rules(): array
    {
        return [
            'category_id'    => 'nullable|exists:categories,id',
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:5000',
            'price'          => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery.*'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'      => 'boolean',
            'is_returnable'  => 'boolean',
            'return_window_days' => 'nullable|integer|min:0|max:365',
            'stock_quantity' => 'nullable|integer|min:0',
            'track_stock'    => 'boolean',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_active'   => $this->boolean('is_active', true),
            'is_returnable' => $this->boolean('is_returnable', true),
            'track_stock' => $this->boolean('track_stock', true),
        ]);
    }
}
