<?php

namespace App\Services\Shared;

use App\Models\Order;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Notifications\ReturnStatusNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReturnRefundService
{
    /**
     * Customer submits a return request.
     *
     * Enforces the return window: the order must have been delivered within
     * the last PLANTIX_RETURN_WINDOW_DAYS days.
     *
     * @param  UploadedFile[]|null  $images  Raw uploaded files (not paths)
     */
    public function requestReturn(User $user, Order $order, array $data): ReturnRequest
    {
        // ── 1. Allow only delivered orders ───────────────────────────────────
        if ($order->status !== 'delivered') {
            throw ValidationException::withMessages([
                'order' => 'Return requests can only be submitted for delivered orders.',
            ]);
        }

        // ── 2. Enforce return window ──────────────────────────────────────────
        $windowDays = config('plantix.return_window_days', 7);
        $deliveredAt = $order->delivered_at ?? $order->updated_at;

        if ($deliveredAt->diffInDays(now()) > $windowDays) {
            throw ValidationException::withMessages([
                'order' => "Return window has expired. Returns are only accepted within {$windowDays} days of delivery.",
            ]);
        }

        // ── 3. Prevent duplicate return for the same order ────────────────────
        if ($order->returnRequest()->exists()) {
            throw new \RuntimeException('A return request already exists for this order.');
        }

        // ── 4. Store uploaded images securely (private disk) ─────────────────
        $storedPaths = null;
        if (! empty($data['images']) && is_array($data['images'])) {
            $storedPaths = [];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $mimeMap = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
            ];

            foreach ($data['images'] as $file) {
                if (! ($file instanceof UploadedFile) || ! $file->isValid()) {
                    continue;
                }

                $mime = $file->getMimeType();
                if (! in_array($mime, $allowedMimes, true)) {
                    throw ValidationException::withMessages([
                        'images' => "File type '{$mime}' is not allowed. Only JPEG, PNG, WebP, and GIF images are accepted.",
                    ]);
                }

                $ext  = $mimeMap[$mime];
                $name = Str::uuid() . '.' . $ext;
                Storage::disk('local')->putFileAs('return-images/' . $order->id, $file, $name);
                $storedPaths[] = 'return-images/' . $order->id . '/' . $name;
            }
        }

        // ── 5. Create the return request ──────────────────────────────────────
        $return = ReturnRequest::create([
            'order_id'         => $order->id,
            'user_id'          => $user->id,
            'return_reason_id' => $data['return_reason_id'] ?? null,
            'description'      => $data['description'] ?? null,
            'images'           => $storedPaths ? json_encode($storedPaths) : null,
            'status'           => 'pending',
        ]);

        return $return->fresh(['order', 'reason']);
    }

    /**
     * Admin approves a return.
     */
    public function approve(ReturnRequest $return, ?string $adminNotes = null): ReturnRequest
    {
        $return->update(['status' => 'approved', 'admin_notes' => $adminNotes]);

        try {
            $return->user->notify(new ReturnStatusNotification($return));
        } catch (\Throwable $e) {
            Log::error('Return approval notification failed: ' . $e->getMessage());
        }

        return $return->fresh();
    }

    /**
     * Admin rejects a return.
     */
    public function reject(ReturnRequest $return, string $adminNotes): ReturnRequest
    {
        $return->update(['status' => 'rejected', 'admin_notes' => $adminNotes]);

        try {
            $return->user->notify(new ReturnStatusNotification($return));
        } catch (\Throwable $e) {
            Log::error('Return rejection notification failed: ' . $e->getMessage());
        }

        return $return->fresh();
    }

    /**
     * Process a refund for an approved return.
     */
    public function processRefund(ReturnRequest $return, array $data, User $processedBy): Refund
    {
        return DB::transaction(function () use ($return, $data, $processedBy) {

            $refund = Refund::create([
                'return_id'       => $return->id,
                'order_id'        => $return->order_id,
                'amount'          => $data['amount'],
                'method'          => $data['method'],
                'status'          => 'processed',
                'transaction_ref' => $data['transaction_ref'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'processed_at'    => now(),
                'processed_by'    => $processedBy->id,
            ]);

            $return->update(['status' => 'refunded']);
            $return->order->update(['payment_status' => 'refunded']);

            // Wallet refund
            if ($data['method'] === 'wallet') {
                $return->user->increment('wallet_amount', $data['amount']);
            }

            return $refund;
        });
    }
}
