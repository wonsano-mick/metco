<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('loan_officer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('disbursement_account_id')->nullable()->constrained('accounts')->onDelete('set null');

            $table->string('loan_number')->unique();
            $table->enum('loan_type', ['personal', 'mortgage', 'funeral', 'business', 'auto', 'education', 'agriculture', 'emergency']);
            $table->text('purpose');

            // Financial Details
            $table->decimal('amount', 15, 4);
            $table->decimal('interest_rate', 8, 4);
            $table->enum('interest_type', ['fixed', 'reducing', 'flat']);
            $table->integer('term_months');
            $table->enum('repayment_frequency', ['monthly', 'biweekly', 'weekly', 'quarterly']);

            // Dates
            $table->date('start_date');
            $table->date('end_date');
            $table->date('disbursement_date')->nullable();
            $table->date('next_payment_date')->nullable();

            // Status
            $table->enum('status', ['draft', 'pending', 'under_review', 'committee_review', 'approved', 'rejected', 'disbursed', 'active', 'completed', 'defaulted', 'written_off']);
            $table->enum('application_status', ['new', 'processing', 'verified', 'documented']);
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'referred']);
            $table->enum('committee_status', ['pending', 'reviewed', 'recommended', 'not_recommended']);

            // Financial Totals
            $table->decimal('total_interest', 15, 4)->default(0);
            $table->decimal('total_amount', 15, 4)->default(0);
            $table->decimal('remaining_balance', 15, 4)->default(0);
            $table->decimal('amount_paid', 15, 4)->default(0);

            // Fees & Penalties
            $table->decimal('penalty_rate', 8, 4)->default(0);
            $table->decimal('late_payment_fee', 10, 4)->default(0);
            $table->decimal('processing_fee', 10, 4)->default(0);
            $table->decimal('insurance_fee', 10, 4)->default(0);

            // Collateral & Guarantors
            $table->decimal('collateral_value', 15, 4)->nullable();
            $table->json('collateral_details')->nullable();
            $table->json('guarantors')->nullable();

            // Notes & Metadata
            $table->text('committee_notes')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('disbursement_method', ['bank_transfer', 'cash', 'cheque', 'mobile_money'])->nullable();
            $table->json('metadata')->nullable();

            // Timestamps
            $table->dateTime('application_date');
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('disbursed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('defaulted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['customer_id', 'status']);
            $table->index(['loan_officer_id', 'status']);
            $table->index(['status', 'next_payment_date']);
            $table->index('loan_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};