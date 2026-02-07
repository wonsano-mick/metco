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

class CustomerCreate extends Component
{
    use WithFileUploads;

    // Step management
    public $currentStep = 1;

    // Customer Type
    #[Validate('required|in:individual,organization')]
    public $customer_type = '';

    // Terms & Conditions
    #[Validate('required|accepted')]
    public $terms_accepted = false;

    public $signatories_verified = false;

    // ===== INDIVIDUAL CUSTOMER FIELDS =====

    // Personal Information
    #[Validate('required_if:customer_type,individual|string|max:255')]
    public $first_name = '';

    #[Validate('required_if:customer_type,individual|string|max:255')]
    public $last_name = '';

    #[Validate('required|email|unique:customers,email')]
    public $email = '';

    #[Validate('required|string|max:20')]
    public $phone = '';

    #[Validate('nullable|string|max:20')]
    public $phone_alt = '';

    #[Validate('required_if:customer_type,individual|date|before:today')]
    public $date_of_birth = '';

    #[Validate('required_if:customer_type,individual|in:male,female,other')]
    public $gender = 'male';

    #[Validate('required_if:customer_type,individual|string|max:100')]
    public $nationality = 'Ghanaian';

    // Identification
    #[Validate('required_if:customer_type,individual|in:national_id,passport,drivers_license,voters_id')]
    public $id_type = 'national_id';

    #[Validate('required_if:customer_type,individual|string|max:50')]
    public $id_number = '';

    #[Validate('required_if:customer_type,individual|date|after:today')]
    public $id_expiry_date = '';

    #[Validate('required_if:customer_type,individual|string|max:100')]
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

    // Employment & Income (Individual)
    #[Validate('required_if:customer_type,individual|string|max:100')]
    public $occupation = '';

    #[Validate('nullable|string|max:255')]
    public $employer_name = '';

    #[Validate('nullable|string|max:500')]
    public $employer_address = '';

    #[Validate('required_if:customer_type,individual|numeric|min:0')]
    public $monthly_income = 0;

    #[Validate('required_if:customer_type,individual|string|max:100')]
    public $source_of_income = '';

    #[Validate('nullable|numeric|min:0')]
    public $net_worth = 0;

    // ===== ORGANIZATION CUSTOMER FIELDS =====

    #[Validate('required_if:customer_type,organization|string|max:255')]
    public $company_name = '';

    #[Validate('required_if:customer_type,organization|in:corporation,llc,partnership,sole_proprietorship,ngo,government,other')]
    public $organization_type = '';

    #[Validate('required_if:customer_type,organization|string|max:100')]
    public $registration_number = '';

    #[Validate('required_if:customer_type,organization|string|max:100')]
    public $tax_identification_number = '';

    #[Validate('required_if:customer_type,organization|in:agriculture,manufacturing,construction,retail,technology,finance,healthcare,education,real_estate,transportation,hospitality,other')]
    public $industry = '';

    #[Validate('required_if:customer_type,organization|string|max:500')]
    public $business_nature = '';

    #[Validate('required_if:customer_type,organization|string|max:255')]
    public $contact_person = '';

    #[Validate('required_if:customer_type,organization|string|max:100')]
    public $contact_person_position = '';

    #[Validate('required_if:customer_type,organization|string|max:20')]
    public $contact_person_phone = '';

    #[Validate('required_if:customer_type,organization|numeric|min:0')]
    public $annual_revenue = 0;

    #[Validate('required_if:customer_type,organization|integer|min:1')]
    public $number_of_employees = 1;

    #[Validate('nullable|url|max:255')]
    public $website = '';

    // ===== COMMON FIELDS =====

    // Additional Information
    #[Validate('required|in:low,medium,high')]
    public $risk_profile = 'medium';

    #[Validate('required|in:pending,verified,rejected')]
    public $kyc_status = 'pending';

    #[Validate('required_if:customer_type,individual|in:single,married,divorced,widowed')]
    public $marital_status = 'single';

    #[Validate('nullable|integer|min:0')]
    public $dependents = 0;

    #[Validate('nullable|string|max:100')]
    public $education_level = '';

    #[Validate('required|in:active,inactive,suspended')]
    public $status = 'active';

    #[Validate('required|in:basic,premium,platinum')]
    public $customer_tier = 'basic';

    // Branch & Manager
    #[Validate('required|exists:branches,id')]
    public $branch_id = '';

    #[Validate('nullable|exists:users,id')]
    public $relationship_manager_id = '';

    // File Uploads
    #[Validate('nullable|image|max:2048')] // 2MB max for profile photo
    public $profile_photo;

    #[Validate('nullable|image|max:2048')] // 2MB max for signature
    public $signature_image;

    #[Validate('required_if:customer_type,individual|image|max:5120')] // 5MB max for ID front
    public $id_front_image;

    #[Validate('nullable|image|max:5120')] // 5MB max for ID back
    public $id_back_image;

    // Additional
    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    #[Validate('nullable|array')]
    public $emergency_contacts = [];

    #[Validate('nullable|array')]
    public $next_of_kin = [];

    #[Validate('nullable|array')]
    public $authorized_signatories = [];

    #[Validate('nullable|array')]
    public $additional_documents = [];

    // Data for dropdowns
    public $branches = [];
    public $relationshipManagers = [];
    public $countries = [];
    public $idTypes = [];

    public function mount()
    {
        if (!Gate::allows('create customers')) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::user();

        // Set default branch
        $this->branch_id = $user->branch_id ?? '';

        // Load dropdown data
        $this->loadBranches();
        $this->loadRelationshipManagers();
        $this->loadCountries();
        $this->loadIdTypes();

        // Initialize emergency contacts array for individuals
        $this->emergency_contacts = [
            ['name' => '', 'relationship' => '', 'phone' => '', 'email' => '']
        ];

        // Initialize next of kin contacts array for individuals
        $this->next_of_kin = [
            ['name' => '', 'relationship' => '', 'phone' => '', 'percentage' => '']
        ];

        // Initialize authorized signatories array for organizations
        if ($this->customer_type === 'organization') {
            $this->authorized_signatories = [
                ['name' => '', 'position' => '', 'phone' => '', 'email' => '']
            ];
        }
    }

    // New method for tab navigation
    public function goToStep($step)
    {
        // Validate current step before moving
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        } elseif ($step > $this->currentStep) {
            // Validate current step before proceeding to next
            if ($this->currentStep === 1 && !$this->customer_type) {
                $this->addError('customer_type', 'Please select a customer type.');
                return;
            }

            if ($this->currentStep === 2) {
                $this->validateStep2();
            } elseif ($this->currentStep === 3) {
                $this->validateStep3();
            } elseif ($this->currentStep === 4) {
                $this->validateStep4();
            } elseif ($this->currentStep === 5) {
                $this->validateStep5();
            }

            $this->currentStep = $step;
        }

        $this->dispatch('step-changed', step: $this->currentStep);
    }

    public function selectCustomerType($type)
    {
        $this->customer_type = $type;

        // If organization, set some defaults
        if ($type === 'organization') {
            $this->organization_type = 'corporation';
            $this->industry = 'other';
            $this->contact_person_position = 'Managing Director';
        }
    }

    public function nextStep()
    {
        $this->goToStep($this->currentStep + 1);
    }

    public function previousStep()
    {
        $this->goToStep($this->currentStep - 1);
    }

    protected function validateStep2()
    {
        $rules = [
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:20',
        ];

        if ($this->customer_type === 'individual') {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
            $rules['date_of_birth'] = 'required|date|before:today';
            $rules['gender'] = 'required|in:male,female,other';
            $rules['nationality'] = 'required|string|max:100';
        } else {
            $rules['company_name'] = 'required|string|max:255';
            $rules['organization_type'] = 'required|in:corporation,llc,partnership,sole_proprietorship,ngo,government,other';
            $rules['registration_number'] = 'required|string|max:100';
            $rules['tax_identification_number'] = 'required|string|max:100';
            $rules['industry'] = 'required|in:agriculture,manufacturing,construction,retail,technology,finance,healthcare,education,real_estate,transportation,hospitality,other';
            $rules['contact_person'] = 'required|string|max:255';
            $rules['contact_person_position'] = 'required|string|max:100';
            $rules['contact_person_phone'] = 'required|string|max:20';
        }

        $this->validate($rules);
    }

    protected function validateStep3()
    {
        $rules = [
            'address_line_1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
        ];

        $this->validate($rules);
    }

    protected function validateStep4()
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'status' => 'required|in:active,inactive,suspended',
            'customer_tier' => 'required|in:basic,premium,platinum',
            'risk_profile' => 'required|in:low,medium,high',
        ];

        $this->validate($rules);
    }

    protected function validateStep5()
    {
        $rules = [];

        if ($this->customer_type === 'individual') {
            // Apply the validation you mentioned
            $rules['id_type'] = 'required|in:national_id,passport,drivers_license,voters_id';
            $rules['id_number'] = 'required|string|max:50';
            $rules['id_expiry_date'] = 'required|date|after:today';
            $rules['id_issuing_country'] = 'required|string|max:100';
            $rules['occupation'] = 'required|string|max:100';
            $rules['monthly_income'] = 'required|numeric|min:0';
            $rules['source_of_income'] = 'required|string|max:100';
        } else {
            $rules['annual_revenue'] = 'required|numeric|min:0';
            $rules['number_of_employees'] = 'required|integer|min:1';
        }

        $this->validate($rules);
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
            // Add more countries as needed
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

    public function addNextOfKin()
    {
        $this->next_of_kin[] = ['name' => '', 'relationship' => '', 'phone' => '', 'percentage' => ''];
    }

    public function removeNextOfKin($index)
    {
        unset($this->next_of_kin[$index]);
        $this->next_of_kin = array_values($this->next_of_kin);
    }

    public function addSignatory()
    {
        $this->authorized_signatories[] = ['name' => '', 'position' => '', 'phone' => '', 'email' => ''];
    }

    public function removeSignatory($index)
    {
        unset($this->authorized_signatories[$index]);
        $this->authorized_signatories = array_values($this->authorized_signatories);
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

    public function save()
    {
        // dd($this->all());
        $rules = $this->getRules();

        // Add conditional rule for signatories_verified
        if ($this->customer_type === 'organization') {
            $rules['signatories_verified'] = 'required|accepted';
        }

        // Validate
        $this->validate($rules);

        // Additional validations
        if ($this->customer_type === 'individual') {
            // Validate age (must be at least 18)
            $age = $this->calculateAge();
            if ($age && $age < 18) {
                $this->addError('date_of_birth', 'Customer must be at least 18 years old.');
                return;
            }

            // Validate ID document for individuals
            if (!$this->id_front_image) {
                $this->addError('id_front_image', 'ID front image is required for individual customers.');
                return;
            }
        }

        // Validate terms acceptance
        if (!$this->terms_accepted) {
            $this->addError('terms_accepted', 'You must accept the terms and conditions.');
            return;
        }

        // Validate signatories for organizations
        if ($this->customer_type === 'organization') {
            if (!$this->signatories_verified) {
                $this->addError('signatories_verified', 'You must verify that signatories are properly documented.');
                return;
            }
        }

        try {
            // Handle file uploads
            $profilePhotoPath = null;
            $idFrontImagePath = null;
            $idBackImagePath = null;
            $signatureImagePath = null;

            if ($this->profile_photo) {
                $profilePhotoPath = $this->profile_photo->store('customers/profile-photos', 'public');
            }

            if ($this->id_front_image) {
                $idFrontImagePath = $this->id_front_image->store('customers/id-documents', 'public');
            }

            if ($this->id_back_image) {
                $idBackImagePath = $this->id_back_image->store('customers/id-documents', 'public');
            }

            if ($this->signature_image) {
                $signatureImagePath = $this->signature_image->store('customers/signatures', 'public');
            }

            // Generate customer number
            $customerNumber = Customer::generateCustomerNumber();

            if (empty($this->relationship_manager_id)) {
                $this->relationship_manager_id = null;
            }

            // Prepare customer data
            $customerData = [
                'branch_id' => $this->branch_id,
                'relationship_manager_id' => $this->relationship_manager_id,
                'customer_number' => $customerNumber,
                'email' => $this->email,
                'phone' => $this->phone,
                'phone_alt' => $this->phone_alt,
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'risk_profile' => $this->risk_profile,
                'kyc_status' => $this->kyc_status,
                'profile_photo_path' => $profilePhotoPath,
                'id_front_image_path' => $idFrontImagePath,
                'id_back_image_path' => $idBackImagePath,
                'signature_image_path' => $signatureImagePath,
                'status' => $this->status,
                'customer_type' => $this->customer_type,
                'customer_tier' => $this->customer_tier,
                'registered_at' => now(),
                'verified_at' => $this->kyc_status === 'verified' ? now() : null,
                'notes' => $this->notes,
                'metadata' => [
                    'created_by' => Auth::user()->id,
                    'created_at' => now()->toISOString(),
                    'customer_type' => $this->customer_type,
                ],
            ];

            // Add individual-specific fields
            if ($this->customer_type === 'individual') {
                $customerData['first_name'] = $this->first_name;
                $customerData['last_name'] = $this->last_name;
                $customerData['date_of_birth'] = $this->date_of_birth;
                $customerData['gender'] = $this->gender;
                $customerData['nationality'] = $this->nationality;
                $customerData['id_type'] = $this->id_type;
                $customerData['id_number'] = $this->id_number;
                $customerData['id_expiry_date'] = $this->id_expiry_date;
                $customerData['id_issuing_country'] = $this->id_issuing_country;
                $customerData['occupation'] = $this->occupation;
                $customerData['employer_name'] = $this->employer_name;
                $customerData['employer_address'] = $this->employer_address;
                $customerData['monthly_income'] = $this->monthly_income;
                $customerData['source_of_income'] = $this->source_of_income;
                $customerData['net_worth'] = $this->net_worth;
                $customerData['marital_status'] = $this->marital_status;
                $customerData['dependents'] = $this->dependents;
                $customerData['education_level'] = $this->education_level;
                $customerData['emergency_contacts'] = array_filter($this->emergency_contacts, function ($contact) {
                    return !empty($contact['name']) || !empty($contact['phone']);
                });
                $customerData['next_of_kin'] = array_filter($this->next_of_kin, function ($nextOfKin) {
                    return !empty($nextOfKin['name']) || !empty($nextOfKin['phone']) || !empty($nextOfKin['percentage']);
                });
            } else {
                // Add organization-specific fields
                $customerData['company_name'] = $this->company_name;
                $customerData['first_name'] = $this->contact_person; // For compatibility
                $customerData['last_name'] = ''; // For compatibility
                $customerData['organization_type'] = $this->organization_type;
                $customerData['registration_number'] = $this->registration_number;
                $customerData['tax_identification_number'] = $this->tax_identification_number;
                $customerData['industry'] = $this->industry;
                $customerData['business_nature'] = $this->business_nature;
                $customerData['contact_person'] = $this->contact_person;
                $customerData['contact_person_position'] = $this->contact_person_position;
                $customerData['contact_person_phone'] = $this->contact_person_phone;
                $customerData['monthly_income'] = $this->annual_revenue / 12; // Store as monthly for compatibility
                $customerData['annual_revenue'] = $this->annual_revenue;
                $customerData['number_of_employees'] = $this->number_of_employees;
                $customerData['website'] = $this->website;
                $customerData['authorized_signatories'] = array_filter($this->authorized_signatories, function ($signatory) {
                    return !empty($signatory['name']) || !empty($signatory['email']);
                });
                $customerData['source_of_income'] = 'Business Operations';
                $customerData['occupation'] = 'Business';
                $customerData['employer_address'] = $this->employer_address;
            }

            // Create customer
            $customer = Customer::create($customerData);

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($customer)
                ->withProperties([
                    'customer_number' => $customer->customer_number,
                    'customer_name' => $customer->full_name,
                    'customer_type' => $customer->customer_type,
                    'branch_id' => $customer->branch_id,
                ])
                ->log(($customer->customer_type === 'individual' ? 'Individual' : 'Organizational') . ' customer created');

            session()->flash('success', ($this->customer_type === 'individual' ? 'Individual' : 'Organizational') . ' customer created successfully.');

            // Redirect to customer show page
            return redirect()->route('customers.show', $customer->id);
        } catch (\Exception $e) {
            Log::error('Customer creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => [
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'customer_type' => $this->customer_type,
                ]
            ]);

            session()->flash('error', 'Failed to create customer: ' . $e->getMessage());
            $this->addError('general', 'Failed to create customer: ' . $e->getMessage());
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
        return view('livewire.customers.customer-create');
    }
}
