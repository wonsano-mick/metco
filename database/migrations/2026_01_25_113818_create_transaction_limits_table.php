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
        Schema::create('transaction_limits', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_type_id')->constrained('account_types')->onDelete('cascade');

            // Limit Configuration
            $table->enum('transaction_type', [
                'transfer',
                'withdrawal',
                'deposit',
                'bill_payment',
                'loan_payment',
                'atm_withdrawal',
                'online_transfer',
                'international_transfer'
            ])->default('transfer');

            $table->enum('period', [
                'per_transaction',
                'daily',
                'weekly',
                'monthly',
                'quarterly',
                'yearly'
            ])->default('per_transaction');

            // Limits
            $table->decimal('min_amount', 18, 4)->nullable()->comment('Minimum transaction amount');
            $table->decimal('max_amount', 18, 4)->nullable()->comment('Maximum transaction amount');
            $table->integer('max_count')->nullable()->comment('Maximum number of transactions in period');
            $table->decimal('daily_limit', 18, 4)->nullable()->comment('Daily aggregate limit');
            $table->decimal('weekly_limit', 18, 4)->nullable()->comment('Weekly aggregate limit');
            $table->decimal('monthly_limit', 18, 4)->nullable()->comment('Monthly aggregate limit');

            // Time Restrictions
            $table->time('start_time')->nullable()->comment('Start time for allowed transactions');
            $table->time('end_time')->nullable()->comment('End time for allowed transactions');
            $table->json('allowed_days')->nullable()->comment('Array of allowed days (0=Sunday, 6=Saturday)');

            // Channel Restrictions
            $table->json('allowed_channels')->nullable()->comment('Allowed transaction channels');
            $table->json('restricted_channels')->nullable()->comment('Restricted transaction channels');

            // Status and Metadata
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0)->comment('Priority for rule application');
            $table->json('metadata')->nullable();
            $table->text('description')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['account_type_id', 'transaction_type']);
            $table->index(['account_type_id', 'period']);
            $table->index(['is_active', 'transaction_type']);
            $table->index(['account_type_id', 'is_active']);
            // $table->index(['tenant_id', 'account_type_id', 'transaction_type']);
            // $table->index(['tenant_id', 'account_type_id', 'period']);
            // $table->index(['tenant_id', 'is_active', 'transaction_type']);
            // $table->index(['tenant_id', 'account_type_id', 'is_active']);

            // Unique constraint
            $table->unique(
                // ['tenant_id', 'account_type_id', 'transaction_type', 'period'],
                ['account_type_id', 'transaction_type', 'period'],
                'unique_limit_per_account_type_transaction_period'
            );
        });

        // Create transaction limit overrides table for specific customers/accounts
        Schema::create('trans_limit_over', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('limit_id')->constrained('transaction_limits')->onDelete('cascade');
            $table->morphs('overridable'); // Can be for Account, Customer, or User

            // Override Values
            $table->decimal('override_min_amount', 18, 4)->nullable();
            $table->decimal('override_max_amount', 18, 4)->nullable();
            $table->integer('override_max_count')->nullable();
            $table->decimal('override_daily_limit', 18, 4)->nullable();
            $table->decimal('override_weekly_limit', 18, 4)->nullable();
            $table->decimal('override_monthly_limit', 18, 4)->nullable();

            // Override Status
            $table->boolean('is_active')->default(true);
            $table->enum('override_type', ['increase', 'decrease', 'custom'])->default('custom');
            $table->json('metadata')->nullable();

            // Effective Dates
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_to')->nullable();
            $table->boolean('is_permanent')->default(false);

            // Approval
            $table->boolean('requires_approval')->default(false);
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['limit_id', 'overridable_type', 'overridable_id']);
            $table->index(['is_active', 'approval_status']);
            $table->index(['effective_from', 'effective_to']);
            $table->index(['overridable_type', 'overridable_id', 'is_active']);

            // $table->index(['tenant_id', 'limit_id', 'overridable_type', 'overridable_id']);
            // $table->index(['tenant_id', 'is_active', 'approval_status']);
            // $table->index(['tenant_id', 'effective_from', 'effective_to']);
            // $table->index(['tenant_id', 'overridable_type', 'overridable_id', 'is_active']);

            // Unique constraint
            $table->unique(
                ['limit_id', 'overridable_type', 'overridable_id'],
                'unique_limit_override'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_limit_overrides');
        Schema::dropIfExists('transaction_limits');
    }
};
