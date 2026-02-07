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
        Schema::create('ledger_entries', function (Blueprint $table) {
            // Primary Key
            $table->id();

            // Tenant Context
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();

            // Foreign Keys
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict');

            // Entry Details
            $table->enum('entry_type', ['credit', 'debit', 'adjustment', 'reversal']);
            $table->enum('category', [
                'principal',
                'interest',
                'fee',
                'tax',
                'commission',
                'penalty',
                'adjustment',
                'reversal',
                'other'
            ])->default('principal');

            // Financial Details
            $table->decimal('amount', 18, 4)->default(0);
            $table->decimal('running_balance', 18, 4)->default(0)->comment('Account balance after this entry');
            $table->decimal('available_balance_after', 18, 4)->default(0)->comment('Available balance after this entry');
            $table->decimal('ledger_balance_after', 18, 4)->default(0)->comment('Ledger balance after this entry');
            $table->string('currency', 3)->default('GHS');

            // Balances Before Transaction
            $table->decimal('balance_before', 18, 4)->default(0);
            $table->decimal('available_balance_before', 18, 4)->default(0);
            $table->decimal('ledger_balance_before', 18, 4)->default(0);

            // Description and Metadata
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->json('reversal_data')->nullable()->comment('Data for reversal entries');

            // Settlement Information
            $table->boolean('is_settled')->default(false);
            $table->timestamp('settled_at')->nullable();
            $table->string('settlement_reference')->nullable();

            // Reversal Information
            $table->boolean('is_reversed')->default(false);
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversal_entry_id')->nullable()->constrained('ledger_entries')->nullOnDelete();

            // Audit Information
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Timestamps
            $table->timestamp('entry_date')->useCurrent()->comment('Business date of entry');
            $table->timestamp('value_date')->useCurrent()->comment('Value date for accounting');
            $table->timestamps();

            // Soft Deletes
            $table->softDeletes();

            // Indexes for Performance
            // Standard Indexes (Short names)
            $table->index(['account_id', 'entry_date'], 'le_acc_date_idx');
            $table->index(['account_id', 'created_at'], 'le_acc_created_idx');
            $table->index(['transaction_id'], 'le_tx_idx');
            $table->index(['settlement_reference'], 'le_settle_ref_idx');
            $table->index(['reversal_entry_id'], 'le_rev_entry_idx');

            // Compound indexes (Explicitly shortened to avoid the 64-char error)
            $table->index(['account_id', 'entry_type', 'created_at'], 'le_acc_type_cr_idx');
            $table->index(['account_id', 'is_settled', 'created_at'], 'le_acc_set_cr_idx');
            $table->index(['account_id', 'is_reversed', 'created_at'], 'le_acc_rev_cr_idx');
            $table->index(['entry_type', 'created_at'], 'le_type_cr_idx');
            $table->index(['category', 'created_at'], 'le_cat_cr_idx');
            $table->index(['account_id', 'value_date'], 'le_acc_val_idx');

            // The "Long" Compound indexes (Most likely the current culprits)
            $table->index(['account_id', 'entry_type', 'category', 'created_at'], 'le_acc_type_cat_cr_idx');
            $table->index(['transaction_id', 'entry_type'], 'le_tx_type_idx');
            $table->index(['account_id', 'is_settled', 'is_reversed', 'created_at'], 'le_acc_set_rev_cr_idx');
        });

        // Create ledger entry batches table for batch processing
        Schema::create('ledger_entry_batches', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_reference')->unique();
            $table->string('batch_type')->default('daily');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'reversed'])->default('pending');
            $table->date('batch_date');
            $table->integer('entry_count')->default(0);
            $table->decimal('total_debits', 18, 4)->default(0);
            $table->decimal('total_credits', 18, 4)->default(0);
            $table->decimal('net_amount', 18, 4)->default(0);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['batch_date']);
            $table->index(['status', 'created_at']);
            // $table->index(['tenant_id', 'batch_date']);
            // $table->index(['tenant_id', 'status', 'created_at']);
            $table->index(['batch_reference']);
        });

        // Create ledger entry batch items table
        Schema::create('ledger_entry_batch_items', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->constrained('ledger_entry_batches')->onDelete('cascade');
            $table->foreignId('ledger_entry_id')->constrained('ledger_entries')->onDelete('cascade');
            $table->enum('status', ['pending', 'included', 'excluded', 'error'])->default('pending');
            $table->string('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['batch_id']);
            $table->index(['ledger_entry_id']);
            // $table->index(['tenant_id', 'batch_id']);
            // $table->index(['tenant_id', 'ledger_entry_id']);
            $table->unique(['batch_id', 'ledger_entry_id']);
        });

        // Create ledger summary table for reporting
        Schema::create('ledger_summaries', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->date('summary_date');
            $table->string('period_type')->default('daily'); // daily, weekly, monthly, quarterly, yearly

            // Opening balances
            $table->decimal('opening_balance', 18, 4)->default(0);
            $table->decimal('opening_available_balance', 18, 4)->default(0);
            $table->decimal('opening_ledger_balance', 18, 4)->default(0);

            // Transaction counts
            $table->integer('credit_count')->default(0);
            $table->integer('debit_count')->default(0);
            $table->integer('adjustment_count')->default(0);
            $table->integer('reversal_count')->default(0);

            // Amount totals
            $table->decimal('total_credits', 18, 4)->default(0);
            $table->decimal('total_debits', 18, 4)->default(0);
            $table->decimal('total_adjustments', 18, 4)->default(0);
            $table->decimal('total_reversals', 18, 4)->default(0);

            // Closing balances
            $table->decimal('closing_balance', 18, 4)->default(0);
            $table->decimal('closing_available_balance', 18, 4)->default(0);
            $table->decimal('closing_ledger_balance', 18, 4)->default(0);

            // Fees and charges
            $table->decimal('total_fees', 18, 4)->default(0);
            $table->decimal('total_interest', 18, 4)->default(0);
            $table->decimal('total_taxes', 18, 4)->default(0);

            // Metadata
            $table->json('metadata')->nullable();
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Unique constraint and indexes
            $table->unique(['account_id', 'summary_date', 'period_type']);
            $table->index(['account_id', 'summary_date']);
            $table->index(['summary_date', 'period_type']);
            $table->index(['account_id', 'period_type', 'summary_date']);
            // $table->unique(['tenant_id', 'account_id', 'summary_date', 'period_type']);
            // $table->index(['tenant_id', 'account_id', 'summary_date']);
            // $table->index(['tenant_id', 'summary_date', 'period_type']);
            // $table->index(['tenant_id', 'account_id', 'period_type', 'summary_date']);

            // Compound indexes for reporting
            $table->index(['summary_date', 'is_finalized']);
            $table->index(['account_id', 'is_finalized', 'summary_date']);
            // $table->index(['tenant_id', 'summary_date', 'is_finalized']);
            // $table->index(['tenant_id', 'account_id', 'is_finalized', 'summary_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_summaries');
        Schema::dropIfExists('ledger_entry_batch_items');
        Schema::dropIfExists('ledger_entry_batches');
        Schema::dropIfExists('ledger_entries');
    }
};
