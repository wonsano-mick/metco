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
        Schema::create('account_types', function (Blueprint $table) {
            $table->id('id');
            $table->boolean('is_for_organizations')->default(false);
            $table->string('code', 20)->unique(); 
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('min_balance', 15, 4)->default(0);
            $table->decimal('max_balance', 15, 4)->nullable();
            $table->decimal('interest_rate', 8, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable(); // Optional: additional features
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            // $table->index(['tenant_id', 'is_active']);
            $table->index(['code', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
