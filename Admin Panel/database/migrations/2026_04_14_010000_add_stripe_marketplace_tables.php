<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'appointment_id')) {
                $table->foreignId('appointment_id')->nullable()->after('order_id')->constrained('appointments')->nullOnDelete();
            }

            if (! Schema::hasColumn('payments', 'payment_type')) {
                $table->string('payment_type', 50)->default('product')->after('gateway');
            }

            if (! Schema::hasColumn('payments', 'stripe_session_id')) {
                $table->string('stripe_session_id', 255)->nullable()->after('gateway_transaction_id');
                $table->index('stripe_session_id');
            }

            if (! Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id', 255)->nullable()->after('stripe_session_id');
                $table->index('stripe_payment_intent_id');
            }

            if (! Schema::hasColumn('payments', 'stripe_charge_id')) {
                $table->string('stripe_charge_id', 255)->nullable()->after('stripe_payment_intent_id');
            }

            if (! Schema::hasColumn('payments', 'stripe_transfer_id')) {
                $table->string('stripe_transfer_id', 255)->nullable()->after('stripe_charge_id');
            }

            if (! Schema::hasColumn('payments', 'platform_commission')) {
                $table->decimal('platform_commission', 12, 2)->default(0)->after('amount');
            }

            if (! Schema::hasColumn('payments', 'net_amount')) {
                $table->decimal('net_amount', 12, 2)->default(0)->after('platform_commission');
            }

            if (! Schema::hasColumn('payments', 'stripe_account_id')) {
                $table->string('stripe_account_id', 255)->nullable()->after('net_amount');
            }

            if (! Schema::hasColumn('payments', 'metadata')) {
                $table->json('metadata')->nullable()->after('gateway_response');
            }
        });

        if (! Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->enum('type', ['credit', 'debit']);
                $table->decimal('amount', 12, 2);
                $table->decimal('balance', 12, 2);
                $table->string('description', 255)->nullable();
                $table->string('firebase_doc_id', 191)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index('user_id');
                $table->index('order_id');
            });
        }

        if (! Schema::hasTable('payouts')) {
            Schema::create('payouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->foreignId('expert_id')->nullable()->constrained('experts')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
                $table->string('payment_type', 50)->nullable();
                $table->decimal('amount', 12, 2);
                $table->decimal('commission', 12, 2)->default(0);
                $table->decimal('net_amount', 12, 2)->default(0);
                $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'cancelled'])->default('pending');
                $table->string('method', 50)->nullable();
                $table->string('stripe_transfer_id', 255)->nullable();
                $table->string('stripe_payout_id', 255)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamps();

                $table->index(['vendor_id', 'status']);
                $table->index(['expert_id', 'status']);
                $table->index('payment_id');
            });
        }

        if (! Schema::hasTable('stripe_accounts')) {
            Schema::create('stripe_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('accountable_type', 100)->nullable();
                $table->unsignedBigInteger('accountable_id')->nullable();
                $table->string('stripe_account_id', 255)->unique();
                $table->string('onboarding_status', 50)->default('pending');
                $table->boolean('charges_enabled')->default(false);
                $table->boolean('payouts_enabled')->default(false);
                $table->boolean('details_submitted')->default(false);
                $table->string('country', 10)->nullable();
                $table->string('email', 191)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('last_onboarded_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'onboarding_status']);
            });
        }

        Schema::table('experts', function (Blueprint $table) {
            if (! Schema::hasColumn('experts', 'stripe_account_id')) {
                $table->string('stripe_account_id', 255)->nullable()->after('consultation_duration_minutes');
            }

            if (! Schema::hasColumn('experts', 'stripe_account_status')) {
                $table->string('stripe_account_status', 50)->default('pending')->after('stripe_account_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            if (Schema::hasColumn('experts', 'stripe_account_status')) {
                $table->dropColumn('stripe_account_status');
            }

            if (Schema::hasColumn('experts', 'stripe_account_id')) {
                $table->dropColumn('stripe_account_id');
            }
        });

        Schema::dropIfExists('stripe_accounts');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('wallet_transactions');

        Schema::table('payments', function (Blueprint $table) {
            $drops = [];
            foreach (['appointment_id', 'payment_type', 'stripe_session_id', 'stripe_payment_intent_id', 'stripe_charge_id', 'stripe_transfer_id', 'platform_commission', 'net_amount', 'stripe_account_id', 'metadata'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $drops[] = $column;
                }
            }

            if ($drops) {
                $table->dropColumn($drops);
            }
        });
    }
};