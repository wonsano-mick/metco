<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_type_id')->constrained('account_types');
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->decimal('fee_amount', 15, 4);
            $table->string('currency', 3)->default('GHS');
            $table->enum('calculation_method', ['fixed', 'percentage', 'tiered'])->default('fixed');
            $table->decimal('percentage_rate', 7, 4)->nullable();
            $table->json('tiers')->nullable(); // For tiered fees
            $table->boolean('has_minimum_balance_waiver')->default(false);
            $table->decimal('minimum_balance_threshold', 15, 4)->nullable();
            $table->enum('charge_day', ['day_of_month', 'day_of_week', 'last_day'])->default('day_of_month');
            $table->integer('charge_day_value')->nullable(); // e.g., 1 for 1st of month, 5 for Friday
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
        Schema::dropIfExists('fee_configurations');
    }
};
