<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('kyc_status');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users');
            $table->text('kyc_rejection_reason')->nullable()->after('verified_by');
            $table->timestamp('kyc_rejected_at')->nullable()->after('kyc_rejection_reason');
            $table->foreignId('kyc_rejected_by')->nullable()->after('kyc_rejected_at')->constrained('users');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'verified_at',
                'verified_by',
                'kyc_rejection_reason',
                'kyc_rejected_at',
                'kyc_rejected_by'
            ]);
        });
    }
};
