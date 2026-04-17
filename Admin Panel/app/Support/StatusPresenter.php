<?php

namespace App\Support;

class StatusPresenter
{
    public static function present(string $domain, ?string $status): array
    {
        $normalized = strtolower(trim((string) $status));

        $maps = [
            'order' => [
                'pending' => ['#FEF3C7', '#B45309'],
                'pending_payment' => ['#DBEAFE', '#1D4ED8'],
                'confirmed' => ['#DCFCE7', '#166534'],
                'processing' => ['#E0E7FF', '#3730A3'],
                'shipped' => ['#CFFAFE', '#0E7490'],
                'delivered' => ['#D1FAE5', '#065F46'],
                'completed' => ['#D1FAE5', '#065F46'],
                'cancelled' => ['#FEE2E2', '#B91C1C'],
                'rejected' => ['#FEE2E2', '#B91C1C'],
                'refunded' => ['#E5E7EB', '#374151'],
                'payment_failed' => ['#FEE2E2', '#B91C1C'],
                'return_requested' => ['#FFEDD5', '#C2410C'],
                'returned' => ['#E5E7EB', '#374151'],
                'draft' => ['#F3F4F6', '#4B5563'],
            ],
            'appointment' => [
                'pending_payment' => ['#DBEAFE', '#1D4ED8'],
                'payment_failed' => ['#FEE2E2', '#B91C1C'],
                'pending_expert_approval' => ['#FEF3C7', '#B45309'],
                'confirmed' => ['#DCFCE7', '#166534'],
                'reschedule_requested' => ['#EDE9FE', '#6D28D9'],
                'rescheduled' => ['#E0E7FF', '#3730A3'],
                'completed' => ['#D1FAE5', '#065F46'],
                'cancelled' => ['#FEE2E2', '#B91C1C'],
                'rejected' => ['#FEE2E2', '#B91C1C'],
                'draft' => ['#F3F4F6', '#4B5563'],
            ],
            'forum' => [
                'open' => ['#D1FAE5', '#065F46'],
                'resolved' => ['#DBEAFE', '#1D4ED8'],
                'locked' => ['#E5E7EB', '#374151'],
                'archived' => ['#FEF3C7', '#92400E'],
            ],
            'dispute' => [
                'pending' => ['#FEF3C7', '#B45309'],
                'vendor_responded' => ['#DBEAFE', '#1D4ED8'],
                'escalated' => ['#EDE9FE', '#6D28D9'],
                'resolved' => ['#DCFCE7', '#166534'],
                'rejected' => ['#FEE2E2', '#B91C1C'],
                'cancelled' => ['#E5E7EB', '#374151'],
                'refunded' => ['#E5E7EB', '#374151'],
            ],
        ];

        $palette = $maps[$domain][$normalized] ?? ['#F3F4F6', '#4B5563'];

        return [
            'label' => ucwords(str_replace('_', ' ', $normalized !== '' ? $normalized : 'unknown')),
            'background' => $palette[0],
            'color' => $palette[1],
        ];
    }
}
