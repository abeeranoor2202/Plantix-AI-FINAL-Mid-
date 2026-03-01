<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Production e-commerce schema hardening migration.
 *
 * Fixes identified:
 *  1. orders.status — add pending_payment, payment_failed, completed
 *  2. orders         — add payment_intent_id for direct Stripe PI lookup in webhooks
 *  3. products       — add sku (unique), status enum, rating_avg
 *  4. reviews        — add status enum, edit_locked_at, fix unique constraint
 *  5. coupons        — add per_user_limit column (separate from global usage_limit)
 *  6. order_items    — add snapshot columns (unit_price already exists, add discount_price)
 *  7. returns        — add vendor_notes (already done in another migration; guard with hasColumn)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ──────────────────────────────────────────────────────────────────────
        // 1. orders — extend status enum + add payment_intent_id
        // ──────────────────────────────────────────────────────────────────────

        // Change to string first so we can update values safely before re-typing
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });

        // Migrate any legacy 'pending' that means pending_payment for Stripe orders
        // (orders with payment_method=stripe and payment_status=pending)
        DB::table('orders')
            ->where('payment_method', 'stripe')
            ->where('payment_status', 'pending')
            ->where('status', 'pending')
            ->update(['status' => 'pending_payment']);

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'draft',            // not yet submitted
                'pending_payment',  // awaiting Stripe confirmation
                'payment_failed',   // Stripe payment failed
                'pending',          // COD order placed / payment confirmed
                'confirmed',        // vendor confirmed
                'processing',       // being prepared
                'shipped',          // dispatched
                'delivered',        // received by customer
                'completed',        // review window passed; order closed
                'cancelled',        // cancelled (any actor)
                'rejected',         // vendor rejected
                'return_requested', // customer raised return
                'returned',         // return approved + stock restored
                'refunded',         // full refund issued
            ])->default('pending_payment')->change();

            // Direct Stripe PI lookup — avoids joining payments table in webhook handler
            if (!Schema::hasColumn('orders', 'payment_intent_id')) {
                $table->string('payment_intent_id', 200)->nullable()->after('payment_method');
                $table->index('payment_intent_id');
            }
        });

        // ──────────────────────────────────────────────────────────────────────
        // 2. products — add sku, status enum, rating_avg
        // ──────────────────────────────────────────────────────────────────────

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 100)->nullable()->unique()->after('name');
            }

            // status enum replaces the is_active boolean with three-state value
            if (!Schema::hasColumn('products', 'status')) {
                $table->enum('status', ['active', 'inactive', 'draft'])
                      ->default('active')
                      ->after('is_active');
            }

            // Denormalised avg rating for O(1) shop listing reads
            if (!Schema::hasColumn('products', 'rating_avg')) {
                $table->decimal('rating_avg', 3, 2)->default(0.00)->after('sort_order');
                $table->unsignedSmallInteger('rating_count')->default(0)->after('rating_avg');
            }

            $table->index('status');
            $table->index('sku');
        });

        // Back-fill status from is_active for existing products
        DB::table('products')->where('is_active', true)->update(['status' => 'active']);
        DB::table('products')->where('is_active', false)->update(['status' => 'inactive']);

        // ──────────────────────────────────────────────────────────────────────
        // 3. reviews — moderation status, edit lock, correct unique constraint
        // ──────────────────────────────────────────────────────────────────────

        Schema::table('reviews', function (Blueprint $table) {
            // Drop the old (user_id, order_id) unique — too broad, one review per ORDER
            // not per product. New: unique per (user_id, order_id, product_id)
            try {
                $table->dropUnique(['user_id', 'order_id']);
            } catch (\Throwable) {
                // index might be named differently; ignore if already gone
            }

            if (!Schema::hasColumn('reviews', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])
                      ->default('approved') // auto-approve; change to 'pending' for moderation
                      ->after('is_active');
            }

            // Lock editing after 24 hours (set by post-save event)
            if (!Schema::hasColumn('reviews', 'edit_locked_at')) {
                $table->timestamp('edit_locked_at')->nullable()->after('status');
            }

            // New correct unique: one review per user per product per order
            $table->unique(['user_id', 'order_id', 'product_id'], 'reviews_user_order_product_unique');

            $table->index('status');
        });

        // ──────────────────────────────────────────────────────────────────────
        // 4. coupons — per-user usage limit (separate from global)
        // ──────────────────────────────────────────────────────────────────────

        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'per_user_limit')) {
                $table->unsignedSmallInteger('per_user_limit')->default(1)->after('usage_limit');
            }
        });

        // ──────────────────────────────────────────────────────────────────────
        // 5. order_items — add snapshot of discount price for audit trail
        // ──────────────────────────────────────────────────────────────────────

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0.00)->after('unit_price');
            }
            // FK to product_id is already in place; add index for reporting joins
            $table->index('product_id');
        });

        // ──────────────────────────────────────────────────────────────────────
        // 6. product_stocks — ensure quantities can never go negative at DB level
        //    (application enforces this too, but defence-in-depth)
        // ──────────────────────────────────────────────────────────────────────

        // Add check constraint via raw SQL (Blueprint doesn't support CHECK in MySQL 5.7)
        if (DB::getDriverName() === 'mysql') {
            $hasCheck = DB::select("
                SELECT COUNT(*) as cnt
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'product_stocks'
                  AND CONSTRAINT_NAME = 'chk_product_stocks_qty_non_negative'
            ");

            if (empty($hasCheck) || $hasCheck[0]->cnt == 0) {
                DB::statement('ALTER TABLE product_stocks
                    ADD CONSTRAINT chk_product_stocks_qty_non_negative CHECK (quantity >= 0)');
            }

            // Same on products.stock_quantity
            $hasCheck2 = DB::select("
                SELECT COUNT(*) as cnt
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'products'
                  AND CONSTRAINT_NAME = 'chk_products_stock_qty_non_negative'
            ");

            if (empty($hasCheck2) || $hasCheck2[0]->cnt == 0) {
                DB::statement('ALTER TABLE products
                    ADD CONSTRAINT chk_products_stock_qty_non_negative CHECK (stock_quantity >= 0)');
            }
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndexIfExists('orders_payment_intent_id_index');
            $table->dropColumn('payment_intent_id');
            $table->string('status', 30)->default('pending')->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndexIfExists('products_status_index');
            $table->dropIndexIfExists('products_sku_unique');
            $table->dropColumn(['sku', 'status', 'rating_avg', 'rating_count']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_user_order_product_unique');
            $table->dropColumn(['status', 'edit_locked_at']);
            $table->unique(['user_id', 'order_id']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('per_user_limit');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
        });
    }
};
