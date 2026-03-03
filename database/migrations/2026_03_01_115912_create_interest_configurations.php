<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_type_id')->constrained('account_types');
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->decimal('interest_rate', 7, 4); // e.g., 2.5000 for 2.5%
            $table->enum('calculation_method', ['daily_balance', 'minimum_balance', 'average_daily_balance', 'tiered']);
            $table->enum('posting_method', ['compound', 'simple']);
            $table->integer('compound_frequency_days')->nullable(); // For compound interest
            $table->json('tiers')->nullable(); // For tiered interest rates
            $table->decimal('minimum_balance_required', 15, 4)->nullable();
            $table->decimal('maximum_balance_limit', 15, 4)->nullable();
            $table->enum('interest_day', ['day_of_month', 'day_of_week', 'last_day'])->default('day_of_month');
            $table->integer('interest_day_value')->nullable(); // e.g., 1 for 1st of month
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_type_id', 'is_active']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interest_configurations');
    }
};
