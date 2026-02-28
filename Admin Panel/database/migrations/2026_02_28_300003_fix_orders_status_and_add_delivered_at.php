<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Timestamp when the order was marked as delivered
            // Required for the return-window enforcement (PLANTIX_RETURN_WINDOW_DAYS)
            $table->timestamp('delivered_at')->nullable()->after('estimated_delivery');

            // Fix the order status enum to use e-commerce states, not food-delivery states
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'rejected',
                'return_requested',
                'returned',
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivered_at');

            $table->enum('status', [
                'pending', 'accepted', 'preparing', 'ready',
                'driver_assigned', 'picked_up', 'delivered', 'rejected', 'cancelled',
            ])->default('pending')->change();
        });
    }
};
