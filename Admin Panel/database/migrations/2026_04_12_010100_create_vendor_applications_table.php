<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('application_number', 40)->unique();
            $table->string('business_name');
            $table->string('owner_name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('cnic_tax_id', 100)->nullable();
            $table->string('business_category', 150)->nullable();
            $table->text('business_address')->nullable();
            $table->string('city', 120)->nullable();
            $table->string('region', 120)->nullable();
            $table->string('bank_name', 150)->nullable();
            $table->string('bank_account_name', 150)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('iban', 100)->nullable();
            $table->string('cnic_document', 500)->nullable();
            $table->string('business_license_document', 500)->nullable();
            $table->string('tax_certificate_document', 500)->nullable();
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'suspended'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['business_category', 'city', 'region']);
            $table->index('user_id');
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_applications');
    }
};
