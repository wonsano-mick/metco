<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->enum('type', [
                'teller',           // Teller cash accounts
                'charges',          // Fee/charge accounts
                'fee_income',       // Fee income accounts (monthly fees, transaction fees)
                'interest_income',  // Interest income accounts
                'interest_expense', // Interest expense accounts
                'suspense',         // Suspense accounts
                'clearing',         // Clearing accounts
                'income',           // General income
                'expense',          // General expense
                'liability'         // Liability accounts (e.g. loan payables)
            ]);
            $table->string('currency', 3)->default('GHS');
            $table->decimal('balance', 15, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'currency']);
            $table->index('code');
        }); 

        // Create default system accounts
        DB::table('system_accounts')->insert([
            // Teller accounts (one per branch)
            ['code' => 'TELLER-CASH-001', 'name' => 'Main Teller Cash', 'type' => 'teller', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Main teller cash account', 'created_at' => now()],

            // Charge accounts
            ['code' => 'CHG-MAINT-001', 'name' => 'Account Maintenance Fees', 'type' => 'charges', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Monthly account maintenance fees', 'created_at' => now()],
            ['code' => 'CHG-TRANS-001', 'name' => 'Transaction Fees', 'type' => 'charges', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Transaction processing fees', 'created_at' => now()],
            ['code' => 'CHG-LATE-001', 'name' => 'Late Payment Fees', 'type' => 'charges', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Late payment penalties', 'created_at' => now()],
            ['code' => 'CHG-OVER-001', 'name' => 'Overdraft Fees', 'type' => 'charges', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Overdraft facility fees', 'created_at' => now()],
            ['code' => 'CHG-MONTHLY-001', 'name' => 'Monthly Fee Income', 'type' => 'charges', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Income from monthly fees', 'created_at' => now()],

            // Interest accounts
            ['code' => 'INT-INC-001', 'name' => 'Interest Income', 'type' => 'interest_income', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Interest earned on loans', 'created_at' => now()],
            ['code' => 'INT-EXP-001', 'name' => 'Interest Expense', 'type' => 'interest_expense', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Interest paid on deposits', 'created_at' => now()],

            // Suspense/Clearing
            ['code' => 'SUSP-001', 'name' => 'Suspense Account', 'type' => 'suspense', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Transactions awaiting clearing', 'created_at' => now()],
            ['code' => 'CLR-001', 'name' => 'Clearing Account', 'type' => 'clearing', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Cheque clearing', 'created_at' => now()],

            //loan payable account
            ['code' => 'LOAN-PAY-001', 'name' => 'Loan Payable Account', 'type' => 'liability', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Account for recording loan payables', 'created_at' => now()],

            // Main Cash account
            ['code' => 'CASH-001', 'name' => 'Main Cash Account', 'type' => 'cash', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Main cash account for the institution', 'created_at' => now()],

            //cheque account
            ['code' => 'CHEQUE-001', 'name' => 'Cheque Account', 'type' => 'clearing', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Account for processing cheque transactions', 'created_at' => now()],

            //Mobile money account
            ['code' => 'MM-001', 'name' => 'Mobile Money Account', 'type' => 'clearing', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Account for processing mobile money transactions', 'created_at' => now()],

            //bank bank account
            ['code' => 'BANK-001', 'name' => 'Bank Account', 'type' => 'clearing', 'currency' => 'GHS', 'balance' => 0, 'description' => 'Account for processing bank transfer transactions', 'created_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_accounts');
    }
};
