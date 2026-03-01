<?php

namespace App\Mail\User;

use App\Mail\PlantixBaseMail;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;

class CouponMail extends PlantixBaseMail
{
    public function __construct(
        public readonly User   $user,
        public readonly Coupon $coupon,
        public readonly string $type = 'assigned',  // assigned | expiring
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return $this->type === 'expiring'
            ? "⏰ Your coupon {$this->coupon->code} expires soon!"
            : "🎁 You have a new coupon: {$this->coupon->code}";
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.coupon',
            with: [
                'user'          => $this->user,
                'coupon'        => $this->coupon,
                'type'          => $this->type,
                'recipientEmail'=> $this->user->email,
            ]
        );
    }
}
