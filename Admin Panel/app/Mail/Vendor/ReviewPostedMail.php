<?php

namespace App\Mail\Vendor;

use App\Mail\PlantixBaseMail;
use App\Models\Product;
use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Mail\Mailables\Content;

class ReviewPostedMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Review  $review,
        public readonly Product $product,
        public readonly Vendor  $vendor,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return "⭐ New {$this->review->rating}-star review on \"{$this->product->name}\"";
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor.review-posted',
            with: [
                'review'        => $this->review,
                'product'       => $this->product,
                'vendor'        => $this->vendor,
                'recipientEmail'=> $this->vendor->author?->email ?? '',
            ]
        );
    }
}
