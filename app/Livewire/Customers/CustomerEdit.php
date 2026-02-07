<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use App\Models\Eloquent\User;
use Livewire\WithFileUploads;
use App\Models\Eloquent\Branch;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Customer;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CustomerEdit extends Component
{
    use WithFileUploads;

    public Customer $customer;

    // Personal Information
    #[Validate('required|string|max:255')]
    public $first_name = '';

    #[Validate('required|string|max:255')]
    public $last_name = '';

    #[Validate('email')]
    public $email = '';

    #[Validate('required|string|max:20')]
    public $phone = '';

    #[Validate('nullable|string|max:20')]
    public $phone_alt = '';

    #[Validate('required|date|before:today')]
    public $date_of_birth = '';

    #[Validate('required|in:male,female,other')]
    public $gender = 'male';

    #[Validate('required|string|max:100')]
    public $nationality = 'Ghanaian';

    // Identification
    #[Validate('required|in:national_id,passport,drivers_license,voters_id')]
    public $id_type = 'national_id';

    #[Validate('required|string|max:50')]
    public $id_number = '';

    #[Validate('required|date|after:today')]
    public $id_expiry_date = '';

    #[Validate('required|string|max:100')]
    public $id_issuing_country = 'Ghana';

    // Address
    #[Validate('required|string|max:255')]
    public $address_line_1 = '';

    #[Validate('nullable|string|max:255')]
    public $address_line_2 = '';

    #[Validate('required|string|max:100')]
    public $city = '';

    #[Validate('required|string|max:100')]
    public $state = '';

    #[Validate('required|string|max:100')]
    public $country = 'Ghana';

    // Employment & Income
    #[Validate('required|string|max:100')]
    public $occupation = '';

    #[Validate('nullable|string|max:255')]
    public $employer_name = '';

    #[Validate('nullable|string|max:500')]
    public $employer_address = '';

    #[Validate('required|numeric|min:0')]
    public $monthly_income = 0;

    #[Validate('required|string|max:100')]
    public $source_of_income = '';

    #[Validate('nullable|numeric|min:0')]
    public $net_worth = 0;

    // Additional Information
    #[Validate('required|in:low,medium,high')]
    public $risk_profile = 'medium';

    #[Validate('required|in:pending,verified,rejected')]
    public $kyc_status = 'pending';

    #[Validate('required|in:single,married,divorced,widowed')]
    public $marital_status = 'single';

    #[Validate('nullable|integer|min:0')]
    public $dependents = 0;

    #[Validate('nullable|string|max:100')]
    public $education_level = '';

    #[Validate('required|in:active,inactive,suspended')]
    public $status = 'active';

    #[Validate('required|in:individual,corporate')]
    public $customer_type = 'individual';

    #[Validate('required|in:basic,premium,platinum')]
    public $customer_tier = 'basic';

    // Branch & Manager
    #[Validate('required|exists:branches,id')]
    public $branch_id = '';

    #[Validate('nullable|exists:users,id')]
    public $relationship_manager_id = '';

    // File Uploads
    public $profile_photo;
    public $id_front_image;
    public $id_back_image;
    public $signature_image;

    // File previews
    public $existing_profile_photo = null;
    public $existing_id_front_image = null;
    public $existing_id_back_image = null;
    public $existing_signature_image = null;

    // Additional
    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    #[Validate('nullable|array')]
    public $emergency_contacts = [];

    #[Validate('nullable|array')]
    public $additional_documents = [];

    // Data for dropdowns
    public $branches = [];
    public $relationshipManagers = [];
    public $countries = [];
    public $idTypes = [];

    public function mount(Customer $customer)
    {
        if (!Gate::allows('update customers')) {
            abort(403, 'Unauthorized access.');
        }

        $this->customer = $customer;

        // Load customer data
        $this->loadCustomerData();

        // Load dropdown data
        $this->loadBranches();
        $this->loadRelationshipManagers();
        $this->loadCountries();
        $this->loadIdTypes();

        // Load existing file paths for preview
        $this->loadExistingFiles();
    }

    protected function loadCustomerData()
    {
        // Personal Information
        $this->first_name = $this->customer->first_name;
        $this->last_name = $this->customer->last_name;
        $this->email = $this->customer->email;
        $this->phone = $this->customer->phone;
        $this->phone_alt = $this->customer->phone_alt;
        $this->date_of_birth = $this->customer->date_of_birth;
        $this->gender = $this->customer->gender;
        $this->nationality = $this->customer->nationality;

        // Identification
        $this->id_type = $this->customer->id_type;
        $this->id_number = $this->customer->id_number;
        $this->id_expiry_date = $this->customer->id_expiry_date;
        $this->id_issuing_country = $this->customer->id_issuing_country;

        // Address
        $this->address_line_1 = $this->customer->address_line_1;
        $this->address_line_2 = $this->customer->address_line_2;
        $this->city = $this->customer->city;
        $this->state = $this->customer->state;
        $this->country = $this->customer->country;

        // Employment & Income
        $this->occupation = $this->customer->occupation;
        $this->employer_name = $this->customer->employer_name;
        $this->employer_address = $this->customer->employer_address;
        $this->monthly_income = $this->customer->monthly_income;
        $this->source_of_income = $this->customer->source_of_income;
        $this->net_worth = $this->customer->net_worth;

        // Additional Information
        $this->risk_profile = $this->customer->risk_profile;
        $this->kyc_status = $this->customer->kyc_status;
        $this->marital_status = $this->customer->marital_status;
        $this->dependents = $this->customer->dependents;
        $this->education_level = $this->customer->education_level;
        $this->status = $this->customer->status;
        $this->customer_type = $this->customer->customer_type;
        $this->customer_tier = $this->customer->customer_tier;

        // Branch & Manager
        $this->branch_id = $this->customer->branch_id;
        $this->relationship_manager_id = $this->customer->relationship_manager_id;

        // Additional
        $this->notes = $this->customer->notes;
        $this->emergency_contacts = $this->customer->emergency_contacts ?? [
            ['name' => '', 'relationship' => '', 'phone' => '', 'email' => '']
        ];
        $this->additional_documents = $this->customer->additional_documents ?? [];
    }

    protected function loadExistingFiles()
    {
        if ($this->customer->profile_photo_path) {
            $this->existing_profile_photo = $this->customer->profile_photo_path
                ? asset('storage/' . $this->customer->profile_photo_path)
                : null;
        }

        if ($this->customer->id_front_image_path) {
            $this->existing_id_front_image = $this->customer->id_front_image_path
                ? asset('storage/' . $this->customer->id_front_image_path)
                : null;
        }

        if ($this->customer->id_back_image_path) {
            $this->existing_id_back_image = $this->customer->id_back_image_path
                ? asset('storage/' . $this->customer->id_back_image_path)
                : null;
        }

        if ($this->customer->signature_image_path) {
            $this->existing_signature_image = $this->customer->signature_image_path
                ? asset('storage/' . $this->customer->signature_image_path)
                : null;
        }
    }

    protected function loadBranches()
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\Eloquent\User) {
            return;
        }

        if ($user->can('view all branches')) {
            $this->branches = Branch::orderBy('name')->get();
        } else {
            $this->branches = Branch::where('id', $user->branch_id)->get();
        }
    }

    protected function loadRelationshipManagers()
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\Eloquent\User) {
            return;
        }

        $query = User::role('relationship_manager')->active();

        if (!$user->can('view all branches') && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $this->relationshipManagers = $query->orderBy('first_name')->get();
    }

    protected function loadCountries()
    {
        $this->countries = [
            'Ghana' => 'Ghana',
            // 'Nigeria' => 'Nigeria',
            // 'Ivory Coast' => 'Ivory Coast',
            // 'South Africa' => 'South Africa',
            // 'United States' => 'United States',
            // 'United Kingdom' => 'United Kingdom',
            // 'Canada' => 'Canada',
        ];
    }

    protected function loadIdTypes()
    {
        $this->idTypes = [
            'national_id' => 'Ghana Card',
            'passport' => 'Passport',
            'drivers_license' => "Driver's License",
            'voters_id' => "Voter's ID",
        ];
    }

    public function addEmergencyContact()
    {
        $this->emergency_contacts[] = ['name' => '', 'relationship' => '', 'phone' => '', 'email' => ''];
    }

    public function removeEmergencyContact($index)
    {
        unset($this->emergency_contacts[$index]);
        $this->emergency_contacts = array_values($this->emergency_contacts);
    }

    public function calculateAge()
    {
        if ($this->date_of_birth) {
            $birthDate = new \DateTime($this->date_of_birth);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;
            return $age;
        }
        return null;
    }

    public function update()
    {
        $this->validate();

        // Validate age (must be at least 18)
        $age = $this->calculateAge();
        if ($age && $age < 18) {
            $this->addError('date_of_birth', 'Customer must be at least 18 years old.');
            return;
        }

        try {
            // Handle file uploads
            $profilePhotoPath = $this->customer->profile_photo_path;
            $idFrontImagePath = $this->customer->id_front_image_path;
            $idBackImagePath = $this->customer->id_back_image_path;
            $signatureImagePath = $this->customer->signature_image_path;

            if ($this->profile_photo) {
                // Delete old file if exists
                if ($profilePhotoPath && Storage::disk('public')->exists($profilePhotoPath)) {
                    Storage::disk('public')->delete($profilePhotoPath);
                }
                $profilePhotoPath = $this->profile_photo->store('customers/profile-photos', 'public');
            }

            if ($this->id_front_image) {
                // Delete old file if exists
                if ($idFrontImagePath && Storage::disk('public')->exists($idFrontImagePath)) {
                    Storage::disk('public')->delete($idFrontImagePath);
                }
                $idFrontImagePath = $this->id_front_image->store('customers/id-documents', 'public');
            }

            if ($this->id_back_image) {
                // Delete old file if exists
                if ($idBackImagePath && Storage::disk('public')->exists($idBackImagePath)) {
                    Storage::disk('public')->delete($idBackImagePath);
                }
                $idBackImagePath = $this->id_back_image->store('customers/id-documents', 'public');
            }

            if ($this->signature_image) {
                // Delete old file if exists
                if ($signatureImagePath && Storage::disk('public')->exists($signatureImagePath)) {
                    Storage::disk('public')->delete($signatureImagePath);
                }
                $signatureImagePath = $this->signature_image->store('customers/signatures', 'public');
            }

            if (empty($this->relationship_manager_id)) {
                $this->relationship_manager_id = null;
            }

            // Update customer
            $this->customer->update([
                'branch_id' => $this->branch_id,
                'relationship_manager_id' => $this->relationship_manager_id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'phone_alt' => $this->phone_alt,
                'date_of_birth' => $this->date_of_birth,
                'gender' => $this->gender,
                'nationality' => $this->nationality,
                'id_type' => $this->id_type,
                'id_number' => $this->id_number,
                'id_expiry_date' => $this->id_expiry_date,
                'id_issuing_country' => $this->id_issuing_country,
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'occupation' => $this->occupation,
                'employer_name' => $this->employer_name,
                'employer_address' => $this->employer_address,
                'monthly_income' => $this->monthly_income,
                'source_of_income' => $this->source_of_income,
                'net_worth' => $this->net_worth,
                'risk_profile' => $this->risk_profile,
                'kyc_status' => $this->kyc_status,
                'profile_photo_path' => $profilePhotoPath,
                'id_front_image_path' => $idFrontImagePath,
                'id_back_image_path' => $idBackImagePath,
                'signature_image_path' => $signatureImagePath,
                'marital_status' => $this->marital_status,
                'dependents' => $this->dependents,
                'education_level' => $this->education_level,
                'emergency_contacts' => array_filter($this->emergency_contacts, function ($contact) {
                    return !empty($contact['name']) || !empty($contact['phone']);
                }),
                'additional_documents' => $this->additional_documents,
                'status' => $this->status,
                'customer_type' => $this->customer_type,
                'customer_tier' => $this->customer_tier,
                'verified_at' => $this->kyc_status === 'verified' && !$this->customer->verified_at ? now() : $this->customer->verified_at,
                'notes' => $this->notes,
                'metadata' => [
                    ...($this->customer->metadata ?? []),
                    'updated_by' => Auth::user()->id,
                    'updated_at' => now()->toISOString(),
                ],
            ]);

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($this->customer)
                ->withProperties([
                    'customer_number' => $this->customer->customer_number,
                    'customer_name' => $this->customer->full_name,
                    'branch_id' => $this->customer->branch_id,
                ])
                ->log('Customer updated');

            session()->flash('success', 'Customer details updated successfully.');
            // Redirect to customer show page
            return redirect()->route('customers.show', $this->customer->id);
        } catch (\Exception $e) {
            Log::error('Customer update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'customer_id' => $this->customer->id,
                'data' => [
                    'email' => $this->email,
                    'phone' => $this->phone,
                ]
            ]);

            session()->flash('error', 'Failed to update customer details:' . $e->getMessage());

            $this->addError('general', 'Failed to update customer: ' . $e->getMessage());
        }
    }

    public function removeExistingFile($type)
    {
        try {
            switch ($type) {
                case 'profile_photo':
                    if ($this->customer->profile_photo_path && Storage::disk('public')->exists($this->customer->profile_photo_path)) {
                        Storage::disk('public')->delete($this->customer->profile_photo_path);
                    }
                    $this->customer->profile_photo_path = null;
                    $this->existing_profile_photo = null;
                    break;

                case 'id_front':
                    if ($this->customer->id_front_image_path && Storage::disk('public')->exists($this->customer->id_front_image_path)) {
                        Storage::disk('public')->delete($this->customer->id_front_image_path);
                    }
                    $this->customer->id_front_image_path = null;
                    $this->existing_id_front_image = null;
                    break;

                case 'id_back':
                    if ($this->customer->id_back_image_path && Storage::disk('public')->exists($this->customer->id_back_image_path)) {
                        Storage::disk('public')->delete($this->customer->id_back_image_path);
                    }
                    $this->customer->id_back_image_path = null;
                    $this->existing_id_back_image = null;
                    break;

                case 'signature':
                    if ($this->customer->signature_image_path && Storage::disk('public')->exists($this->customer->signature_image_path)) {
                        Storage::disk('public')->delete($this->customer->signature_image_path);
                    }
                    $this->customer->signature_image_path = null;
                    $this->existing_signature_image = null;
                    break;
            }

            $this->customer->save();

            session()->flash('success', 'File removed successfully');
        } catch (\Exception $e) {

            session()->flash('success', 'Failed to remove file: '.$e->getMessage());
        }
    }

    public function getDefaultProfilePhoto(string $name): string
    {
        $initials = collect(explode(' ', $name))
            ->map(fn($word) => mb_substr($word, 0, 1))
            ->join('');

        return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=7F9CF5&color=FFFFFF&size=256";
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.customers.customer-edit');
    }
}
