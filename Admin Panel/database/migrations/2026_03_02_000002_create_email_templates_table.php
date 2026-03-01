<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100)->unique(); // new_order_placed, new_vendor_signup, etc.
            $table->string('subject');
            $table->longText('body')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('firebase_doc_id', 191)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
