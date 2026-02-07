<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Eloquent\Customer;
use App\Models\Eloquent\Branch;
use App\Models\Eloquent\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing customers
        DB::table('customers')->truncate();

        // Get branches and relationship managers
        $branches = Branch::all();
        $relationshipManagers = User::whereIn('role', ['manager', 'teller', 'supervisor'])->get();

        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please run BranchSeeder first.');
            return;
        }

        if ($relationshipManagers->isEmpty()) {
            $this->command->error('No relationship managers found. Please create users first.');
            return;
        }

        $customers = $this->generateCustomerData();

        $progressBar = $this->command->getOutput()->createProgressBar(count($customers));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Starting...');
        $progressBar->start();

        foreach ($customers as $customerData) {
            $progressBar->setMessage("Creating customer: {$customerData['first_name']} {$customerData['last_name']}");

            // Randomly assign branch and relationship manager
            $branch = $branches->random();
            $relationshipManager = $relationshipManagers->random();

            // Calculate age from date of birth
            $dateOfBirth = Carbon::parse($customerData['date_of_birth']);
            $age = $dateOfBirth->age;

            // Determine customer tier based on income and age
            $customerTier = $this->determineCustomerTier($customerData['monthly_income'], $age);

            // Determine risk profile
            $riskProfile = $this->determineRiskProfile($age, $customerData['occupation'], $customerData['monthly_income']);

            // Create emergency contacts
            $emergencyContacts = $this->generateEmergencyContacts();

            // Create the customer
            Customer::create([
                'branch_id' => $branch->id,
                'relationship_manager_id' => $relationshipManager->id,
                'customer_number' => $customerData['customer_number'],
                'first_name' => $customerData['first_name'],
                'last_name' => $customerData['last_name'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'],
                'phone_alt' => $this->generatePhoneNumber(),
                'date_of_birth' => $dateOfBirth,
                'gender' => $customerData['gender'],
                'nationality' => $customerData['nationality'],
                'id_type' => $customerData['id_type'],
                'id_number' => $customerData['id_number'],
                'id_expiry_date' => Carbon::now()->addYears(5),
                'id_issuing_country' => $customerData['id_issuing_country'],
                'address_line_1' => $customerData['address_line_1'],
                'address_line_2' => $customerData['address_line_2'],
                'city' => $customerData['city'],
                'state' => $customerData['state'],
                'country' => $customerData['country'],
                'postal_code' => $customerData['postal_code'],
                'occupation' => $customerData['occupation'],
                'employer_name' => $customerData['employer_name'],
                'employer_address' => $customerData['employer_address'],
                'monthly_income' => $customerData['monthly_income'],
                'source_of_income' => $customerData['source_of_income'],
                'net_worth' => $customerData['monthly_income'] * 12 * 5, // Approximate 5 years of income
                'risk_profile' => $riskProfile,
                'kyc_status' => $customerData['kyc_status'],
                'profile_photo_path' => $this->generateProfilePhotoUrl($customerData['first_name'], $customerData['last_name'], $customerData['gender']),
                'id_front_image_path' => 'customers/ids/front_' . Str::random(10) . '.jpg',
                'id_back_image_path' => 'customers/ids/back_' . Str::random(10) . '.jpg',
                'signature_image_path' => 'customers/signatures/sig_' . Str::random(10) . '.png',
                'marital_status' => $customerData['marital_status'],
                'dependents' => $customerData['dependents'],
                'education_level' => $customerData['education_level'],
                'emergency_contacts' => $emergencyContacts,
                'additional_documents' => $this->generateAdditionalDocuments(),
                'status' => $customerData['status'],
                'customer_type' => $customerData['customer_type'],
                'customer_tier' => $customerTier,
                'registered_at' => Carbon::now()->subMonths(rand(1, 36)),
                'verified_at' => $customerData['kyc_status'] === 'verified' ? Carbon::now()->subMonths(rand(1, 24)) : null,
                'last_reviewed_at' => Carbon::now()->subMonths(rand(1, 12)),
                'notes' => $customerData['notes'],
            ]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('✅ Successfully seeded ' . count($customers) . ' customers.');
    }

    /**
     * Generate realistic customer data
     */
    private function generateCustomerData(): array
    {
        return [
            // Premium Customers (VIP)
            [
                'customer_number' => 001,
                'first_name' => 'James',
                'last_name' => 'Owusu',
                'email' => 'james.owusu@email.com',
                'phone' => '0241111111',
                'date_of_birth' => '1975-06-15',
                'gender' => 'male',
                'nationality' => 'Ghanaian',
                'id_type' => 'passport',
                'id_number' => 'P12345678',
                'id_issuing_country' => 'Ghana',
                'address_line_1' => '123 Victory Street',
                'address_line_2' => '1 BLK 3',
                'city' => 'Goaso',
                'state' => '',
                'country' => 'GH',
                'postal_code' => '10022',
                'occupation' => 'Teacher',
                'employer_name' => 'Ghana Education Service',
                'employer_address' => 'Goaso Directorate',
                'monthly_income' => 45000.00,
                'source_of_income' => 'Employment',
                'kyc_status' => 'verified',
                'marital_status' => 'married',
                'dependents' => 2,
                'education_level' => 'MBA',
                'status' => 'active',
                'customer_type' => 'individual',
                'notes' => 'High net worth individual. Interested in investment products.',
            ],
            [
                'customer_number' => 002,
                'first_name' => 'Sarah',
                'last_name' => 'Obeng',
                'email' => 'sarah.obeng@email.com',
                'phone' => '0243222222',
                'date_of_birth' => '1982-03-22',
                'gender' => 'female',
                'nationality' => 'Ghanaian',
                'id_type' => 'national_id',
                'id_number' => 'GHA-99188899-0',
                'id_issuing_country' => 'Ghana',
                'address_line_1' => '456 BK Street',
                'address_line_2' => 'Apt 7',
                'city' => 'Goaso',
                'state' => '',
                'country' => 'GH',
                'postal_code' => '',
                'occupation' => 'Surgeon',
                'employer_name' => 'Mim General Hospital',
                'employer_address' => '200 Obour Street',
                'monthly_income' => 38000.00,
                'source_of_income' => 'Employment',
                'kyc_status' => 'verified',
                'marital_status' => 'single',
                'dependents' => 0,
                'education_level' => 'MD',
                'status' => 'active',
                'customer_type' => 'individual',
                'notes' => 'Medical professional. Interested in premium banking services.',
            ],

            // Business Customers
            [
                'customer_number' => 003,
                'first_name' => 'Michael',
                'last_name' => 'Addo Mensah',
                'email' => 'michael@techsolutions.com',
                'phone' => '0243333333',
                'date_of_birth' => '1978-11-30',
                'gender' => 'male',
                'nationality' => 'Ghanaian',
                'id_type' => 'drivers_license',
                'id_number' => 'DL87654321',
                'id_issuing_country' => 'Ghana',
                'address_line_1' => '789 Highway Drive',
                'address_line_2' => 'Building C',
                'city' => 'Mim',
                'state' => '',
                'country' => 'GH',
                'postal_code' => '94107',
                'occupation' => 'CEO',
                'employer_name' => 'Tech Solutions Inc.',
                'employer_address' => 'Opposite Shabash Clinic',
                'monthly_income' => 75000.00,
                'source_of_income' => 'Business',
                'kyc_status' => 'verified',
                'marital_status' => 'married',
                'dependents' => 3,
                'education_level' => 'PhD',
                'status' => 'active',
                'customer_type' => 'business',
                'notes' => 'Business owner. Requires corporate banking services.',
            ],

            // Regular Customers (Basic Tier)
            [
                'customer_number' => 004,
                'first_name' => 'Jessica',
                'last_name' => 'Ofosu Mensah',
                'email' => 'jessica.omensah@email.com',
                'phone' => '0243444444',
                'date_of_birth' => '1990-08-12',
                'gender' => 'female',
                'nationality' => 'Ghanaian',
                'id_type' => 'national_id',
                'id_number' => 'N12349876',
                'id_issuing_country' => 'Ghana',
                'address_line_1' => '321 Oak Street',
                'address_line_2' => 'Apt 5B',
                'city' => 'Kukuom',
                'state' => '',
                'country' => 'GH',
                'postal_code' => '60611',
                'occupation' => 'Teacher',
                'employer_name' => 'Kukuom Basic School',
                'employer_address' => 'P. O. Box 3, Kukuom',
                'monthly_income' => 4200.00,
                'source_of_income' => 'Employment',
                'kyc_status' => 'verified',
                'marital_status' => 'married',
                'dependents' => 1,
                'education_level' => 'BSc',
                'status' => 'active',
                'customer_type' => 'individual',
                'notes' => 'Public school teacher. Looking for savings account.',
            ],
            [
                'customer_number' => 005,
                'first_name' => 'David',
                'last_name' => 'Ofori Sarpong',
                'email' => 'david.osarpong@email.com',
                'phone' => '0243666666',
                'date_of_birth' => '1985-02-28',
                'gender' => 'male',
                'nationality' => 'Ghanaian',
                'id_type' => 'passport',
                'id_number' => 'P98761234',
                'id_issuing_country' => 'Ghana',
                'address_line_1' => 'F24/1, Abotanso',
                'address_line_2' => '',
                'city' => 'Goaso',
                'state' => '',
                'country' => 'GH',
                'postal_code' => '',
                'occupation' => 'Software Engineer',
                'employer_name' => 'TechCorp Inc.',
                'employer_address' => 'Opposite Main market',
                'monthly_income' => 6500.00,
                'source_of_income' => 'Employment',
                'kyc_status' => 'verified',
                'marital_status' => 'single',
                'dependents' => 0,
                'education_level' => 'Bachelors',
                'status' => 'active',
                'customer_type' => 'individual',
                'notes' => 'Tech professional. Frequent international transfers.',
            ],

            // Senior Citizens
            [
                'customer_number' => 006,
                'first_name' => 'Robert',
                'last_name' => 'Duah',
                'email' => 'robert.duah@email.com',
                'phone' => '0243777777',
                'date_of_birth' => '1952-12-05',
                'gender' => 'male',
                'nationality' => 'Ghana',
                'id_type' => 'drivers_license',
                'id_number' => 'DL23456789',
                'id_issuing_country' => 'Ghana',
                'address_line_1' => 'F45/2 Blk 2',
                'address_line_2' => '',
                'city' => 'Goaso',
                'state' => '',
                'country' => 'GH',
                'postal_code' => '',
                'occupation' => 'Retired',
                'employer_name' => 'N/A',
                'employer_address' => '',
                'monthly_income' => 3500.00,
                'source_of_income' => 'Pension',
                'kyc_status' => 'verified',
                'marital_status' => 'widowed',
                'dependents' => 0,
                'education_level' => 'Bachelors',
                'status' => 'active',
                'customer_type' => 'senior',
                'notes' => 'Senior citizen. Prefers in-branch service.',
            ],

            // Joint Account Customers
            [
                'customer_number' => 007,
                'first_name' => 'Emily',
                'last_name' => 'Oppong',
                'email' => 'emily.oppong@email.com',
                'phone' => '0243888888',
                'date_of_birth' => '1988-07-19',
                'gender' => 'female',
                'nationality' => 'Ghana',
                'id_type' => 'national_id',
                'id_number' => 'N45678901',
                'id_issuing_country' => 'Ghana',
                'address_line_1' => '147 Victory Street',
                'address_line_2' => '',
                'city' => 'Akrodie',
                'state' => '',
                'country' => 'GH',
                'postal_code' => '',
                'occupation' => 'Marketing Manager',
                'employer_name' => 'Oasis',
                'employer_address' => '34 Highway',
                'monthly_income' => 7200.00,
                'source_of_income' => 'Employment',
                'kyc_status' => 'verified',
                'marital_status' => 'married',
                'dependents' => 2,
                'education_level' => 'Masters',
                'status' => 'active',
                'customer_type' => 'joint',
                'notes' => 'Joint account with spouse. High transaction volume.',
            ],
        ];
    }

    /**
     * Generate a phone number
     */
    private function generatePhoneNumber(): string
    {
        $formats = [
            '+233-555-###-####',
            // '+44-20-####-####',
            // '+33-1-##-##-##-##',
            // '+49-30-####-####',
            // '+81-90-####-####',
        ];

        $format = $formats[array_rand($formats)];
        $phone = preg_replace_callback('/#/', function () {
            return rand(0, 9);
        }, $format);

        return $phone;
    }

    /**
     * Generate emergency contacts
     */
    private function generateEmergencyContacts(): array
    {
        $relationships = ['Spouse', 'Parent', 'Sibling', 'Child', 'Friend', 'Relative'];

        return [
            [
                'name' => $this->generateName(),
                'relationship' => $relationships[array_rand($relationships)],
                'phone' => $this->generatePhoneNumber(),
                'email' => 'emergency' . rand(1, 999) . '@example.com',
            ],
            [
                'name' => $this->generateName(),
                'relationship' => $relationships[array_rand($relationships)],
                'phone' => $this->generatePhoneNumber(),
                'email' => 'emergency' . rand(1, 999) . '@example.com',
            ],
        ];
    }

    /**
     * Generate a random name
     */
    private function generateName(): string
    {
        $firstNames = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Lisa', 'Robert', 'Emily'];
        $lastNames = ['Ofosu', 'Addo', 'Acheampong', 'Addai', 'Obiri Yeboah', 'Salifu', 'Appiah', 'Frimpong'];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    /**
     * Generate additional documents
     */
    private function generateAdditionalDocuments(): array
    {
        return [
            [
                'type' => 'proof_of_address',
                'filename' => 'utility_bill_' . Str::random(8) . '.pdf',
                'uploaded_at' => Carbon::now()->subMonths(rand(1, 6)),
            ],
            [
                'type' => 'income_statement',
                'filename' => 'paystub_' . Str::random(8) . '.pdf',
                'uploaded_at' => Carbon::now()->subMonths(rand(1, 3)),
            ],
        ];
    }

    /**
     * Generate profile photo URL using UI Avatars API
     */
    private function generateProfilePhotoUrl(string $firstName, string $lastName, string $gender): string
    {
        $name = urlencode($firstName . ' ' . $lastName);
        $colors = [
            'male' => ['4F46E5', '6366F1'], // Indigo shades
            'female' => ['DB2777', 'EC4899'], // Pink shades
            'other' => ['10B981', '34D399'], // Emerald shades
        ];

        $color = $colors[$gender] ?? $colors['other'];

        return "https://ui-avatars.com/api/?name={$name}&background={$color[0]}&color=FFFFFF&size=512&bold=true&font-size=0.5";
    }

    /**
     * Determine customer tier based on income and age
     */
    private function determineCustomerTier(float $monthlyIncome, int $age): string
    {
        if ($monthlyIncome >= 20000) {
            return 'private';
        } elseif ($monthlyIncome >= 10000) {
            return 'vip';
        } elseif ($monthlyIncome >= 5000) {
            return 'premium';
        } elseif ($age >= 65) {
            return 'premium'; // Seniors get premium by default
        } else {
            return 'basic';
        }
    }

    /**
     * Determine risk profile
     */
    private function determineRiskProfile(int $age, string $occupation, float $monthlyIncome): string
    {
        $riskFactors = 0;

        // Age factor (younger = higher risk)
        if ($age < 25) $riskFactors += 2;
        elseif ($age < 35) $riskFactors += 1;
        elseif ($age > 65) $riskFactors += 1; // Seniors also higher risk

        // Occupation factor
        $highRiskOccupations = ['Student', 'Unemployed', 'Retired', 'Freelancer'];
        $mediumRiskOccupations = ['Artist', 'Entrepreneur', 'Consultant'];

        if (in_array($occupation, $highRiskOccupations)) $riskFactors += 3;
        elseif (in_array($occupation, $mediumRiskOccupations)) $riskFactors += 2;

        // Income factor (lower income = higher risk)
        if ($monthlyIncome < 2000) $riskFactors += 2;
        elseif ($monthlyIncome < 5000) $riskFactors += 1;

        // Determine risk level
        if ($riskFactors >= 5) return 'high';
        elseif ($riskFactors >= 3) return 'medium';
        else return 'low';
    }
}
