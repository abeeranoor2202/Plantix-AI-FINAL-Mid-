<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'owner_name')) {
                $table->string('owner_name', 255)->nullable()->after('author_id');
            }

            if (! Schema::hasColumn('vendors', 'business_email')) {
                $table->string('business_email', 255)->nullable()->after('owner_name');
            }

            if (! Schema::hasColumn('vendors', 'business_phone')) {
                $table->string('business_phone', 30)->nullable()->after('phone');
            }

            if (! Schema::hasColumn('vendors', 'tax_id')) {
                $table->string('tax_id', 100)->nullable()->after('business_phone');
            }

            if (! Schema::hasColumn('vendors', 'business_category')) {
                $table->string('business_category', 150)->nullable()->after('category_id');
            }

            if (! Schema::hasColumn('vendors', 'city')) {
                $table->string('city', 120)->nullable()->after('address');
            }

            if (! Schema::hasColumn('vendors', 'region')) {
                $table->string('region', 120)->nullable()->after('city');
            }

            if (! Schema::hasColumn('vendors', 'bank_name')) {
                $table->string('bank_name', 150)->nullable()->after('region');
            }

            if (! Schema::hasColumn('vendors', 'bank_account_name')) {
                $table->string('bank_account_name', 150)->nullable()->after('bank_name');
            }

            if (! Schema::hasColumn('vendors', 'bank_account_number')) {
                $table->string('bank_account_number', 100)->nullable()->after('bank_account_name');
            }

            if (! Schema::hasColumn('vendors', 'iban')) {
                $table->string('iban', 100)->nullable()->after('bank_account_number');
            }

            if (! Schema::hasColumn('vendors', 'cnic_document')) {
                $table->string('cnic_document', 500)->nullable()->after('iban');
            }

            if (! Schema::hasColumn('vendors', 'business_license_document')) {
                $table->string('business_license_document', 500)->nullable()->after('cnic_document');
            }

            if (! Schema::hasColumn('vendors', 'tax_certificate_document')) {
                $table->string('tax_certificate_document', 500)->nullable()->after('business_license_document');
            }

            if (! Schema::hasColumn('vendors', 'status')) {
                $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'suspended'])
                    ->default('pending')
                    ->after('is_approved');
            }

            if (! Schema::hasColumn('vendors', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            }

            if (! Schema::hasColumn('vendors', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('reviewed_by');
            }

            if (! Schema::hasColumn('vendors', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
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

            $table->index(['status', 'is_active', 'is_approved']);
            $table->index(['business_category', 'city', 'region']);
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $columns = [
                'owner_name', 'business_email', 'business_phone', 'tax_id', 'business_category',
                'city', 'region', 'bank_name', 'bank_account_name', 'bank_account_number', 'iban',
                'cnic_document', 'business_license_document', 'tax_certificate_document', 'status',
                'reviewed_by', 'submitted_at', 'reviewed_at', 'approved_at', 'rejected_at', 'suspended_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('vendors', $column)) {
                    if (in_array($column, ['reviewed_by'], true)) {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
