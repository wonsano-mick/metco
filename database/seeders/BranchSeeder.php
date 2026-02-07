<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Eloquent\Branch;
use App\Models\Eloquent\User;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing branches
        DB::table('branches')->truncate();

        // Get or create a branch manager for Goaso
        $branchManager = $this->getOrCreateBranchManager();

        // Create Goaso Main Branch
        Branch::create([
            'id' => 1,
            'name' => 'Goaso Main Branch',
            'code' => 'GOASO001',
            'email' => 'goaso.main@metcu.com',
            'phone' => '+233352012345',
            'address' => 'Market Circle, Opposite Goaso Government Hospital',
            'city' => 'Goaso',
            'state' => 'Ahafo Region',
            'zip_code' => '00233',
            'country' => 'Ghana',
            'user_id' => $branchManager->id,
            'latitude' => 6.8036, // Approximate coordinates for Goaso
            'longitude' => -2.5176,
            'opening_time' => '08:00:00',
            'closing_time' => '17:00:00',
            'status' => 'active',
            'settings' => [
                'branch_type' => 'full_service',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'services' => [
                    'personal_banking',
                    'business_banking',
                    'loans',
                    'mortgages',
                    'investment_services',
                    'foreign_exchange',
                    'atm_services',
                    'safe_deposit_boxes',
                    'mobile_banking_registration',
                    'card_services',
                    'cheque_processing',
                    'money_transfer',
                ],
                'facilities' => [
                    'atm_24_7' => 3,
                    'safe_deposit_boxes' => 150,
                    'parking_space' => 20,
                    'wheelchair_access' => true,
                    'air_conditioning' => true,
                    'security_guard' => true,
                    'cctv_cameras' => true,
                    'customer_waiting_area' => true,
                    'business_lounge' => true,
                    'meeting_rooms' => 2,
                    'free_wifi' => true,
                    'restroom' => true,
                    'drinking_water' => true,
                ],
                'metadata' => [
                    'region' => 'Ahafo',
                    'zone' => 'Middle Belt',
                    'branch_category' => 'A',
                    'established_date' => '2010-05-15',
                    'last_renovation' => '2022-03-10',
                    'next_maintenance' => '2024-06-15',
                    'security_level' => 'high',
                    'compliance_rating' => 'excellent',
                    'customer_satisfaction' => 94.5,
                    'peak_hours' => ['09:00-11:00', '14:00-16:00'],
                    'special_services' => [
                        'agricultural_loans',
                        'cocoa_farmer_support',
                        'mobile_money_agent_banking',
                    ],
                    'staff_count' => 28,
                    'monthly_target' => 5000000.00,
                    'actual_performance' => 4750000.00,
                    'performance_percentage' => 95.0,
                ],
                'notes' => 'Main branch serving Goaso and surrounding communities in Ahafo Region. Flagship branch with full services.',
            ],
        ]);

        $this->command->info('Successfully seeded Goaso Main Branch.');
        $this->command->info('Branch Code: GOASO001');
        $this->command->info('Location: Goaso, Ahafo Region, Ghana');
        $this->command->info('Manager: ' . $branchManager->full_name);
    }

    /**
     * Get or create a branch manager for Goaso
     */
    private function getOrCreateBranchManager(): User
    {
        // Try to find an existing branch manager
        $branchManager = User::where('role', 'manager')->first();

        if ($branchManager) {
            $this->command->info('📋 Using existing Branch Manager: ' . $branchManager->full_name);
            return $branchManager;
        }

        // If no branch manager exists, create one
        $this->command->info('👨‍💼 Creating new Branch Manager for Goaso...');

        // Check if we have any users to assign as branch manager
        $adminUser = User::where('role', 'admin')->first();

        if ($adminUser) {
            // Temporarily change admin to branch manager for seeding
            $adminUser->update(['role' => 'manager']);
            $this->command->info('📋 Assigned existing admin as Branch Manager: ' . $adminUser->full_name);
            return $adminUser;
        }

        // Create a new branch manager user
        $branchManager = User::create([
            'first_name' => 'Kwame',
            'last_name' => 'Osei',
            'email' => 'kwame.osei@metcu.com',
            'password' => bcrypt('password123'),
            'phone' => '+233244556677',
            'role' => 'manager',
            'status' => 'active',
            'branch_id' => null, // Will be updated after branch creation
        ]);

        $this->command->info('Created new Branch Manager: Kwame Osei');

        return $branchManager;
    }
}
