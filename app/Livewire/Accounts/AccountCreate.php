<?php

namespace App\Livewire\Accounts;

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use App\Models\Eloquent\User;
use App\Models\Eloquent\Branch;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\Customer;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Eloquent\AccountType;
use App\Models\Eloquent\LedgerEntry;
use App\Models\Eloquent\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Services\Transaction\TransactionService;

class AccountCreate extends Component
{
    // URL parameters
    #[Url]
    public $customer_type = null;

    #[Url]
    public $customer_id = null;

    // Step 1: Customer Type Selection
    public $customerSearch = '';
    public $selectedCustomer = null;

    // Step 2: Account Details
    public $account_type_id = '';
    public $currency = 'GHS';
    public $initial_deposit = 0;
    public $minimum_balance = 0;
    public $overdraft_limit = 0;
    public $status = 'active';
    public $branch_id = '';
    public $notes = '';
    public $generatedAccountNumber = '';

    // Terms & Conditions
    public $termsAccepted = false;
    public $signatoriesVerified = false;

    // Organization-specific fields
    public $signatories = [];

    // Computed properties
    public $selectedAccountType = null;
    public $accountTypes = [];
    public $currencies = ['GHS', 'USD', 'EUR', 'GBP'];
    public $currencySymbol = '₵';
    public $branches = [];
    public $statusOptions = [
        ['value' => 'active', 'label' => 'Active'],
        ['value' => 'dormant', 'label' => 'Dormant'],
        ['value' => 'restricted', 'label' => 'Restricted'],
        ['value' => 'closed', 'label' => 'Closed'],
    ];

    // Transaction service
    protected $transactionService;

    // Current step tracking
    public $currentStep = 1;

    public function mount()
    {
        if (!Gate::allows('create accounts')) {
            abort(403, 'Unauthorized access.');
        }

        // Initialize transaction service
        $this->transactionService = new TransactionService();

        // Load data
        $this->loadAccountTypes();
        $this->loadBranches();

        // Generate initial account number
        $this->generateAccountNumber();

        // Set initial step based on URL parameters
        if ($this->customer_id) {
            // If customer_id is provided in URL, load and select the customer
            $this->selectCustomerFromUrl($this->customer_id);

            if ($this->customer_type && $this->customer_id) {
                $this->currentStep = 3;
            }
        } elseif ($this->customer_type) {
            // If only customer_type is provided
            $this->currentStep = 2;
        } else {
            // Start from beginning
            $this->currentStep = 1;
        }

        // Set default branch if user has limited branch access
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }
        if (!$user->can('view all branches') && $user->branch_id) {
            $this->branch_id = $user->branch_id;
        }

        // Initialize signatories array for organizations
        $this->signatories = [
            ['name' => '', 'email' => '', 'phone' => '']
        ];
    }

    public function updatedCustomerType($value)
    {
        // Reset customer selection when type changes
        $this->reset(['customer_id', 'selectedCustomer', 'account_type_id', 'selectedAccountType']);

        // Set current step to 2
        $this->currentStep = 2;

        // Update URL
        $this->dispatch(
            'update-url',
            customer_type: $value,
            customer_id: null
        );

        // Trigger a Livewire render to show step 2
        $this->dispatch('refresh');
    }

    public function refresh()
    {
        // This method will trigger a re-render
        // No need to do anything else
    }

    public function updatedAccountTypeId($value)
    {
        $this->loadSelectedAccountType();

        // Set default values based on selected account type
        if ($this->selectedAccountType) {
            // Set minimum balance to account type's min_balance
            $this->minimum_balance = $this->selectedAccountType['min_balance'];

            // Set initial deposit to at least the minimum balance
            if ($this->initial_deposit < $this->minimum_balance) {
                $this->initial_deposit = $this->minimum_balance;
            }

            // Set default overdraft limit based on customer type
            $this->overdraft_limit = $this->customer_type === 'organization'
                ? $this->selectedAccountType['min_balance'] * 2 // Higher limit for organizations
                : $this->selectedAccountType['min_balance'] * 0.5; // Lower limit for individuals

            // Default status is already set to 'active'
            $this->status = 'active';
        }

        $this->currentStep = 4;
        // $this->dispatch('scroll-to-top');
    }

    public function updatedInitialDeposit($value)
    {
        // Ensure initial deposit is at least minimum balance
        if ($this->selectedAccountType && $value < $this->selectedAccountType['min_balance']) {
            $this->addError('initial_deposit', 'Initial deposit must be at least ' . number_format($this->selectedAccountType['min_balance'], 2));
        }
    }

    public function updatedCurrency($value)
    {
        // Update currency symbol based on selected currency
        $this->currencySymbol = $this->getCurrencySymbol($value);
    }

    public function updatedMinimumBalance($value)
    {
        // Ensure minimum balance is not negative
        if ($value < 0) {
            $this->minimum_balance = 0;
        }

        // Ensure initial deposit is at least minimum balance
        if ($this->initial_deposit < $value) {
            $this->initial_deposit = $value;
        }
    }

    public function updatedOverdraftLimit($value)
    {
        $this->overdraft_limit = (float) $value;

        // Ensure overdraft limit is not negative
        if ($value < 0) {
            $this->overdraft_limit = 0;
        }
    }

    public function addSignatory()
    {
        $this->signatories[] = ['name' => '', 'email' => '', 'phone' => ''];
    }

    public function removeSignatory($index)
    {
        if (count($this->signatories) > 1) {
            unset($this->signatories[$index]);
            $this->signatories = array_values($this->signatories);
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;

            if ($this->currentStep === 2) {
                $this->account_type_id = '';
                $this->selectedAccountType = null;
            } elseif ($this->currentStep === 1) {
                $this->customer_id = null;
                $this->selectedCustomer = null;
                $this->customer_type = null;
                $this->dispatch(
                    'update-url',
                    customer_type: null,
                    customer_id: null
                );
            }

            // $this->dispatch('scroll-to-top');
        }
    }

    protected function loadSelectedAccountType()
    {
        if (!$this->account_type_id) {
            $this->selectedAccountType = null;
            return;
        }

        foreach ($this->accountTypes as $type) {
            if ($type['id'] == $this->account_type_id) {
                $this->selectedAccountType = $type;
                return;
            }
        }

        $this->selectedAccountType = null;
    }

    protected function selectCustomerFromUrl($customerId)
    {
        try {
            $customer = Customer::with(['branch', 'accounts.accountType'])->findOrFail($customerId);

            if (!$customer) {
                session()->flash('error', 'Customer not found');
                return redirect()->route('accounts.create');
            }

            // Set customer type based on the customer
            $this->customer_type = $customer->customer_type ?? 'individual';

            // Check if customer is eligible
            $isEligible = $this->isCustomerEligible($customer);

            if (!$isEligible) {
                session()->flash('error', 'Customer is not eligible for account creation. ' .
                    ($this->customer_type === 'individual'
                        ? 'Customer must be active and have verified KYC.'
                        : 'Organization must be active and have verified KYC.'));
                // Still show the customer but prevent account creation
                $this->customer_id = $customer->id;
                $this->loadSelectedCustomerData($customer);
                return redirect()->route('customers.show',$customer->id);
            }

            $this->customer_id = $customer->id;
            $this->loadSelectedCustomerData($customer);
            $this->currentStep = 3;

            // Update URL parameters
            $this->dispatch(
                'update-url',
                customer_type: $this->customer_type,
                customer_id: $customerId
            );

            $this->dispatch('scroll-to-top');
        } catch (\Exception $e) {
            Log::error('Error selecting customer from URL: ' . $e->getMessage());

            session()->flash('error', 'Error loading customer'.$e->getMessage());
            return redirect()->route('accounts.create');
        }
    }

    protected function loadSelectedCustomerData(Customer $customer)
    {
        $accounts = $customer->accounts ?? collect();

        $this->selectedCustomer = [
            'id' => $customer->id,
            'full_name' => $customer->full_name ?? 'Unknown Customer',
            'name' => $customer->full_name ?? $customer->company_name ?? 'Unknown',
            'email' => $customer->email ?? 'N/A',
            'phone' => $customer->phone ?? 'N/A',
            'customer_number' => $customer->customer_number ?? 'N/A',
            'profile_photo_url' => $customer->profile_photo_url ?? $this->getDefaultProfilePhoto($customer->full_name ?? 'Customer'),
            'kyc_status' => $customer->kyc_status ?? 'pending',
            'age' => $customer->date_of_birth ? $customer->date_of_birth->age : null,
            'address' => ($customer->address_line_1 ?? '') . ', ' . ($customer->city ?? ''),
            'existing_accounts' => $accounts->count(),
            'total_balance' => $accounts->sum('current_balance'),
            'accounts' => $accounts->map(function ($account) {
                return [
                    'account_number' => $account->account_number ?? 'N/A',
                    'type' => $account->accountType->name ?? 'N/A',
                    'balance' => $account->current_balance ?? 0,
                    'currency' => $account->currency ?? 'GHS',
                    'status' => $account->status ?? 'unknown',
                ];
            })->toArray(),
            'branch_name' => $customer->branch->name ?? 'N/A',
        ];

        // Add organization-specific fields
        if ($this->customer_type === 'organization') {
            $this->selectedCustomer['organization_type'] = $customer->organization_type ?? 'N/A';
            $this->selectedCustomer['industry'] = $customer->industry ?? 'N/A';
            $this->selectedCustomer['registration_number'] = $customer->registration_number ?? 'N/A';
            $this->selectedCustomer['contact_person'] = $customer->contact_person ?? 'N/A';
            $this->selectedCustomer['tax_identification_number'] = $customer->tax_identification_number ?? 'N/A';
        }
    }

    protected function loadAccountTypes()
    {
        $types = AccountType::where('is_active', true)
            ->orderBy('name')
            ->get();

        $this->accountTypes = $types->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'description' => $type->description,
                'interest_rate' => $type->interest_rate,
                'min_balance' => $type->min_balance,
                'max_balance' => $type->max_balance,
                'status' => $type->status,
                'is_for_organizations' => $type->is_for_organizations ?? false,
                'icon' => $type->icon ?? 'fa-wallet',
            ];
        })->toArray();
    }

    public function updatedCustomerId($value)
    {
        // This method will be called when customer_id changes via URL parameter
        if ($value && !$this->selectedCustomer) {
            $this->selectCustomerFromUrl($value);
        }
    }

    protected function loadBranches()
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            $this->branches = [];
            return;
        }

        if ($user->can('view all branches')) {
            $this->branches = Branch::orderBy('name')->get();
        } else {
            $this->branches = Branch::where('id', $user->branch_id)->get();
        }
    }

    public function getCustomersProperty()
    {
        $user = Auth::user();
        if (!$user instanceof User || !$this->customer_type) {
            return [];
        }

        $query = Customer::query();

        // Filter by customer type
        $query->where('customer_type', $this->customer_type);

        // Filter by branch if user doesn't have all-branch access
        if (!$user->can('view all branches') && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Apply search filter
        if ($this->customerSearch) {
            $query->where(function ($q) {
                $q->where('customer_number', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('first_name', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('last_name', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');

                // Additional search fields for organizations
                if ($this->customer_type === 'organization') {
                    $q->orWhere('company_name', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('registration_number', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('tax_identification_number', 'like', '%' . $this->customerSearch . '%');
                }
            });
        }

        // Only show active customers
        $query->where('status', 'active');

        try {
            $customers = $query->with(['branch', 'accounts'])
                ->orderBy($this->customer_type === 'individual' ? 'first_name' : 'company_name')
                ->get();

            return $customers->map(function ($customer) {
                $isEligible = $this->isCustomerEligible($customer);
                $accounts = $customer->accounts ?? collect();

                $customerData = [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name ?? $customer->company_name ?? 'Unknown',
                    'name' => $customer->full_name ?? $customer->company_name ?? 'Unknown',
                    'email' => $customer->email ?? 'N/A',
                    'phone' => $customer->phone ?? 'N/A',
                    'customer_number' => $customer->customer_number ?? 'N/A',
                    'profile_photo_url' => $customer->profile_photo_url ?? $this->getDefaultProfilePhoto(
                        $this->customer_type === 'individual' ? $customer->full_name : $customer->company_name
                    ),
                    'branch_name' => $customer->branch->name ?? 'N/A',
                    'existing_accounts' => $accounts->count(),
                    'total_balance' => $accounts->sum('current_balance') ?? 0,
                    'kyc_status' => $customer->kyc_status ?? 'pending',
                    'is_eligible' => $isEligible,
                    'is_selectable' => $isEligible,
                ];

                // Add organization-specific fields
                if ($this->customer_type === 'organization') {
                    $customerData['organization_type'] = $customer->organization_type ?? 'N/A';
                    $customerData['registration_number'] = $customer->registration_number ?? 'N/A';
                    $customerData['industry'] = $customer->industry ?? 'N/A';
                    $customerData['contact_person'] = $customer->contact_person ?? 'N/A';
                }

                return $customerData;
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading customers for account creation: ' . $e->getMessage());
            return [];
        }
    }

    protected function isCustomerEligible(Customer $customer): bool
    {
        // Customer must be active
        if ($customer->status !== 'active') {
            return false;
        }

        // Customer must have verified KYC
        if ($customer->kyc_status !== 'verified') {
            return false;
        }

        // Additional checks for individuals
        if ($this->customer_type === 'individual') {
            // Check if customer is at least 18 years old
            // if ($customer->date_of_birth && now()->diffInYears($customer->date_of_birth) < 18) {
            //     return false;
            // }
        }

        // Additional checks for organizations
        if ($this->customer_type === 'organization') {
            // Check if organization has valid registration
            if (empty($customer->registration_number)) {
                return false;
            }

            // Check if organization is not blacklisted
            if ($customer->is_blacklisted ?? false) {
                return false;
            }
        }

        return true;
    }

    public function selectCustomer($customerId)
    {
        try {
            $customer = Customer::with(['branch', 'accounts.accountType'])->findOrFail($customerId);

            if (!$customer) {
                $this->addError('general', 'Customer not found.');
                return;
            }

            // Verify customer type matches
            if ($customer->customer_type !== $this->customer_type) {
                $this->addError('general', 'Selected customer does not match the customer type.');
                return;
            }

            if (!$this->isCustomerEligible($customer)) {
                $this->addError('general', 'Customer is not eligible for account creation. ' .
                    ($this->customer_type === 'individual'
                        ? 'Customer must be active, have verified KYC, and be at least 18 years old.'
                        : 'Organization must be active, have verified KYC, and valid registration documents.'));
                return;
            }

            $this->customer_id = $customerId;
            $this->loadSelectedCustomerData($customer);

            // Move to step 3
            $this->currentStep = 3;

            // Update the URL to reflect the selected customer
            $this->dispatch(
                'update-url',
                customer_type: $this->customer_type,
                customer_id: $customerId
            );

            // $this->dispatch('scroll-to-top');
        } catch (\Exception $e) {
            $this->addError('general', 'Error loading customer: ' . $e->getMessage());
        }
    }

    // Add this method to handle step changes
    public function changeStep($step)
    {
        // Validate step transition
        if ($step < 1 || $step > 4) {
            return;
        }

        // Validate backward navigation
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
            return;
        }

        // Validate forward navigation based on data
        switch ($step) {
            case 2:
                if (!$this->customer_type) {
                    $this->addError('customer_type', 'Please select a customer type first.');
                    return;
                }
                break;

            case 3:
                if (!$this->customer_id) {
                    $this->addError('general', 'Please select a customer first.');
                    return;
                }
                break;

            case 4:
                if (!$this->account_type_id) {
                    $this->addError('account_type_id', 'Please select an account type first.');
                    return;
                }
                break;
        }

        $this->currentStep = $step;

        // Dispatch event to update progress indicators
        $this->dispatch('step-changed', step: $step);
        // $this->dispatch('scroll-to-top');
    }

    public function clearCustomerSelection()
    {
        $this->customer_id = null;
        $this->selectedCustomer = null;
        $this->account_type_id = '';
        $this->selectedAccountType = null;
        $this->initial_deposit = 0;
        $this->minimum_balance = 0;
        $this->overdraft_limit = 0;
        $this->currentStep = 2;

        // Clear the URL parameters
        $this->dispatch(
            'update-url',
            customer_type: $this->customer_type,
            customer_id: null
        );
    }

    public function generateAccountNumber()
    {
        // Generate account number based on customer type
        $prefix = $this->customer_type === 'organization' ? 'ORG' : 'IND';
        $branchCode = str_pad($this->branch_id ?: '001', 3, '0', STR_PAD_LEFT);
        $timestamp = now()->format('ymdHis');
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

        $this->generatedAccountNumber = $prefix . $branchCode . $timestamp . $random;

        // Ensure uniqueness
        while (Account::where('account_number', $this->generatedAccountNumber)->exists()) {
            $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $this->generatedAccountNumber = $prefix . $branchCode . $timestamp . $random;
        }
    }

    public function save()
    {
        // dd($this->all());
        // Validate based on customer type
        $validationRules = [
            'customer_type' => 'required|in:individual,organization',
            'customer_id' => 'required|exists:customers,id',
            'account_type_id' => 'required|exists:account_types,id',
            'currency' => 'required|string|max:3',
            'initial_deposit' => 'required|numeric|min:0',
            'minimum_balance' => 'required|numeric|min:0',
            'overdraft_limit' => 'required|numeric|min:0',
            'status' => 'required|in:active,dormant,restricted,closed',
            'notes' => 'nullable|string|max:1000',
            'generatedAccountNumber' => 'required|string|unique:accounts,account_number',
            'termsAccepted' => 'required|accepted',
        ];

        // Additional validation for organizations
        if ($this->customer_type === 'organization') {
            $validationRules['signatoriesVerified'] = 'required|accepted';

            // Validate signatories
            foreach ($this->signatories as $index => $signatory) {
                $validationRules["signatories.{$index}.name"] = 'required|string|min:3';
                $validationRules["signatories.{$index}.email"] = 'required|email';
                $validationRules["signatories.{$index}.phone"] = 'required|string|min:10';
            }
        }

        $this->validate($validationRules);

        // Check if customer is eligible
        $customer = Customer::find($this->customer_id);
        if (!$this->isCustomerEligible($customer)) {
            $this->addError('general', 'Customer is not eligible for account creation.');
            return;
        }

        // Verify customer type matches
        if ($customer->customer_type !== $this->customer_type) {
            $this->addError('general', 'Customer type mismatch.');
            return;
        }

        // Check minimum balance requirement
        $accountType = AccountType::find($this->account_type_id);
        if ($this->initial_deposit < $accountType->min_balance) {
            $this->addError('initial_deposit', 'Initial deposit must be at least ' . number_format($accountType->min_balance, 2));
            return;
        }

        try {
            DB::beginTransaction();

            // Set branch_id from customer if not specified
            if (!$this->branch_id && $customer->branch_id) {
                $this->branch_id = $customer->branch_id;
            }

            // Prepare metadata
            $metadata = [
                'created_by' => Auth::user()->id,
                'opened_at' => now()->toISOString(),
                'initial_deposit' => $this->initial_deposit,
                'customer_type' => $this->customer_type,
            ];

            // Add organization-specific metadata
            if ($this->customer_type === 'organization') {
                $metadata['signatories'] = $this->signatories;
                $metadata['organization_type'] = $customer->organization_type;
                $metadata['registration_number'] = $customer->registration_number;
                $metadata['tax_identification_number'] = $customer->tax_identification_number;
            }

            // Create the account
            $account = Account::create([
                'customer_id' => $this->customer_id,
                'account_type_id' => $this->account_type_id,
                'branch_id' => Auth::user()->branch->id,
                'account_number' => $this->generatedAccountNumber,
                'currency' => $this->currency,
                'current_balance' => 0,
                'available_balance' => 0,
                'ledger_balance' => 0,
                'minimum_balance' => $this->minimum_balance,
                'overdraft_limit' => $this->overdraft_limit,
                'status' => $this->status,
                'opened_at' => now(),
                'notes' => $this->notes,
                'metadata' => $metadata,
            ]);

            // If initial deposit is greater than 0, create a deposit transaction
            if ($this->initial_deposit > 0) {
                $this->processInitialDeposit($account, $this->initial_deposit);
            }

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($account)
                ->withProperties([
                    'account_number' => $account->account_number,
                    'customer_id' => $account->customer_id,
                    'customer_type' => $this->customer_type,
                    'initial_deposit' => $this->initial_deposit,
                ])
                ->log(($this->customer_type === 'individual' ? 'Individual' : 'Organizational') . ' account created');

            // Log audit
            AuditLogService::log('account_created', $account, null, [
                'account_number' => $account->account_number,
                'customer_id' => $account->customer_id,
                'customer_type' => $this->customer_type,
                'account_type_id' => $account->account_type_id,
                'initial_deposit' => $this->initial_deposit,
                'currency' => $this->currency,
            ], [
                'branch_id' => $this->branch_id,
                'created_by' => Auth::user()->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            session()->flash('success', ($this->customer_type === 'individual' ? 'Individual' : 'Organizational') . ' account created successfully.');
            // Redirect to account details page
            return redirect()->route('accounts.show', $account->id);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Account creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => [
                    'customer_id' => $this->customer_id,
                    'customer_type' => $this->customer_type,
                    'account_type_id' => $this->account_type_id,
                ]
            ]);

            session()->flash('error', 'Failed to create account: ' . $e->getMessage());
            $this->addError('general', 'Failed to create account: ' . $e->getMessage());
        }
    }

    /**
     * Process initial deposit for the new account
     */
    protected function processInitialDeposit(Account $account, float $amount)
    {
        try {
            // Create a deposit transaction
            $transaction = Transaction::create([
                'transaction_reference' => $this->generateTransactionReference(),
                'type' => 'cash_deposit',
                'status' => 'completed',
                'amount' => $amount,
                'currency' => $account->currency,
                'description' => 'Initial deposit for ' . ($this->customer_type === 'individual' ? 'personal' : 'organizational') . ' account opening - ' . $account->account_number,
                'metadata' => [
                    'purpose' => 'account_opening',
                    'customer_type' => $this->customer_type,
                    'branch_id' => $this->branch_id ?? 0,
                    'teller_id' => Auth::user()->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'description' => 'Initial account deposit',
                    'cash_reference' => 'INITIAL' . now()->format('YmdHis'),
                    'initiator_type' => 'self',
                    'receipt_options' => ['sms' => false, 'email' => false, 'print' => true],
                    'transaction_type' => 'cash_deposit',
                    'customer_verified' => true,
                    'cash_denominations' => $this->getDefaultCashDenominations($amount),
                    'processed_by_teller' => true,
                    'verification_method' => 'signature',
                    'cash_handling_method' => 'cash',
                ],
                'initiated_by' => Auth::user()->id,
                'initiated_at' => now(),
                'completed_at' => now(),
                'destination_account_id' => $account->id,
            ]);

            // Create ledger entry for the deposit
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $account->id,
                'entry_type' => 'credit',
                'amount' => $amount,
                'currency' => $account->currency,
                'balance_after' => $amount,
            ]);

            // Update account balances
            $account->update([
                'current_balance' => $amount,
                'available_balance' => $amount,
                'ledger_balance' => $amount,
            ]);

            // Log audit for transaction
            AuditLogService::logTransactionCreated($transaction, [
                'account_number' => $account->account_number,
                'customer_id' => $account->customer_id,
                'customer_type' => $this->customer_type,
                'purpose' => 'account_opening',
                'initial_deposit' => true,
            ]);

            return $transaction;
        } catch (\Exception $e) {
            Log::error('Failed to process initial deposit: ' . $e->getMessage(), [
                'account_id' => $account->id,
                'amount' => $amount,
            ]);
            throw $e;
        }
    }

    /**
     * Generate transaction reference
     */
    protected function generateTransactionReference(): string
    {
        return 'TXN' . now()->format('YmdHis') . strtoupper(Str::random(6));
    }

    /**
     * Generate default cash denominations for the amount
     */
    protected function getDefaultCashDenominations(float $amount): array
    {
        $denominations = [
            ['count' => 0, 'denomination' => 100],
            ['count' => 0, 'denomination' => 50],
            ['count' => 0, 'denomination' => 20],
            ['count' => 0, 'denomination' => 10],
            ['count' => 0, 'denomination' => 5],
            ['count' => 0, 'denomination' => 1],
            ['count' => 0, 'denomination' => 0.5],
            ['count' => 0, 'denomination' => 0.25],
            ['count' => 0, 'denomination' => 0.1],
            ['count' => 0, 'denomination' => 0.05],
            ['count' => 0, 'denomination' => 0.01],
        ];

        $remaining = $amount;
        foreach ($denominations as &$denomination) {
            if ($remaining >= $denomination['denomination']) {
                $count = floor($remaining / $denomination['denomination']);
                $denomination['count'] = $count;
                $remaining = round($remaining - ($count * $denomination['denomination']), 2);
            }
        }

        return $denominations;
    }

    /**
     * Get currency symbol
     */
    public function getCurrencySymbol($currency): string
    {
        $symbols = [
            'GHS' => '₵',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        return $symbols[$currency] ?? $currency;
    }

    /**
     * Get currency name
     */
    public function getCurrencyName($currency): string
    {
        $names = [
            'GHS' => 'Ghana Cedi',
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
        ];

        return $names[$currency] ?? $currency;
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
        // Load selected account type if not loaded
        if ($this->account_type_id && !$this->selectedAccountType) {
            $this->loadSelectedAccountType();
        }

        // Update currency symbol
        $this->currencySymbol = $this->getCurrencySymbol($this->currency);

        return view('livewire.accounts.account-create');
    }
}
