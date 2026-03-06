<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('monthly_account_processings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->date('processing_month');
            $table->decimal('balance_before', 15, 2);
            $table->decimal('monthly_fee_applied', 15, 2)->default(0);
            $table->decimal('interest_earned', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2);
            $table->decimal('fee_transaction_id')->nullable();
            $table->decimal('interest_transaction_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'processing_month']);
            $table->index('processing_month');
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_account_processings');
    }
};
