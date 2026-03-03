<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('fee_configuration_id')->constrained('fee_configurations');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->string('fee_reference', 100)->unique();
            $table->decimal('amount', 15, 4);
            $table->string('currency', 3);
            $table->enum('status', ['pending', 'processed', 'failed', 'waived'])->default('pending');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->date('period_start');
            $table->date('period_end');
            $table->date('charge_date');
            $table->date('processed_at')->nullable();
            $table->decimal('balance_before', 15, 4)->nullable();
            $table->decimal('balance_after', 15, 4)->nullable();
            $table->boolean('waived')->default(false);
            $table->string('waiver_reason')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'status']);
            $table->index(['charge_date', 'status']);
            $table->index('fee_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_fees');
    }
};
