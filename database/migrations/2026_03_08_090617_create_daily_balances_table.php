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
        Schema::create('daily_balances', function (Blueprint $table) {
            $table->id();

            // Account relationship
            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();

            // Date the balance applies to
            $table->date('balance_date');

            // Opening balance at start of the day
            $table->decimal('opening_balance', 18, 2);

            // Closing balance after all transactions
            $table->decimal('closing_balance', 18, 2);

            $table->timestamps();

            // Prevent duplicate daily records
            $table->unique(['account_id', 'balance_date']);
            $table->index(['account_id', 'balance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_balances');
    }
};
