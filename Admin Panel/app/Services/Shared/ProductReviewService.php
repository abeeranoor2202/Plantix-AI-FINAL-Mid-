<?php

namespace App\Services\Shared;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductReviewService
{
    public function __construct(
        private readonly ProductReviewEligibilityService $eligibility,
    ) {}

    /**
     * Create or update a single review per user+product.
     * Throws AuthorizationException (403) when purchase verification fails.
     */
    public function save(User $user, Product $product, array $data): Review
    {
        $orderId = (int) ($data['order_id'] ?? 0);
        if (! $this->eligibility->isOrderEligible($user, $product, $orderId)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('You can only review products you have purchased');
        }

        return DB::transaction(function () use ($user, $product, $data, $orderId): Review {
            $existingReview = Review::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->latest('id')
                ->first();

            $reviewImages = $this->normalizeReviewImages(
                $existingReview?->review_images,
                $data['review_images'] ?? null,
                (bool) data_get($product, 'category.image_review_enabled', false)
            );

            if ($existingReview) {
                if (! $existingReview->isEditable()) {
                    throw new \DomainException('This review can no longer be edited (edit window has closed).');
                }

                $existingReview->update([
                    'order_id'       => $orderId,
                    'vendor_id'      => $product->vendor_id,
                    'title'          => $data['title'] ?? null,
                    'rating'         => (int) $data['rating'],
                    'comment'        => $data['comment'] ?? null,
                    'review_images'  => $reviewImages,
                    'status'         => Review::STATUS_PENDING,
                ]);

                return $existingReview->fresh();
            }

            return Review::create([
                'product_id'      => $product->id,
                'order_id'        => $orderId,
                'user_id'         => $user->id,
                'vendor_id'       => $product->vendor_id,
                'title'           => $data['title'] ?? null,
                'rating'          => (int) $data['rating'],
                'comment'         => $data['comment'] ?? null,
                'review_images'   => $reviewImages,
                'status'          => Review::STATUS_PENDING,
            ]);
        });
    }

    private function normalizeReviewImages(?array $existing, mixed $newFiles, bool $imageReviewEnabled): ?array
    {
        if (! $imageReviewEnabled) {
            return null;
        }

        if (! is_array($newFiles) || empty($newFiles)) {
            return $existing ?: null;
        }

        foreach ((array) $existing as $imagePath) {
            Storage::disk('public')->delete((string) $imagePath);
        }

        $stored = [];
        foreach ($newFiles as $uploadedImage) {
            if (! $uploadedImage instanceof UploadedFile) {
                continue;
            }
            $stored[] = $uploadedImage->store('reviews', 'public');
        }

        return $stored ?: null;
    }
}

