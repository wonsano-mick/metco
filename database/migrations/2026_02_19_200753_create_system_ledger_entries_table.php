<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_account_id')->constrained('system_accounts')->onDelete('restrict');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->enum('entry_type', ['debit', 'credit']);
            $table->decimal('amount', 15, 4);
            $table->string('currency', 3)->default('GHS');
            $table->decimal('balance_before', 15, 4)->default(0);
            $table->decimal('balance_after', 15, 4)->default(0);
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['system_account_id', 'created_at']);
            $table->index(['transaction_id']);
            $table->index(['entry_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_ledger_entries');
    }
};
