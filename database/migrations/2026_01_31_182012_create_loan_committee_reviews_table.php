<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_committee_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('reviewed_by')->constrained('users')->onDelete('cascade');

            $table->enum('decision', ['approve', 'reject', 'refer', 'hold']);
            $table->integer('score')->nullable(); // 1-10 score
            $table->enum('risk_level', ['low', 'medium', 'high', 'very_high']);
            $table->text('recommendation');
            $table->text('comments')->nullable();
            $table->json('conditions')->nullable(); // Conditions for approval

            $table->dateTime('reviewed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['loan_id', 'decision']);
            $table->index('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_committee_reviews');
    }
};
