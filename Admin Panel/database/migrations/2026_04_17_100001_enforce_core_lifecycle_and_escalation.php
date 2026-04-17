<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'suspended', 'banned'])
                    ->default('active')
                    ->after('role');
                $table->index('status');
            }
        });

        DB::table('users')->where('is_banned', true)->update(['status' => 'banned']);
        DB::table('users')->where('active', false)->where('is_banned', false)->update(['status' => 'suspended']);
        DB::table('users')->where('active', true)->where('is_banned', false)->update(['status' => 'active']);

        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])
                    ->default('pending')
                    ->after('is_approved');
                $table->index('status');
            }

            if (! Schema::hasColumn('vendors', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('vendors', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            if (! Schema::hasColumn('vendors', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('reviewed_at');
            }

            if (! Schema::hasColumn('vendors', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('vendors', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('rejected_at');
            }
        });

        DB::table('vendors')->where('is_approved', true)->where('is_active', true)->update(['status' => 'approved']);
        DB::table('vendors')->where('is_approved', false)->where('is_active', true)->update(['status' => 'pending']);
        DB::table('vendors')->where('is_active', false)->where('is_approved', true)->update(['status' => 'suspended']);
        DB::table('vendors')->where('is_approved', false)->where('is_active', false)->update(['status' => 'rejected']);

        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'expert_rating')) {
                $table->unsignedTinyInteger('expert_rating')->nullable()->after('completed_at');
            }

            if (! Schema::hasColumn('appointments', 'expert_review')) {
                $table->text('expert_review')->nullable()->after('expert_rating');
            }
        });

        Schema::create('appointment_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('expert_id')->constrained('experts')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['expert_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'dispute_status')) {
                $table->enum('dispute_status', ['none', 'pending', 'vendor_responded', 'escalated', 'resolved', 'rejected', 'cancelled'])
                    ->default('none')
                    ->after('status');
                $table->index('dispute_status');
            }

            if (! Schema::hasColumn('orders', 'disputed_at')) {
                $table->timestamp('disputed_at')->nullable()->after('dispute_status');
            }

            if (! Schema::hasColumn('orders', 'dispute_reason')) {
                $table->text('dispute_reason')->nullable()->after('disputed_at');
            }

            if (! Schema::hasColumn('orders', 'vendor_dispute_response')) {
                $table->text('vendor_dispute_response')->nullable()->after('dispute_reason');
            }

            if (! Schema::hasColumn('orders', 'dispute_resolved_by')) {
                $table->foreignId('dispute_resolved_by')->nullable()->after('vendor_dispute_response')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'dispute_resolved_at')) {
                $table->timestamp('dispute_resolved_at')->nullable()->after('dispute_resolved_by');
            }

            if (! Schema::hasColumn('orders', 'dispute_admin_notes')) {
                $table->text('dispute_admin_notes')->nullable()->after('dispute_resolved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'dispute_admin_notes')) {
                $table->dropColumn('dispute_admin_notes');
            }
            if (Schema::hasColumn('orders', 'dispute_resolved_at')) {
                $table->dropForeign(['dispute_resolved_by']);
                $table->dropColumn(['dispute_resolved_by', 'dispute_resolved_at']);
            }
            if (Schema::hasColumn('orders', 'vendor_dispute_response')) {
                $table->dropColumn('vendor_dispute_response');
            }
            if (Schema::hasColumn('orders', 'dispute_reason')) {
                $table->dropColumn('dispute_reason');
            }
            if (Schema::hasColumn('orders', 'disputed_at')) {
                $table->dropColumn('disputed_at');
            }
            if (Schema::hasColumn('orders', 'dispute_status')) {
                $table->dropColumn('dispute_status');
            }
        });

        Schema::dropIfExists('appointment_feedback');

        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'expert_review')) {
                $table->dropColumn('expert_review');
            }
            if (Schema::hasColumn('appointments', 'expert_rating')) {
                $table->dropColumn('expert_rating');
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }
            if (Schema::hasColumn('vendors', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('vendors', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('vendors', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
            if (Schema::hasColumn('vendors', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
                $table->dropColumn('reviewed_by');
            }
            if (Schema::hasColumn('vendors', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};