<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RolesAndPermissionsSeeder::class,
            AccountTypeSeeder::class,
            // BranchSeeder::class,
            // CustomerSeeder::class,
            // TransactionSeeder::class,
            // Add other seeders here
        ]);
    }
}
