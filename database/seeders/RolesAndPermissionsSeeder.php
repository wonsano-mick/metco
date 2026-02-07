<?php

namespace Database\Seeders;

use Ramsey\Uuid\Uuid;
use App\Models\Eloquent\User;
use App\Models\Eloquent\Branch;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for web guard
        $permissions = $this->getPermissions();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create roles with their permissions
        $roles = $this->getRolesWithPermissions();

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
            $role->syncPermissions($rolePermissions);
        }

        // Create users
        $this->createUsers();

        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Roles and permissions seeded successfully!');
    }

    protected function getPermissions(): array
    {
        return [
            // User permissions (ADD THESE)
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'manage users', // Add this one

            // Account permissions
            'view accounts',
            'create accounts',
            'update accounts',
            'delete accounts',
            'view account balance',
            'transfer funds',
            'withdraw funds',
            'deposit funds',
            'freeze accounts',

            // Transaction permissions
            'view transactions',
            'create transactions',
            'cancel transactions',
            'approve transactions',
            'export transaction reports',
            'reverse transactions',

            // Customer permissions
            'view customers',
            'create customers',
            'update customers',
            'delete customers',
            'verify kyc',

            // Admin permissions
            'manage roles',
            'view audit logs',
            'manage system settings',

            // Branch permissions
            'manage branch',
            'view all branches',

            // Report permissions
            'view reports',
            'generate reports',
            'export data',

            //loan permissions
            'create loans',
            'view loans',
            'approve loans',
            'reject loans',
            'cancel loans',
            'review loans',
            'disburse loans',
            'process loan payment',
        ];
    }


    protected function getRolesWithPermissions(): array
    {
        $permissions = $this->getPermissions();

        return [
            'customer' => [
                'view accounts',
                'view account balance',
                'transfer funds',
                'withdraw funds',
                'deposit funds',
                'view transactions',
                'create transactions',
            ],
            'teller' => [
                'user.view',
                'view accounts',
                'create accounts',
                'view account balance',
                'deposit funds',
                'withdraw funds',
                'view transactions',
                'create transactions',
                'view customers',
            ],
            'manager' => [
                'user.view',
                'user.create',
                'user.edit',
                'manage users',
                'view accounts',
                'create accounts',
                'update accounts',
                'view account balance',
                'transfer funds',
                'withdraw funds',
                'deposit funds',
                'view transactions',
                'create transactions',
                'cancel transactions',
                'approve transactions',
                'view customers',
                'create customers',
                'update customers',
                'verify kyc',
                'manage branch',
                'view reports',
                'generate reports',
                'view loans',
                'approve loans',
                'reject loans',
                'cancel loans',
                'review loans',
                'disburse loans',
                'process loan payment',
            ],
            'relationship_manager' => [
                'view customers',
                'create customers',
                'update customers',
            ],
            'supervisor' => [
                'view reports',
                'generate reports',
                'export data',
                'view customers',
                'create customers',
                'update customers',
                'delete customers',
                'verify kyc',
                'view transactions',
                'create transactions',
                'cancel transactions',
                'approve transactions',
                'export transaction reports',
                'reverse transactions',
                'view accounts',
                'create accounts',
                'update accounts',
                'delete accounts',
                'view account balance',
                'transfer funds',
                'withdraw funds',
                'deposit funds',
                'freeze accounts',
            ],
            'loan officer'=>[
                'view customers',
                'create loans',
                'view loans',
                'create loans',
                'view loans',
                'approve loans',
                'reject loans',
                'cancel loans',
                'review loans',
                'disburse loans',
                'process loan payment',
            ],
            'loan committee'=>[
                'view loans',
                'review loans',
                'reject loans',
            ],
            'super-admin' => $permissions,
        ];
    }

    protected function createUsers(): void
    {
        Branch::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Goaso Main Branch',
                'code' => 'GOASO001',
                'email' => 'goaso.main@metco.com',
                'phone' => '+233352012345',
                'address' => 'Market Circle, Opposite Goaso Government Hospital',
                'city' => 'Goaso',
                'state' => 'Ahafo Region',
                'zip_code' => '00233',
                'country' => 'Ghana',
                'latitude' => 6.8036,
                'longitude' => -2.5176,
                'opening_time' => '08:00:00',
                'closing_time' => '17:00:00',
                'status' => 'active',
            ]
        );
        
        $users = [
            [
                // 'id' => Uuid::uuid4()->toString(),
                'id' => 1,
                'email' => 'admin@metco.com',
                'first_name' => 'Michael',
                'last_name' => 'Ofosu Acheampong',
                'username' => 'super.admin',
                'password' => 'SecurePassword123!',
                'role' => 'super-admin',
                'branch_id' => 1,
            ],
            [
                // 'id' => Uuid::uuid4()->toString(),
                'id' => 2,
                'email' => 'manager@metco.com',
                'first_name' => 'Kofi',
                'last_name' => 'Adoma Boateng',
                'username' => 'branch.manager',
                'password' => 'Manager123!',
                'role' => 'manager',
                'branch_id' => 1,
            ],
            [
                // 'id' => Uuid::uuid4()->toString(),
                'id' => 3,
                'email' => 'teller@metco.com',
                'first_name' => 'Fredrick',
                'last_name' => 'Owusu',
                'username' => 'bank.teller',
                'password' => 'Teller123!',
                'role' => 'teller',
                'branch_id' => 1,
            ],
            [
                // 'id' => Uuid::uuid4()->toString(),
                'id' => 4,
                'email' => 'customer@metco.com',
                'first_name' => 'Joshua',
                'last_name' => 'Doe',
                'username' => 'joshua.doe',
                'password' => 'Customer123!',
                'role' => 'customer',
                'branch_id' => 1,
            ],
        ];

        foreach ($users as $userData) {
            // Check if user exists by email
            $user = User::where('email', $userData['email'])->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'id' => $userData['id'],
                    'email' => $userData['email'],
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'username' => $userData['username'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'status' => 'active',
                    'branch_id' => $userData['branch_id'],
                    'email_verified_at' => now(),
                ]);

                $this->command->info("Created user: {$userData['email']}");
            } else {
                // Update existing user
                $user->update([
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'username' => $userData['username'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'status' => 'active',
                    'branch_id' => $userData['branch_id'],
                ]);

                $this->command->info("Updated user: {$userData['email']}");
            }

            // Assign role - make sure user has an ID
            if ($user->id) {
                $user->syncRoles([$userData['role']]);
                $this->command->info("Assigned role '{$userData['role']}' to {$userData['email']}");
            } else {
                $this->command->error("User {$userData['email']} has no ID!");
            }

            $this->command->info("Credentials: {$userData['email']} / {$userData['password']}");
            $this->command->info('---');
        }
    }
}
