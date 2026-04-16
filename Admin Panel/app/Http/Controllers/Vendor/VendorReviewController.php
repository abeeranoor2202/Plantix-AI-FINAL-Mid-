<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * VendorReviewController
 *
 * Allows vendors to view and monitor customer reviews left on their products.
 * Vendors cannot edit or delete reviews; only admins can do that.
 */
class VendorReviewController extends Controller
{
    private function vendorId(): int
    {
        return auth('vendor')->user()->vendor->id;
    }

    /**
     * List all reviews for this vendor's products.
     * Route: GET /vendor/reviews
     */
    public function index(Request $request): View
    {
        $query = Review::with(['user', 'product'])
            ->where('vendor_id', $this->vendorId())
            ->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->paginate(20)->withQueryString();

        $avgRating = Review::where('vendor_id', $this->vendorId())->avg('rating');
        $ratingCounts = Review::where('vendor_id', $this->vendorId())
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->orderByDesc('rating')
            ->pluck('total', 'rating');

        return view('vendor.reviews.index', compact('reviews', 'avgRating', 'ratingCounts'));
    }

    /**
     * Show a single review detail.
     * Route: GET /vendor/reviews/{id}
     */
    public function show(int $id): View
    {
        $review = Review::with(['user', 'product', 'order'])
            ->where('vendor_id', $this->vendorId())
            ->findOrFail($id);

        return view('vendor.reviews.show', compact('review'));
    }

    public function respond(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'vendor_response' => ['required', 'string', 'max:2000'],
        ]);

        $review = Review::where('vendor_id', $this->vendorId())->findOrFail($id);

        $review->update([
            'vendor_response' => $data['vendor_response'],
            'vendor_responded_at' => now(),
        ]);

        return back()->with('success', 'Your response has been saved.');
    }
}
