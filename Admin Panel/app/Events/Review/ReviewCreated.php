<?php

namespace App\Events\Review;

use App\Models\Product;
use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Review  $review,
        public readonly Product $product,
        public readonly Vendor  $vendor,
    ) {}
}
