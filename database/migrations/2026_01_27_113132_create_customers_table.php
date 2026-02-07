<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->uuid('tenant_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->nullOnDelete();
            // $table->uuid('branch_id')->nullable()->index();
            // $table->uuid('relationship_manager_id')->nullable()->index(); // Bank employee assigned
            $table->foreignId('relationship_manager_id')->nullable()->nullOnDelete();
            $table->string('customer_number', 50)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('phone_alt', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('nationality', 100)->nullable();

            // Identification
            $table->enum('id_type', ['passport', 'national_id', 'drivers_license', 'voters_id'])->nullable();
            $table->string('id_number')->nullable();
            $table->date('id_expiry_date')->nullable();
            $table->string('id_issuing_country')->nullable();

            // Address
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country', 40)->default('Ghana');
            $table->string('postal_code', 20)->nullable();

            // Employment
            $table->string('occupation')->nullable();
            $table->string('employer_name')->nullable();
            $table->string('employer_address')->nullable();
            $table->decimal('monthly_income', 15, 4)->nullable();
            $table->string('source_of_income')->nullable();

            // Financial Profile
            $table->decimal('net_worth', 15, 4)->nullable();
            $table->string('risk_profile', 20)->nullable()->default('medium'); // low, medium, high
            $table->string('kyc_status', 20)->default('pending'); // pending, verified, rejected

            // Images/Photos
            $table->string('profile_photo_path')->nullable();
            $table->string('id_front_image_path')->nullable();
            $table->string('id_back_image_path')->nullable();
            $table->string('signature_image_path')->nullable();

            // Additional Info
            $table->string('marital_status', 20)->nullable(); // single, married, divorced, widowed
            $table->integer('dependents')->default(0);
            $table->string('education_level', 100)->nullable();
            $table->json('emergency_contacts')->nullable();
            $table->json('next_of_kin')->nullable();
            $table->json('additional_documents')->nullable();

            // Status
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended', 'closed'])->default('pending');
            $table->enum('customer_type', ['individual', 'organization', 'joint', 'minor', 'senior'])->default('individual');
            $table->enum('customer_tier', ['basic', 'premium', 'vip', 'private'])->default('basic');

            // Dates
            $table->dateTime('registered_at')->nullable();
            // $table->dateTime('verified_at')->nullable();
            $table->dateTime('last_reviewed_at')->nullable();

            // Metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            // $table->index(['tenant_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['customer_number', 'status']);
            $table->index(['email', 'status']);
            $table->index(['phone', 'status']);
            $table->index(['customer_type', 'status']);
            $table->index(['kyc_status', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
