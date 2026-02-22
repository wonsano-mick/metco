<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->decimal('monthly_fee', 10, 4)->default(0)->after('interest_rate');
            $table->enum('type', ['savings', 'checking', 'loan', 'investment', 'other'])
                ->default('savings')->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->dropColumn(['monthly_fee', 'type']);
        });
    }
};
