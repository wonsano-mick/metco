<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert default transaction types
        $transactionTypes = [
            ['code' => 'TRANSFER', 'name' => 'Fund Transfer', 'description' => 'Transfer between accounts'],
            ['code' => 'DEPOSIT', 'name' => 'Deposit', 'description' => 'Money deposit into account'],
            ['code' => 'WITHDRAWAL', 'name' => 'Withdrawal', 'description' => 'Money withdrawal from account'],
            ['code' => 'BILL_PAYMENT', 'name' => 'Bill Payment', 'description' => 'Payment of bills'],
            ['code' => 'LOAN_PAYMENT', 'name' => 'Loan Payment', 'description' => 'Loan repayment'],
            ['code' => 'INTEREST_CREDIT', 'name' => 'Interest Credit', 'description' => 'Interest credited to account'],
            ['code' => 'FEE_CHARGE', 'name' => 'Fee Charge', 'description' => 'Service fee charge'],
            ['code' => 'REVERSAL', 'name' => 'Reversal', 'description' => 'Transaction reversal'],
            ['code' => 'ADJUSTMENT', 'name' => 'Adjustment', 'description' => 'Balance adjustment'],
        ];

        foreach ($transactionTypes as $type) {
            DB::table('transaction_types')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }

        // Insert default transaction categories
        $transactionCategories = [
            ['code' => 'TRANSFER', 'name' => 'Transfer', 'description' => 'Money transfers'],
            ['code' => 'DEPOSIT', 'name' => 'Deposit', 'description' => 'Cash or check deposits'],
            ['code' => 'WITHDRAWAL', 'name' => 'Withdrawal', 'description' => 'Cash withdrawals'],
            ['code' => 'PAYMENT', 'name' => 'Payment', 'description' => 'Bill payments'],
            ['code' => 'REFUND', 'name' => 'Refund', 'description' => 'Transaction refunds'],
            ['code' => 'FEE', 'name' => 'Fee', 'description' => 'Bank fees and charges'],
            ['code' => 'INTEREST', 'name' => 'Interest', 'description' => 'Interest payments'],
            ['code' => 'ADJUSTMENT', 'name' => 'Adjustment', 'description' => 'Balance adjustments'],
            ['code' => 'OTHER', 'name' => 'Other', 'description' => 'Other transactions'],
        ];

        foreach ($transactionCategories as $category) {
            DB::table('transaction_categories')->updateOrInsert(
                ['code' => $category['code']],
                $category
            );
        }

        // Insert default transaction statuses
        $transactionStatuses = [
            ['code' => 'PENDING', 'name' => 'Pending', 'description' => 'Transaction is pending processing'],
            ['code' => 'PROCESSING', 'name' => 'Processing', 'description' => 'Transaction is being processed'],
            ['code' => 'COMPLETED', 'name' => 'Completed', 'description' => 'Transaction completed successfully'],
            ['code' => 'FAILED', 'name' => 'Failed', 'description' => 'Transaction failed'],
            ['code' => 'CANCELLED', 'name' => 'Cancelled', 'description' => 'Transaction was cancelled'],
            ['code' => 'REVERSED', 'name' => 'Reversed', 'description' => 'Transaction was reversed'],
            ['code' => 'ON_HOLD', 'name' => 'On Hold', 'description' => 'Transaction is on hold'],
            ['code' => 'REQUIRES_APPROVAL', 'name' => 'Requires Approval', 'description' => 'Transaction requires approval'],
        ];

        foreach ($transactionStatuses as $status) {
            DB::table('transaction_statuses')->updateOrInsert(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
