<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('interest_configuration_id')->constrained('interest_configurations');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->string('interest_reference', 100)->unique();
            $table->decimal('amount', 15, 4);
            $table->decimal('interest_rate', 7, 4);
            $table->string('currency', 3);
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->enum('calculation_method', ['daily_balance', 'minimum_balance', 'average_daily_balance', 'tiered']);
            $table->date('period_start');
            $table->date('period_end');
            $table->date('posting_date');
            $table->date('processed_at')->nullable();
            $table->decimal('average_balance', 15, 4)->nullable();
            $table->decimal('minimum_balance', 15, 4)->nullable();
            $table->decimal('balance_before', 15, 4)->nullable();
            $table->decimal('balance_after', 15, 4)->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('calculation_details')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'status']);
            $table->index(['posting_date', 'status']);
            $table->index('interest_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_interests');
    }
};
