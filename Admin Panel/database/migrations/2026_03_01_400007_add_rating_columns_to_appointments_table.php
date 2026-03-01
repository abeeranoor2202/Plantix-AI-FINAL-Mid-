<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedTinyInteger('customer_rating')->nullable()->after('status')
                  ->comment('1–5 star rating left by the customer');
            $table->text('customer_review')->nullable()->after('customer_rating');
            $table->timestamp('rated_at')->nullable()->after('customer_review');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['customer_rating', 'customer_review', 'rated_at']);
        });
    }
};
