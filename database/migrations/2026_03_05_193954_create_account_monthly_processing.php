<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_monthly_processing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_type_id')->constrained();
            $table->decimal('monthly_fee_applied', 15, 4)->default(0);
            $table->decimal('interest_earned', 15, 4)->default(0);
            $table->decimal('balance_before', 15, 4);
            $table->decimal('balance_after', 15, 4);
            $table->date('processing_month'); // Stores the month being processed (e.g., 2024-01-01)
            $table->timestamp('processed_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Ensure we don't process the same account twice for the same month
            $table->unique(['account_id', 'processing_month'], 'unique_account_month');

            // Indexes for querying
            $table->index(['processing_month', 'account_id']);
            $table->index(['account_id', 'processed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_monthly_processing');
    }
};
