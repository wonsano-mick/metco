<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Eloquent\AccountType;

class AccountTypeSeeder extends Seeder
{
    public function run(): void
    {
        $accountTypes = [
            [
                'code' => 'SAV',
                'name' => 'Savings Account',
                'description' => 'Basic savings account with interest',
                'min_balance' => 10.00,
                'max_balance' => 1000000.00,
                'interest_rate' => 2.5,
                'is_active' => true,
                'is_for_organizations' => false,
            ],
            [
                'code' => 'CAV',
                'name' => 'Current Account',
                'description' => 'Daily transactional account',
                'min_balance' => 25.00,
                'max_balance' => null,
                'interest_rate' => 0.5,
                'is_active' => true,
                'is_for_organizations' => false,
            ],
            [
                'code' => 'MDA',
                'name' => 'Meba Daakye Account',
                'description' => 'Catering for children account',
                'min_balance' => 1000.00,
                'max_balance' => 5000000.00,
                'interest_rate' => 3.75,
                'is_active' => true,
                'is_for_organizations' => false,
            ],
            [
                'code' => 'FDA',
                'name' => 'Fixed Deposit Account',
                'description' => 'Fixed-term deposit with higher interest',
                'min_balance' => 500.00,
                'max_balance' => 10000000.00,
                'interest_rate' => 4.25,
                'is_active' => true,
                'is_for_organizations' => false,
            ],
            [
                'name' => 'Organization Current Account',
                'code' => 'ORG_CURRENT',
                'description' => 'Current account for organizations',
                'interest_rate' => 0.5,
                'min_balance' => 1000.00,
                'max_balance' => 1000000.00,
                'is_for_organizations' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Business Savings Account',
                'code' => 'BIZ_SAVINGS',
                'description' => 'Savings account for small businesses',
                'interest_rate' => 2.5,
                'min_balance' => 500.00,
                'max_balance' => 500000.00,
                'is_for_organizations' => true,
                'is_active' => true,
            ],
        ];

        foreach ($accountTypes as $type) {
            AccountType::create($type);
        }
    }
}
