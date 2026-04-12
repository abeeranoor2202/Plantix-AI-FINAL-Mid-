<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'text_review_enabled')) {
                $table->boolean('text_review_enabled')->default(true)->after('sort_order');
            }

            if (! Schema::hasColumn('categories', 'image_review_enabled')) {
                $table->boolean('image_review_enabled')->default(false)->after('text_review_enabled');
            }
        });

        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'title')) {
                $table->string('title')->nullable()->after('order_id');
            }

            if (! Schema::hasColumn('reviews', 'review_images')) {
                $table->json('review_images')->nullable()->after('comment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'review_images')) {
                $table->dropColumn('review_images');
            }

            if (Schema::hasColumn('reviews', 'title')) {
                $table->dropColumn('title');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'image_review_enabled')) {
                $table->dropColumn('image_review_enabled');
            }

            if (Schema::hasColumn('categories', 'text_review_enabled')) {
                $table->dropColumn('text_review_enabled');
            }
        });
    }
};
