<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add organization fields
            $table->string('company_name')->nullable()->after('last_name');
            $table->string('organization_type')->nullable()->after('company_name');
            $table->string('registration_number')->nullable()->after('organization_type');
            $table->string('tax_identification_number')->nullable()->after('registration_number');
            $table->string('industry')->nullable()->after('tax_identification_number');
            $table->text('business_nature')->nullable()->after('industry');
            $table->string('contact_person')->nullable()->after('business_nature');
            $table->json('authorized_signatories')->nullable()->after('contact_person');
            $table->boolean('is_blacklisted')->default(false)->after('authorized_signatories');
            $table->text('blacklist_reason')->nullable()->after('is_blacklisted');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'organization_type',
                'registration_number',
                'tax_identification_number',
                'industry',
                'business_nature',
                'contact_person',
                'authorized_signatories',
                'is_blacklisted',
                'blacklist_reason',
            ]);
        });
    }
};
