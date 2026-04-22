<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Services\Shared\ProductReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class ProductReviewController extends Controller
{
    public function __construct(
        private readonly ProductReviewService $reviewService,
    ) {}

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
        $product = Product::with('category')->findOrFail($productId);
        $textReviewEnabled = (bool) data_get($product, 'category.text_review_enabled', true);
        $imageReviewEnabled = (bool) data_get($product, 'category.image_review_enabled', false);

        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'rating'   => 'required|integer|min:1|max:5',
            'title'    => 'nullable|string|max:255',
            'comment'  => $textReviewEnabled ? 'nullable|string|max:2000' : 'prohibited',
            'review_images'   => $imageReviewEnabled ? 'nullable|array|max:5' : 'prohibited',
            'review_images.*' => $imageReviewEnabled ? 'image|max:5120' : 'prohibited',
        ]);

        /** @var \App\Models\User $user */
        $user    = auth('web')->user();
        try {
            $review = $this->reviewService->save($user, $product, [
                'order_id' => (int) $request->input('order_id'),
                'title' => $request->input('title'),
                'rating' => (int) $request->input('rating'),
                'comment' => $textReviewEnabled ? $request->input('comment') : null,
                'review_images' => $imageReviewEnabled ? (array) $request->file('review_images', []) : null,
            ]);
        } catch (AuthorizationException $e) {
            abort(403, 'You can only review products you have purchased');
        } catch (\DomainException $e) {
            return back()->withErrors(['review' => $e->getMessage()]);
        }

        $message = $review->wasRecentlyCreated
            ? 'Review submitted. It will appear after admin approval.'
            : 'Review updated. It will appear after admin approval.';

        return back()->with('success', $message);
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

