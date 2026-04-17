<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    /**
     * Display a listing of active vendors.
     */
    public function index(Request $request): View
    {
        $query = Vendor::where('is_active', true)->where('status', 'approved');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $sort = match ($request->sort) {
            'rating_desc' => ['rating', 'desc'],
            'newest'      => ['created_at', 'desc'],
            'az'          => ['title', 'asc'],
            default       => ['rating', 'desc'],
        };

        $stores = $query->orderBy(...$sort)->paginate(12);

        return view('customer.stores', compact('stores'));
    }

    /**
     * Display the specified vendor along with active products.
     */
    public function show(int $id): View
    {
        // Find the active/approved vendor
        $store = Vendor::where('is_active', true)
                   ->where('status', 'approved')
                       ->findOrFail($id);

        // Fetch their associated active/in-stock products
        $products = $store->products()
                          ->with(['category', 'primaryImage'])
                          ->active()
                          ->inStock()
                          ->paginate(12);

        // Fetch the 4 latest reviews strictly for this vendor's products
        // Alternatively we can use rating and review count from the vendor itself
        // but let's see what reviews the user actually left for this vendor.
        $reviews = \App\Models\Review::whereHas('product', function($query) use ($store) {
                                            $query->where('vendor_id', $store->id);
                                        })
                                        ->with('user')
                                        ->where('is_active', true)
                                        ->orderByDesc('created_at')
                                        ->limit(4)
                                        ->get();

        return view('customer.store-single', compact('store', 'products', 'reviews'));
    }
}
