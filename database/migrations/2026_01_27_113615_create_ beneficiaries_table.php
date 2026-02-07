<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beneficiaries', function (Blueprint $table) {
            // Primary Key
            $table->id();

            // Tenant Context
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();

            // Beneficiary Owner (Customer/User who saved this beneficiary)
            // $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');

            // Beneficiary Identification
            $table->string('beneficiary_reference')->unique()->comment('Internal reference for beneficiary');
            $table->string('nickname')->nullable()->comment('User-given nickname for easy identification');
            $table->enum('beneficiary_type', [
                'internal',      // Same bank, different account
                'domestic',      // Different bank, same country
                'international', // Different bank, different country
                'wallet',        // Mobile wallet
                'bill',          // Utility bill payment
                'government',    // Tax, license payments
                'merchant',      // Merchant payments
                'other'
            ])->default('internal');

            // Personal/Business Information
            $table->enum('entity_type', ['individual', 'business', 'organization', 'government'])->default('individual');
            $table->string('full_name');
            $table->string('business_name')->nullable()->comment('For business/organization beneficiaries');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_country_code', 5)->nullable();

            // Address Information
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country', 2)->nullable()->comment('ISO 3166-1 alpha-2 country code');
            $table->string('postal_code')->nullable();

            // Account/Bank Information (varies by beneficiary type)
            // $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->string('bank_name')->nullable();
            $table->string('bank_code')->nullable()->comment('Routing number, sort code, BIC, etc.');
            $table->string('branch_code')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('account_number');
            $table->string('account_name')->nullable();
            $table->string('account_type')->nullable()->comment('Savings, Current, Credit Card, etc.');
            $table->string('iban')->nullable()->comment('International Bank Account Number');
            $table->string('swift_bic')->nullable()->comment('SWIFT/BIC code');
            $table->string('routing_number')->nullable()->comment('ABA routing number (US)');
            $table->string('sort_code')->nullable()->comment('UK sort code');
            $table->string('ifsc_code')->nullable()->comment('Indian Financial System Code');
            $table->string('bsb_code')->nullable()->comment('Bank State Branch (Australia)');

            // For internal transfers within same bank
            // $table->foreignId('internal_account_id')->nullable()->constrained('accounts')->nullOnDelete();

            // For wallet transfers
            $table->string('wallet_provider')->nullable()->comment('Mobile money provider');
            $table->string('wallet_number')->nullable();

            // For bill payments
            $table->string('bill_type')->nullable()->comment('Electricity, Water, Internet, etc.');
            $table->string('bill_account_number')->nullable();
            $table->string('bill_reference')->nullable();
            $table->json('bill_metadata')->nullable()->comment('Additional bill-specific data');

            // Verification Status
            $table->enum('verification_status', [
                'pending',
                'verified',
                'failed',
                'expired',
                'suspended'
            ])->default('pending');

            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verification_notes')->nullable();
            $table->string('verification_method')->nullable()->comment('How was this beneficiary verified');

            // Security Features
            $table->integer('max_transaction_amount')->nullable()->comment('Maximum allowed per transaction');
            $table->integer('daily_limit')->nullable()->comment('Maximum daily transfers to this beneficiary');
            $table->integer('monthly_limit')->nullable()->comment('Maximum monthly transfers');
            $table->boolean('requires_2fa')->default(false)->comment('Requires 2FA for transfers');
            $table->boolean('requires_approval')->default(false)->comment('Requires manual approval');

            // Usage Statistics
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_amount_transferred', 18, 4)->default(0);
            $table->timestamp('first_used_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('last_failed_at')->nullable();

            // Status and Metadata
            $table->boolean('is_active')->default(true);
            $table->boolean('is_favorite')->default(false);
            $table->enum('status', ['active', 'inactive', 'suspended', 'deleted'])->default('active');
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable()->comment('Customer notes about this beneficiary');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for Performance
            // $table->index(['customer_id', 'beneficiary_type']);
            // $table->index(['customer_id', 'is_active']);
            // $table->index(['customer_id', 'is_favorite']);
            // $table->index(['customer_id', 'status']);
            // $table->index(['beneficiary_reference']);
            // $table->index(['account_number', 'bank_id']);
            $table->index(['iban']);
            $table->index(['swift_bic']);
            $table->index(['verification_status']);
            // $table->index(['internal_account_id']);
            // $table->index(['bank_id', 'account_number']);

            // Compound indexes for common queries
            // $table->index(['customer_id', 'beneficiary_type', 'is_active']);
            // $table->index(['customer_id', 'verification_status', 'is_active']);
            // $table->index(['customer_id', 'last_used_at']);

            // Indexes for Performance
            // $table->index(['tenant_id', 'customer_id', 'beneficiary_type']);
            // $table->index(['tenant_id', 'customer_id', 'is_active']);
            // $table->index(['tenant_id', 'customer_id', 'is_favorite']);
            // $table->index(['tenant_id', 'customer_id', 'status']);
            // $table->index(['tenant_id', 'beneficiary_reference']);
            // $table->index(['tenant_id', 'account_number', 'bank_id']);
            // $table->index(['tenant_id', 'iban']);
            // $table->index(['tenant_id', 'swift_bic']);
            // $table->index(['tenant_id', 'verification_status']);
            // $table->index(['tenant_id', 'internal_account_id']);
            // $table->index(['tenant_id', 'bank_id', 'account_number']);

            // // Compound indexes for common queries
            // $table->index(['tenant_id', 'customer_id', 'beneficiary_type', 'is_active']);
            // $table->index(['tenant_id', 'customer_id', 'verification_status', 'is_active']);
            // $table->index(['tenant_id', 'customer_id', 'last_used_at']);

            // Full-text index for search
            if (config('database.default') === 'mysql') {
                // We provide 'beneficiaries_search_fulltext' as a custom, shorter name
                $table->fullText(
                    ['nickname', 'full_name', 'business_name', 'account_number', 'account_name'],
                    'beneficiaries_search_index'
                );
            }
        });

        // Create beneficiary verification logs
        Schema::create('beneficiary_verification_logs', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('beneficiary_id')->constrained('beneficiaries')->onDelete('cascade');
            $table->enum('verification_type', [
                'initial',
                'periodic',
                'transaction',
                'manual',
                'system'
            ])->default('initial');

            $table->enum('status', ['pending', 'success', 'failed', 'expired'])->default('pending');
            $table->text('details')->nullable();
            $table->json('response_data')->nullable();
            $table->string('verification_method')->nullable();
            $table->string('reference_id')->nullable()->comment('External verification reference');

            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index(['beneficiary_id', 'status']);
            $table->index(['verification_type', 'created_at']);
            // $table->index(['tenant_id', 'beneficiary_id', 'status']);
            // $table->index(['tenant_id', 'verification_type', 'created_at']);
            $table->index(['reference_id']);
        });

        // Create beneficiary limits (customer-specific limits for beneficiaries)
        Schema::create('beneficiary_limits', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            // $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('beneficiary_id')->nullable()->constrained('beneficiaries')->onDelete('cascade');

            // Limit Scope (if beneficiary_id is null, these are default limits for all beneficiaries)
            $table->enum('limit_type', [
                'per_beneficiary',
                'per_beneficiary_type',
                'all_beneficiaries',
                'new_beneficiary'
            ])->default('per_beneficiary');

            // Limits
            $table->integer('max_beneficiaries')->nullable()->comment('Maximum number of beneficiaries allowed');
            $table->integer('max_new_per_day')->nullable()->comment('Maximum new beneficiaries per day');
            $table->integer('max_new_per_week')->nullable()->comment('Maximum new beneficiaries per week');
            $table->integer('max_new_per_month')->nullable()->comment('Maximum new beneficiaries per month');

            // Transaction Limits
            $table->decimal('max_amount_per_transaction', 18, 4)->nullable();
            $table->decimal('max_amount_per_day', 18, 4)->nullable();
            $table->decimal('max_amount_per_week', 18, 4)->nullable();
            $table->decimal('max_amount_per_month', 18, 4)->nullable();

            // Count Limits
            $table->integer('max_transactions_per_day')->nullable();
            $table->integer('max_transactions_per_week')->nullable();
            $table->integer('max_transactions_per_month')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->text('description')->nullable();

            // Effective Dates
            $table->timestamp('effective_from')->useCurrent();
            $table->timestamp('effective_to')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            // $table->index(['customer_id', 'beneficiary_id']);
            // $table->index(['customer_id', 'limit_type']);
            // $table->index(['customer_id', 'is_active']);
            // $table->unique(
            //     ['customer_id', 'beneficiary_id', 'limit_type'],
            //     'unique_beneficiary_limit'
            // );

            // $table->index(['tenant_id', 'customer_id', 'beneficiary_id']);
            // $table->index(['tenant_id', 'customer_id', 'limit_type']);
            // $table->index(['tenant_id', 'customer_id', 'is_active']);
            // $table->unique(
            //     ['tenant_id', 'customer_id', 'beneficiary_id', 'limit_type'],
            //     'unique_beneficiary_limit'
            // );
        });

        // Create beneficiary transaction history (quick access to transactions with this beneficiary)
        // Schema::create('beneficiary_transactions', function (Blueprint $table) {
        //     $table->id();
        //     // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
        //     $table->foreignId('beneficiary_id')->constrained('beneficiaries')->onDelete('cascade');
        //     $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
        //     $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');

        //     // Transaction Details (denormalized for quick access)
        //     $table->string('transaction_reference');
        //     $table->decimal('amount', 18, 4);
        //     $table->string('currency', 3);
        //     $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('pending');
        //     $table->string('description')->nullable();
        //     $table->timestamp('transaction_date')->useCurrent();

        //     // Source Account
        //     $table->foreignId('source_account_id')->nullable()->constrained('accounts')->nullOnDelete();
        //     $table->string('source_account_number')->nullable();

        //     // Indexes
        //     $table->index(['beneficiary_id', 'transaction_date']);
        //     $table->index(['customer_id', 'beneficiary_id']);
        //     $table->index(['transaction_id']);
        //     $table->index(['source_account_id', 'transaction_date']);

        //     // Unique constraint
        //     $table->unique(['beneficiary_id', 'transaction_id']);

        //     // $table->index(['tenant_id', 'beneficiary_id', 'transaction_date']);
        //     // $table->index(['tenant_id', 'customer_id', 'beneficiary_id']);
        //     // $table->index(['tenant_id', 'transaction_id']);
        //     // $table->index(['tenant_id', 'source_account_id', 'transaction_date']);

        //     // // Unique constraint
        //     // $table->unique(['tenant_id', 'beneficiary_id', 'transaction_id']);

        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_transactions');
        Schema::dropIfExists('beneficiary_limits');
        Schema::dropIfExists('beneficiary_verification_logs');
        Schema::dropIfExists('beneficiaries');
    }
};
