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
        Schema::create('transactions', function (Blueprint $table) {
            // Primary Key
            $table->id();

            // Tenant Context (for multi-tenancy)
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();

            // Transaction Identification
            $table->string('transaction_reference')->unique()->comment('Unique transaction reference number');
            $table->uuid('external_reference')->nullable()->comment('External system reference');

            // Transaction Details
            $table->enum('type', [
                'transfer',
                'deposit',
                'withdrawal',
                'bill_payment',
                'loan_payment',
                'interest_credit',
                'fee_charge',
                'reversal',
                'adjustment',
                'cheque_deposit',
                'cash_deposit',
                'wire_transfer',
                'atm_withdrawal',
                'online_transfer',
                'standing_order',
                'direct_debit'
            ])->default('transfer');

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
                'reversed',
                'on_hold',
                'requires_approval'
            ])->default('pending');

            $table->enum('category', [
                'transfer',
                'deposit',
                'withdrawal',
                'payment',
                'refund',
                'fee',
                'interest',
                'adjustment',
                'other'
            ])->default('transfer');

            // Financial Details
            $table->decimal('amount', 18, 4)->default(0);
            $table->decimal('fee_amount', 18, 4)->default(0)->comment('Transaction fee amount');
            $table->decimal('tax_amount', 18, 4)->default(0)->comment('Tax amount if applicable');
            $table->decimal('net_amount', 18, 4)->default(0)->comment('Amount after fees and taxes');
            $table->string('currency', 3)->default('USD');

            // Description and Metadata
            $table->string('description')->nullable();
            $table->text('notes')->nullable()->comment('Internal notes about the transaction');
            $table->json('metadata')->nullable()->comment('Additional transaction data');
            $table->json('failure_reason')->nullable()->comment('Details if transaction failed');

            // Parties Involved
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            // Account Relationships
            $table->foreignId('source_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('destination_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('beneficiary_id')->nullable()->constrained('beneficiaries')->nullOnDelete();

            // Timestamps
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamp('scheduled_for')->nullable()->comment('For scheduled transactions');
            $table->timestamp('expires_at')->nullable()->comment('Transaction expiry time');

            // Audit Trail
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_id')->nullable();
            $table->string('location')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for Performance
            $table->index(['type', 'status']);
            $table->index(['transaction_reference']);
            $table->index(['status', 'created_at']);
            $table->index(['source_account_id', 'created_at']);
            $table->index(['destination_account_id', 'created_at']);
            $table->index(['initiated_by', 'created_at']);
            $table->index(['completed_at']);
            $table->index(['type', 'status', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index(['external_reference']);
            $table->index(['scheduled_for']);
            $table->index(['expires_at']);

            // Compound indexes for common queries
            $table->index(['source_account_id', 'status', 'created_at']);
            $table->index(['destination_account_id', 'status', 'created_at']);
            $table->index(['initiated_by', 'status', 'created_at']);

            // Indexes for Performance
            // $table->index(['tenant_id', 'type', 'status']);
            // $table->index(['tenant_id', 'transaction_reference']);
            // $table->index(['tenant_id', 'status', 'created_at']);
            // $table->index(['tenant_id', 'source_account_id', 'created_at']);
            // $table->index(['tenant_id', 'destination_account_id', 'created_at']);
            // $table->index(['tenant_id', 'initiated_by', 'created_at']);
            // $table->index(['tenant_id', 'completed_at']);
            // $table->index(['tenant_id', 'type', 'status', 'created_at']);
            // $table->index(['tenant_id', 'category', 'created_at']);
            // $table->index(['external_reference']);
            // $table->index(['scheduled_for']);
            // $table->index(['expires_at']);

            // // Compound indexes for common queries
            // $table->index(['tenant_id', 'source_account_id', 'status', 'created_at']);
            // $table->index(['tenant_id', 'destination_account_id', 'status', 'created_at']);
            // $table->index(['tenant_id', 'initiated_by', 'status', 'created_at']);

            // Full-text index for search
            if (config('database.default') === 'mysql') {
                $table->fullText(['transaction_reference', 'description', 'notes']);
            }
        });

        // Create transaction sequences table for reference number generation
        Schema::create('transaction_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('general');
            $table->string('prefix')->default('TXN');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->year('year');
            $table->unsignedTinyInteger('month')->nullable();
            $table->unique(['tenant_id', 'type', 'year', 'month']);
            $table->timestamps();
        });


        Schema::create('beneficiary_transactions', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('beneficiary_id')->constrained('beneficiaries')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            // $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');

            // Transaction Details (denormalized for quick access)
            $table->string('transaction_reference');
            $table->decimal('amount', 18, 4);
            $table->string('currency', 3);
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('pending');
            $table->string('description')->nullable();
            $table->timestamp('transaction_date')->useCurrent();

            // Source Account
            $table->foreignId('source_ac_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('source_account_number')->nullable();

            // Indexes
            $table->index(['beneficiary_id', 'transaction_date']);
            // $table->index(['customer_id', 'beneficiary_id']);
            $table->index(['transaction_id']);
            $table->index(['source_ac_id', 'transaction_date']);

            // Unique constraint
            $table->unique(['beneficiary_id', 'transaction_id']);

            // $table->index(['tenant_id', 'beneficiary_id', 'transaction_date']);
            // $table->index(['tenant_id', 'customer_id', 'beneficiary_id']);
            // $table->index(['tenant_id', 'transaction_id']);
            // $table->index(['tenant_id', 'source_account_id', 'transaction_date']);

            // // Unique constraint
            // $table->unique(['tenant_id', 'beneficiary_id', 'transaction_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_sequences');
        Schema::dropIfExists('transactions');
    }
};
