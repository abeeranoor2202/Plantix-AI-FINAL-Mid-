<?php

namespace App\Mail\Vendor;

use App\Mail\PlantixBaseMail;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Mail\Mailables\Content;

class LowStockMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Product $product,
        public readonly Vendor  $vendor,
        public readonly int     $currentStock,
        public readonly int     $threshold = 5,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return "⚠️ Low Stock Alert: \"{$this->product->name}\" — {$this->currentStock} units left";
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor.low-stock',
            with: [
                'product'       => $this->product,
                'vendor'        => $this->vendor,
                'currentStock'  => $this->currentStock,
                'threshold'     => $this->threshold,
                'recipientEmail'=> $this->vendor->author?->email ?? '',
            ]
        );
    }
}
