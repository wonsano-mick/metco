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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('teller_limit', 18, 4)->nullable()->after('status')
                ->comment('Per-transaction limit for teller');
            $table->decimal('daily_teller_limit', 18, 4)->nullable()->after('teller_limit')
                ->comment('Daily aggregate limit for teller');
            $table->boolean('can_approve_transactions')->default(false)->after('daily_teller_limit')
                ->comment('Whether user can approve transactions as supervisor');
            $table->foreignId('supervisor_id')->nullable()->after('can_approve_transactions')
                ->constrained('users')->nullOnDelete()
                ->comment('Default supervisor for this teller');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn([
                'teller_limit',
                'daily_teller_limit',
                'can_approve_transactions',
                'supervisor_id'
            ]);
        });
    }
};
