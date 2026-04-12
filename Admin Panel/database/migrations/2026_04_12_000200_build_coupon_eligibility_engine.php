<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->decimal('discount_amount', 10, 2)->default(0.00);
                $table->timestamp('used_at')->useCurrent();
                $table->timestamps();

                $table->index(['coupon_id', 'user_id']);
                $table->index('order_id');
            });
        }

        if (! Schema::hasTable('coupon_product')) {
            Schema::create('coupon_product', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['coupon_id', 'product_id']);
            });
        }

        if (! Schema::hasTable('coupon_category')) {
            Schema::create('coupon_category', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['coupon_id', 'category_id']);
            });
        }

        if (! Schema::hasTable('coupon_vendor')) {
            Schema::create('coupon_vendor', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['coupon_id', 'vendor_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_vendor');
        Schema::dropIfExists('coupon_category');
        Schema::dropIfExists('coupon_product');
        Schema::dropIfExists('coupon_usages');
    }
};
