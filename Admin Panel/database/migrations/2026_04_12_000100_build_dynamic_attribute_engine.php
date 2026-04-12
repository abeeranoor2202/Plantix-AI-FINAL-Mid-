<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            if (! Schema::hasColumn('attributes', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('attributes', 'type')) {
                $table->string('type', 30)->default('text')->after('title');
            }

            if (! Schema::hasColumn('attributes', 'unit')) {
                $table->string('unit', 40)->nullable()->after('type');
            }
        });

        DB::table('attributes')
            ->whereNull('name')
            ->update(['name' => DB::raw('title')]);

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['attribute_id', 'value']);
            $table->index(['attribute_id', 'sort_order']);
        });

        Schema::create('category_attribute', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'attribute_id']);
        });

        Schema::table('product_attributes', function (Blueprint $table) {
            if (! Schema::hasColumn('product_attributes', 'attribute_id')) {
                $table->foreignId('attribute_id')->nullable()->after('product_id')
                    ->constrained('attributes')->nullOnDelete();
            }

            if (! Schema::hasColumn('product_attributes', 'value')) {
                $table->text('value')->nullable()->after('attribute_id');
            }

            if (! Schema::hasColumn('product_attributes', 'value_type')) {
                $table->string('value_type', 30)->nullable()->after('value');
            }
        });

        Schema::table('product_attributes', function (Blueprint $table) {
            $table->index(['attribute_id']);
            $table->unique(['product_id', 'attribute_id'], 'product_attribute_unique_product_attribute');
        });
    }

    public function down(): void
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            if (Schema::hasColumn('product_attributes', 'value_type')) {
                $table->dropColumn('value_type');
            }

            if (Schema::hasColumn('product_attributes', 'value')) {
                $table->dropColumn('value');
            }

            if (Schema::hasColumn('product_attributes', 'attribute_id')) {
                $table->dropForeign(['attribute_id']);
                $table->dropColumn('attribute_id');
            }

            $table->dropUnique('product_attribute_unique_product_attribute');
        });

        Schema::dropIfExists('category_attribute');
        Schema::dropIfExists('attribute_values');

        Schema::table('attributes', function (Blueprint $table) {
            if (Schema::hasColumn('attributes', 'unit')) {
                $table->dropColumn('unit');
            }

            if (Schema::hasColumn('attributes', 'type')) {
                $table->dropColumn('type');
            }

            if (Schema::hasColumn('attributes', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
