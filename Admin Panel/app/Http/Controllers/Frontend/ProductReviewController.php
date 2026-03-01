<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /**
     * Submit a review for a product tied to a specific delivered order.
     *
     * Rules enforced:
     *  1. The user must have a delivered or completed order containing the product.
     *  2. One review per (user, order_id, product_id) — DB unique constraint.
     *  3. Review editing is blocked after EDIT_LOCK_HOURS (model constant).
     */
    public function store(Request $request, int $productId): RedirectResponse
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string|max:2000',
        ]);

        /** @var \App\Models\User $user */
        $user    = auth('web')->user();
        $product = Product::findOrFail($productId);

        // ── 1. Verify the order belongs to this user and contains the product ─
        $order = Order::where('id', $request->order_id)
                      ->where('user_id', $user->id)
                      ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
                      ->first();

        if (! $order) {
            return back()->withErrors([
                'order_id' => 'You can only review products from delivered or completed orders.',
            ]);
        }

        $orderHasProduct = $order->items()->where('product_id', $product->id)->exists();
        if (! $orderHasProduct) {
            return back()->withErrors([
                'product_id' => 'This product is not part of the selected order.',
            ]);
        }

        // ── 2. Check for existing review (one per order+product) ──────────────
        $existingReview = Review::where('user_id', $user->id)
                                ->where('order_id', $order->id)
                                ->where('product_id', $product->id)
                                ->first();

        if ($existingReview) {
            // ── 3. Enforce edit lock ─────────────────────────────────────────
            if (! $existingReview->isEditable()) {
                return back()->withErrors([
                    'review' => 'This review can no longer be edited (edit window has closed).',
                ]);
            }

            $existingReview->update([
                'rating'  => $request->rating,
                'comment' => $request->comment,
                'status'  => Review::STATUS_PENDING, // re-moderate on edit
            ]);

            return back()->with('success', 'Review updated. It will appear after admin approval.');
        }

        Review::create([
            'product_id' => $product->id,
            'order_id'   => $order->id,
            'user_id'    => $user->id,
            'vendor_id'  => $product->vendor_id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
            'status'     => Review::STATUS_PENDING,
        ]);

        return back()->with('success', 'Review submitted. It will appear after admin approval.');
    }

    /**
     * Delete own review (only within edit window).
     */
    public function destroy(int $reviewId): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user   = auth('web')->user();
        $review = Review::where('user_id', $user->id)->findOrFail($reviewId);

        if (! $review->isEditable()) {
            return back()->withErrors(['review' => 'This review can no longer be deleted.']);
        }

        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}

