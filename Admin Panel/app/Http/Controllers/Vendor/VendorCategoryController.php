<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use Illuminate\View\View;

/**
 * VendorCategoryController
 *
 * Provides vendors with a read-only view of the global category and attribute
 * catalogue managed by admin. Vendors use this to assign categories / attributes
 * to their products but cannot create or delete global taxonomy entries.
 */
class VendorCategoryController extends Controller
{
    /**
     * List all active categories.
     * Route: GET /vendor/categories
     */
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('name')->paginate(30);
        return view('vendor.categories.index', compact('categories'));
    }

    /**
     * List all product attributes (e.g. Weight, Size, Color).
     * Route: GET /vendor/attributes
     */
    public function attributes(): View
    {
        $attributes = Attribute::orderBy('title')->paginate(30);
        return view('vendor.attributes.index', compact('attributes'));
    }
}
