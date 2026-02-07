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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id('id');
            // $table->foreignId('tenant_id')->nullable()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->nullOnDelete();
            $table->foreignId('account_type_id')->nullable()->nullOnDelete();
            $table->string('account_number', 50)->unique();
            $table->string('currency', 3)->default('GHS');
            $table->decimal('current_balance', 15, 4)->default(0);
            $table->decimal('available_balance', 15, 4)->default(0);
            $table->decimal('ledger_balance', 15, 4)->default(0);
            $table->decimal('overdraft_limit', 15, 4)->default(0);
            $table->decimal('minimum_balance', 15, 4)->default(0);
            $table->enum('status', ['active', 'frozen', 'closed', 'pending', 'dormant', 'suspended'])
                ->default('pending');
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('last_activity_at')->nullable();
            $table->json('metadata')->nullable(); // For additional data
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            // $table->index(['tenant_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['account_number', 'status']);
            $table->index(['status', 'last_activity_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
