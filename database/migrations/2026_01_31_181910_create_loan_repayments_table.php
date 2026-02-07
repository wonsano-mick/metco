<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            
            $table->integer('installment_number');
            $table->date('due_date');
            $table->dateTime('paid_date')->nullable();
            
            // Amounts
            $table->decimal('principal_amount', 15, 4);
            $table->decimal('interest_amount', 15, 4);
            $table->decimal('penalty_amount', 15, 4)->default(0);
            $table->decimal('late_fee', 10, 4)->default(0);
            $table->decimal('total_due', 15, 4);
            $table->decimal('amount_paid', 15, 4)->default(0);
            $table->decimal('remaining_balance', 15, 4)->default(0);
            
            // Payment Info
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue', 'waived', 'written_off']);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'mobile_money', 'direct_debit'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['loan_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};