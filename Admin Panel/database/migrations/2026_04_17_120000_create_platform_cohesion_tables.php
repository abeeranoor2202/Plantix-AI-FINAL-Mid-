<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'reputation_score')) {
                $table->integer('reputation_score')->default(0)->after('wallet_amount')->index();
            }
            if (! Schema::hasColumn('users', 'reputation_level')) {
                $table->string('reputation_level', 32)->default('neutral')->after('reputation_score')->index();
            }
        });

        Schema::table('experts', function (Blueprint $table) {
            if (! Schema::hasColumn('experts', 'reputation_score')) {
                $table->integer('reputation_score')->default(0)->after('rating_avg')->index();
            }
            if (! Schema::hasColumn('experts', 'reputation_level')) {
                $table->string('reputation_level', 32)->default('neutral')->after('reputation_score')->index();
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'reputation_score')) {
                $table->integer('reputation_score')->default(0)->after('rating')->index();
            }
            if (! Schema::hasColumn('vendors', 'reputation_level')) {
                $table->string('reputation_level', 32)->default('neutral')->after('reputation_score')->index();
            }
        });

        Schema::create('platform_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->string('actor_role', 32)->nullable()->index();
            $table->string('action', 100)->index();
            $table->string('entity_type', 100)->nullable()->index();
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['action', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_activities');

        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'reputation_level')) {
                $table->dropColumn('reputation_level');
            }
            if (Schema::hasColumn('vendors', 'reputation_score')) {
                $table->dropColumn('reputation_score');
            }
        });

        Schema::table('experts', function (Blueprint $table) {
            if (Schema::hasColumn('experts', 'reputation_level')) {
                $table->dropColumn('reputation_level');
            }
            if (Schema::hasColumn('experts', 'reputation_score')) {
                $table->dropColumn('reputation_score');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'reputation_level')) {
                $table->dropColumn('reputation_level');
            }
            if (Schema::hasColumn('users', 'reputation_score')) {
                $table->dropColumn('reputation_score');
            }
        });
    }
};
