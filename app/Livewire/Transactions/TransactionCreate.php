<?php

namespace App\Livewire\Transactions;

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\Customer;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use App\Models\Eloquent\Beneficiary;
use App\Models\Eloquent\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\Models\Eloquent\TransactionLimit;
use App\Services\Transaction\TransactionService;

class TransactionCreate extends Component
{
    #[Validate('required|in:transfer,withdrawal,deposit,bill_payment,cash_deposit,cheque_deposit,loan_payment,fee_collection,adjustment')]
    public $transactionType = 'transfer';

    #[Validate('required|exists:customers,id')]
    public $customerId = '';

    #[Validate('required|exists:accounts,id')]
    public $sourceAccountId = '';

    #[Validate('required_if:transactionType,transfer,bill_payment|exists:accounts,id')]
    public $destinationAccountId = '';

    #[Validate('required|numeric|min:0.01')]
    public $amount = '';

    #[Validate('required|string|max:255')]
    public $description = '';

    #[Validate('required|string')]
    public $transactionPurpose = '';

    // New: Transaction initiator type (self or third-party)
    public $transactionInitiator = 'self'; // 'self' or 'third_party'
    public $thirdPartyName = '';
    public $thirdPartyIdType = '';
    public $thirdPartyIdNumber = '';
    public $thirdPartyPhone = '';
    public $thirdPartyRelationship = '';
    public $thirdPartyAuthorization = false;
    public $authorizationDocument = '';

    // Beneficiary selection
    public $beneficiaryId = '';
    public $showBeneficiarySection = false;
    public $beneficiaryType = 'internal'; // internal, existing, new

    // New beneficiary fields for external transfers
    #[Validate('required_if:beneficiaryType,new|string|max:255')]
    public $beneficiaryName = '';

    #[Validate('required_if:beneficiaryType,new|string|max:255')]
    public $beneficiaryAccountNumber = '';

    #[Validate('required_if:beneficiaryType,new|string|max:255')]
    public $beneficiaryBankName = '';

    #[Validate('required_if:beneficiaryType,new|string|max:20')]
    public $beneficiaryBankCode = '';

    // Cash handling
    #[Validate('required_if:transactionType,withdrawal,cash_deposit|in:cash,cheque')]
    public $cashHandlingMethod = 'cash';

    #[Validate('required_if:transactionType,withdrawal,cash_deposit|string')]
    public $cashReferenceNumber = '';

    #[Validate('required_if:transactionType,cheque_deposit|string')]
    public $chequeNumber = '';

    #[Validate('required_if:transactionType,cheque_deposit|string')]
    public $drawerBank = '';

    // Loan payment
    #[Validate('required_if:transactionType,loan_payment|string')]
    public $loanAccountNumber = '';

    // Fee collection
    #[Validate('required_if:transactionType,fee_collection|string')]
    public $feeType = '';
    #[Validate('required_if:transactionType,fee_collection|string')]
    public $feeDescription = '';

    // Adjustment
    #[Validate('required_if:transactionType,adjustment|string')]
    public $adjustmentType = '';
    #[Validate('required_if:transactionType,adjustment|string')]
    public $adjustmentReason = '';

    // Bill payment
    #[Validate('required_if:transactionType,bill_payment|string')]
    public $billType = '';
    #[Validate('required_if:transactionType,bill_payment|string')]
    public $billAccountNumber = '';

    // Teller/banker information
    public $tellerId = '';
    public $supervisorApproval = false;
    public $supervisorId = '';
    public $supervisorPassword = '';

    // Transaction verification
    public $customerVerificationMethod = 'signature';
    public $customerSignature = false;
    public $idVerified = false;
    public $idType = '';
    public $idNumber = '';

    // Currency handling
    #[Validate('required|string|size:3')]
    public $currency = 'GHS';
    public $exchangeRate = 1.0;
    public $foreignAmount = 0;

    // Receipt options
    public $printReceipt = true;
    public $emailReceipt = false;
    public $smsReceipt = false;
    public $customerEmail = '';
    public $customerPhone = '';

    // UI State
    public $step = 1;
    public $totalSteps = 4;
    public $showConfirmation = false;
    public $isProcessing = false;
    public $transactionPreview = null;
    public $limits = [];
    public $availableBalance = 0;
    public $accountBalance = 0;

    // Data collections
    public $customerAccounts = [];
    public $allAccounts = [];
    public $beneficiaries = [];
    public $tellers = [];
    public $supervisors = [];

    // Customer search
    public $customerSearch = '';
    public $searchResults = [];
    public $showSearchResults = false;
    public $isSearching = false;
    public $selectedCustomer = null;

    // Relationship options for third party
    public $relationshipOptions = [
        'spouse' => 'Spouse',
        'parent' => 'Parent',
        'child' => 'Child',
        'sibling' => 'Sibling',
        'relative' => 'Relative',
        'friend' => 'Friend',
        'business_partner' => 'Business Partner',
        'employee' => 'Employee',
        'employer' => 'Employer',
        'attorney' => 'Attorney',
        'other' => 'Other',
    ];

    // ID type options
    public $idTypeOptions = [
        'national_id' => 'National ID',
        'passport' => 'Passport',
        'drivers_license' => 'Driver\'s License',
        'voters_id' => 'Voter\'s ID',
        'birth_certificate' => 'Birth Certificate',
        'other' => 'Other',
    ];

    // Transaction purposes
    public $transactionPurposes = [
        'personal' => 'Personal Transaction',
        'business' => 'Business Transaction',
        'salary' => 'Salary Payment',
        'supplier' => 'Supplier Payment',
        'rent' => 'Rent Payment',
        'loan_repayment' => 'Loan Repayment',
        'investment' => 'Investment',
        'education' => 'Education Fee',
        'medical' => 'Medical Expense',
        'utility' => 'Utility Bill',
        'tax' => 'Tax Payment',
        'other' => 'Other',
    ];

    // Fee types
    public $feeTypes = [
        'account_maintenance' => 'Account Maintenance Fee',
        'transaction_fee' => 'Transaction Fee',
        'late_payment' => 'Late Payment Fee',
        'overdraft' => 'Overdraft Fee',
        'wire_transfer' => 'Wire Transfer Fee',
        'cheque_processing' => 'Cheque Processing Fee',
        'card_replacement' => 'Card Replacement Fee',
        'statement_request' => 'Statement Request Fee',
        'other' => 'Other Fee',
    ];

    // Adjustment types
    public $adjustmentTypes = [
        'correction' => 'Balance Correction',
        'interest' => 'Interest Adjustment',
        'charge_reversal' => 'Charge Reversal',
        'bank_error' => 'Bank Error Correction',
        'fraud_reversal' => 'Fraud Reversal',
        'other' => 'Other Adjustment',
    ];

    // Bill types
    public $billTypes = [
        'electricity' => 'Electricity',
        'water' => 'Water',
        'gas' => 'Gas',
        'internet' => 'Internet',
        'mobile' => 'Mobile Phone',
        'cable' => 'Cable TV',
        'credit_card' => 'Credit Card',
        'insurance' => 'Insurance',
        'tax' => 'Tax',
        'other' => 'Other',
    ];

    // Cash denominations (for cash transactions)
    public $cashDenominations = [];

    protected $listeners = [
        'transactionConfirmed' => 'processTransaction',
        'transactionCancelled' => 'cancelTransaction',
        'close-search-results' => 'closeSearchResults',
    ];


    public function mount()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }

        // Check if user has permission to create transactions as banker
        if (!Gate::allows('create transactions')) {
            abort(403, 'Unauthorized access.');
        }

        $this->loadInitialData();

        // Set default teller as current user
        $this->tellerId = Auth::id();

        // Initialize cash denominations
        $this->initializeCashDenominations();
    }

    private function loadInitialData()
    {
        $user = Auth::user();

        // Load all accounts for internal transfers
        $this->allAccounts = Account::active()
            ->with(['customer', 'accountType'])
            ->orderBy('account_number')
            ->get();

        // Load tellers and supervisors from same branch
        if ($user->branch_id) {
            $this->tellers = \App\Models\Eloquent\User::where('branch_id', $user->branch_id)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['teller', 'manager']);
                })
                ->active()
                ->get();

            $this->supervisors = \App\Models\Eloquent\User::where('branch_id', $user->branch_id)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'supervisor');
                })
                ->active()
                ->get();
        }
    }

    // Get search results property
    public function getSearchResultsProperty()
    {
        if (!$this->customerSearch) {
            return [];
        }

        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return [];
        }

        $query = Customer::query();

        // Filter by branch if user doesn't have all-branch access
        if (!$user->can('view all customers') && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Apply search filter
        $query->where(function ($q) {
            $q->where('customer_number', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('first_name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('last_name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('email', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('id_number', 'like', '%' . $this->customerSearch . '%');
        });

        // Only show active customers with verified KYC
        $query->where('status', 'active')
            ->where('kyc_status', 'verified');

        // ONLY SHOW CUSTOMERS WITH AT LEAST ONE ACTIVE ACCOUNT
        $query->whereHas('accounts', function ($q) {
            $q->where('status', 'active');
        });

        try {
            return $query->with(['accounts.accountType'])
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->take(10)
                ->get();
        } catch (\Exception $e) {
            Log::error('Customer search failed: ' . $e->getMessage());
            return [];
        }
    }


    // Updated customer search method
    public function updatedCustomerSearch($value)
    {
        if (empty($value)) {
            $this->searchResults = [];
            $this->showSearchResults = false;
            return;
        }

        $this->isSearching = true;

        // Load search results
        $this->searchResults = $this->getSearchResultsProperty();

        $this->showSearchResults = true;
        $this->isSearching = false;
    }

    // Select customer method
    public function selectCustomer($customerId)
    {
        $customer = Customer::with(['accounts.accountType'])->find($customerId);

        if ($customer) {
            $this->customerId = $customer->id;
            $this->customerSearch = $customer->full_name . ' (#' . $customer->customer_number . ')';

            // Store selected customer data
            $this->selectedCustomer = [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'customer_number' => $customer->customer_number,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'id_number' => $customer->id_number,
                'profile_photo_url' => $customer->profile_photo_url ?? $this->getDefaultProfilePhoto($customer->full_name),
                'kyc_status' => $customer->kyc_status,
                'accounts' => $customer->accounts->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'account_number' => $account->account_number,
                        'current_balance' => $account->current_balance,
                        'available_balance' => $account->available_balance,
                        'currency' => $account->currency,
                        'status' => $account->status,
                        'account_type' => $account->accountType ? [
                            'name' => $account->accountType->name,
                        ] : null,
                    ];
                })->toArray(),
            ];

            // Load customer accounts for selection
            $this->customerAccounts = $customer->accounts()
                ->active()
                ->with(['accountType'])
                ->get();

            // Load customer beneficiaries
            $this->beneficiaries = $customer->beneficiaries()
                ->active()
                ->verified()
                ->get();

            // Set customer contact info for receipts
            $this->customerEmail = $customer->email;
            $this->customerPhone = $customer->phone;

            // Reset account selection and transaction initiator
            $this->reset(['sourceAccountId', 'destinationAccountId', 'amount', 'transactionInitiator']);
            $this->updateAvailableBalance();

            // Close search results
            $this->closeSearchResults();

            // Dispatch event to focus on amount field
            $this->dispatch('customer-selected');
        }
    }

    public function closeSearchResults()
    {
        $this->showSearchResults = false;
        $this->searchResults = [];
    }

    public function clearCustomerSelection()
    {
        $this->reset([
            'customerId',
            'customerSearch',
            'customerAccounts',
            'sourceAccountId',
            'destinationAccountId',
            'amount',
            'beneficiaries',
            'customerEmail',
            'customerPhone',
            'selectedCustomer',
            'transactionInitiator',
            'thirdPartyName',
            'thirdPartyIdType',
            'thirdPartyIdNumber',
            'thirdPartyPhone',
            'thirdPartyRelationship',
            'thirdPartyAuthorization',
            'authorizationDocument'
        ]);
        $this->closeSearchResults();
    }

    public function updatedTransactionInitiator($value)
    {
        if ($value === 'self') {
            // Clear third party fields if switching to self
            $this->reset([
                'thirdPartyName',
                'thirdPartyIdType',
                'thirdPartyIdNumber',
                'thirdPartyPhone',
                'thirdPartyRelationship',
                'thirdPartyAuthorization',
                'authorizationDocument'
            ]);
        }
    }

    public function updatedCustomerId($value)
    {
        if ($value) {
            $customer = Customer::with(['accounts.accountType'])->find($value);
            if ($customer) {
                $this->customerAccounts = $customer->accounts()
                    ->active()
                    ->with(['accountType'])
                    ->get();

                $this->customerEmail = $customer->email;
                $this->customerPhone = $customer->phone;
                $this->selectedCustomer = [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'customer_number' => $customer->customer_number,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'id_number' => $customer->id_number,
                    'profile_photo_url' => $customer->profile_photo_url ?? $this->getDefaultProfilePhoto($customer->full_name),
                    'kyc_status' => $customer->kyc_status,
                ];

                // Load customer beneficiaries
                $this->beneficiaries = $customer->beneficiaries()
                    ->active()
                    ->verified()
                    ->get();
            }

            $this->reset(['sourceAccountId', 'destinationAccountId', 'amount']);
            $this->updateAvailableBalance();
        }
    }

    public function updatedSourceAccountId($value)
    {
        if ($value) {
            $account = Account::with(['accountType'])->find($value);
            if ($account) {
                $this->accountBalance = $account->current_balance;
                $this->availableBalance = $account->available_balance;
                $this->currency = $account->currency;

                // Load transaction limits
                $this->loadTransactionLimits();

                // Dispatch event to focus on amount field
                $this->dispatch('account-selected');
            }
        }
    }

    private function initializeCashDenominations()
    {
        $this->cashDenominations = [
            ['denomination' => 100, 'count' => 0],
            ['denomination' => 50, 'count' => 0],
            ['denomination' => 20, 'count' => 0],
            ['denomination' => 10, 'count' => 0],
            ['denomination' => 5, 'count' => 0],
            ['denomination' => 1, 'count' => 0],
            ['denomination' => 0.50, 'count' => 0],
            ['denomination' => 0.25, 'count' => 0],
            ['denomination' => 0.10, 'count' => 0],
            ['denomination' => 0.05, 'count' => 0],
            ['denomination' => 0.01, 'count' => 0],
        ];
    }

    public function updatedTransactionType($value)
    {
        $this->reset([
            'destinationAccountId',
            'beneficiaryId',
            'beneficiaryType',
            'cashHandlingMethod',
            'cashReferenceNumber',
            'chequeNumber',
            'drawerBank',
            'loanAccountNumber',
            'feeType',
            'feeDescription',
            'adjustmentType',
            'adjustmentReason',
            'billType',
            'billAccountNumber',
        ]);

        // Show beneficiary section for transfers and bill payments
        if (in_array($value, ['transfer', 'bill_payment'])) {
            $this->showBeneficiarySection = true;
        } else {
            $this->showBeneficiarySection = false;
        }

        // Set default description based on type
        $this->updateDescription();

        // Clear validation errors
        $this->resetErrorBag();
    }

    public function updatedAmount($value)
    {
        // Ensure value is numeric before processing
        $value = (float) $value;

        $this->validateAmount();

        // Auto-calculate cash denominations for cash transactions
        if (
            in_array($this->transactionType, ['withdrawal', 'cash_deposit']) &&
            $this->cashHandlingMethod === 'cash' &&
            is_numeric($value) && $value > 0
        ) {
            $this->calculateCashDenominations($value);
        }
    }

    public function updatedCurrency($value)
    {
        // For GHS currency, set exchange rate to 1
        if ($value === 'GHS') {
            $this->exchangeRate = 1.0;
        } elseif ($value !== 'GHS') {
            // In real app, fetch exchange rate from API
            $this->exchangeRate = 1.1; // Example: 1 USD = 1.1 EUR
        } else {
            $this->exchangeRate = 1.0;
        }

        $this->calculateForeignAmount();
    }

    public function calculateForeignAmount()
    {
        if ($this->amount && $this->exchangeRate && $this->currency !== 'USD') {
            $amount = (float) $this->amount;
            $this->foreignAmount = $amount * $this->exchangeRate;
        } else {
            $this->foreignAmount = 0;
        }
    }

    private function calculateCashDenominations($amount)
    {
        $remaining = $amount;

        foreach ($this->cashDenominations as $key => $denomination) {
            if ($remaining >= $denomination['denomination']) {
                $count = floor($remaining / $denomination['denomination']);
                $this->cashDenominations[$key]['count'] = $count;
                $remaining = round($remaining - ($count * $denomination['denomination']), 2);
            } else {
                $this->cashDenominations[$key]['count'] = 0;
            }
        }
    }

    private function loadTransactionLimits()
    {
        if (!$this->sourceAccountId) {
            $this->limits = [];
            return;
        }

        $account = Account::with(['accountType'])->find($this->sourceAccountId);
        if (!$account || !$account->accountType) {
            $this->limits = [];
            return;
        }

        $this->limits = TransactionLimit::where('account_type_id', $account->account_type_id)
            ->where('transaction_type', $this->transactionType)
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(function ($limit) {
                return [
                    $limit->period => [
                        'max_amount' => $limit->max_amount,
                        'max_count' => $limit->max_count,
                    ]
                ];
            })
            ->toArray();
    }

    private function updateDescription()
    {
        $descriptions = [
            'transfer' => 'Fund Transfer',
            'withdrawal' => 'Cash Withdrawal',
            'deposit' => 'Account Deposit',
            'cash_deposit' => 'Cash Deposit',
            'cheque_deposit' => 'Cheque Deposit',
            'bill_payment' => 'Bill Payment',
            'loan_payment' => 'Loan Payment',
            'fee_collection' => 'Fee Collection',
            'adjustment' => 'Balance Adjustment',
        ];

        $this->description = $descriptions[$this->transactionType] ?? 'Bank Transaction';
    }

    private function validateAmount()
    {
        if (!$this->amount || !is_numeric($this->amount) || $this->amount <= 0) {
            return;
        }

        $amount = (float) $this->amount;
        $availableBalance = (float) $this->availableBalance;

        // Check if source account has sufficient balance for withdrawals/transfers
        if (
            in_array($this->transactionType, ['withdrawal', 'transfer', 'bill_payment', 'loan_payment', 'fee_collection'])
            && $availableBalance < $amount
        ) {
            $this->addError('amount', 'Insufficient funds. Available balance: ' . number_format($availableBalance, 2));
            return;
        }

        // Check against transaction limits
        if (!empty($this->limits['per_transaction'])) {
            $maxAmount = $this->limits['per_transaction']['max_amount'];
            if ($maxAmount && $amount > $maxAmount) {
                $this->addError('amount', "Maximum amount per transaction is " . number_format($maxAmount, 2));
            }
        }
    }

    public function nextStep()
    {
        // Validate current step before moving forward
        if (!$this->validateCurrentStep()) {
            // Show error message if validation fails
            $this->dispatch('showToast', [
                'message' => 'Please fill in all required fields correctly.',
                'type' => 'error'
            ]);
            return; // Don't proceed to next step
        }

        if ($this->step < $this->totalSteps) {
            $this->step++;

            // If moving to step 4 (final review), auto-calculate cash denominations and show confirmation
            if ($this->step === 4) {
                // Auto-calculate cash denominations for cash transactions
                if (
                    in_array($this->transactionType, ['withdrawal', 'cash_deposit'])
                    && $this->cashHandlingMethod === 'cash' && $this->amount
                ) {
                    $this->calculateCashDenominations((float) $this->amount);
                }

                $this->showConfirmation = true;
                $this->prepareTransactionPreview();
            }
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;

            // If going back from step 3, hide beneficiary section if not applicable
            if ($this->step === 2 && !in_array($this->transactionType, ['transfer', 'bill_payment'])) {
                $this->showBeneficiarySection = false;
            }
        }
    }

    private function validateCurrentStep()
    {
        try {
            switch ($this->step) {
                case 1: // Customer and Transaction Details
                    $this->validate([
                        'customerId' => 'required|exists:customers,id',
                        'transactionType' => 'required|in:transfer,withdrawal,deposit,cash_deposit,cheque_deposit,bill_payment,loan_payment,fee_collection,adjustment',
                        'sourceAccountId' => 'required|exists:accounts,id',
                        'amount' => 'required|numeric|min:0.01',
                        'description' => 'required|string|max:255',
                        'transactionPurpose' => 'required|string',
                    ]);

                    // Validate source account has sufficient funds for debit transactions
                    if (in_array($this->transactionType, ['withdrawal', 'transfer', 'bill_payment', 'loan_payment', 'fee_collection'])) {
                        $account = Account::find($this->sourceAccountId);
                        if ($account && (float)$this->amount > $account->available_balance + $account->overdraft_limit) {
                            $this->addError('amount', 'Insufficient funds. Available balance: ' . number_format($account->available_balance, 2));
                            return false;
                        }
                    }

                    // Additional validation for specific transaction types
                    if ($this->transactionType === 'withdrawal' || $this->transactionType === 'cash_deposit') {
                        $this->validate([
                            'cashHandlingMethod' => 'required|in:cash,cheque',
                            'cashReferenceNumber' => 'required_if:cashHandlingMethod,cash|string',
                        ]);
                    }

                    if ($this->transactionType === 'cheque_deposit') {
                        $this->validate([
                            'chequeNumber' => 'required|string',
                            'drawerBank' => 'required|string',
                        ]);
                    }

                    if ($this->transactionType === 'loan_payment') {
                        $this->validate([
                            'loanAccountNumber' => 'required|string',
                        ]);
                    }

                    if ($this->transactionType === 'fee_collection') {
                        $this->validate([
                            'feeType' => 'required|string',
                            'feeDescription' => 'required|string',
                        ]);
                    }

                    if ($this->transactionType === 'adjustment') {
                        $this->validate([
                            'adjustmentType' => 'required|string',
                            'adjustmentReason' => 'required|string',
                        ]);
                    }

                    if ($this->transactionType === 'bill_payment') {
                        $this->validate([
                            'billType' => 'required|string',
                            'billAccountNumber' => 'required|string',
                        ]);
                    }

                    // For transfers, validate beneficiary selection
                    if ($this->transactionType === 'transfer') {
                        if ($this->beneficiaryType === 'internal' && !$this->destinationAccountId) {
                            $this->addError('destinationAccountId', 'Please select a destination account for internal transfer');
                            return false;
                        }
                    }

                    break;
                    case 2: // Transaction Initiator (Self or Third Party)
                    $this->validate([
                        'transactionInitiator' => 'required|in:self,third_party',
                    ]);

                    if ($this->transactionInitiator === 'third_party') {
                        $this->validate([
                            'thirdPartyName' => 'required|string|max:255',
                            'thirdPartyIdType' => 'required|string',
                            'thirdPartyIdNumber' => 'required|string|max:50',
                            'thirdPartyPhone' => 'required|string|max:20',
                            'thirdPartyRelationship' => 'required|string',
                            'thirdPartyAuthorization' => 'required|boolean',
                        ]);

                        if ($this->thirdPartyAuthorization) {
                            $this->validate([
                                'authorizationDocument' => 'required|string|max:255',
                            ]);
                        }
                    }
                    break;

                case 3: // Verification and Receipt Options (now step 3)
                    $this->validate([
                        'customerVerificationMethod' => 'required|in:signature,id,biometric',
                        'customerSignature' => 'required|boolean',
                        'idVerified' => 'required|boolean',
                    ]);

                    if ($this->customerVerificationMethod === 'id') {
                        $this->validate([
                            'idType' => 'required|string',
                            'idNumber' => 'required|string',
                        ]);
                    }

                    // IMPORTANT FIX: Validate supervisor ID if supervisor approval is checked
                    if ($this->supervisorApproval) {
                        $this->validate([
                            'supervisorId' => 'required|exists:users,id',
                        ], [
                            'supervisorId.required' => 'Please select a supervisor when supervisor approval is required.',
                            'supervisorId.exists' => 'The selected supervisor does not exist.',
                        ]);
                    }

                    if ($this->emailReceipt) {
                        $this->validate([
                            'customerEmail' => 'required|email',
                        ]);
                    }

                    if ($this->smsReceipt) {
                        $this->validate([
                            'customerPhone' => 'required|string',
                        ]);
                    }
                    break;
            }

            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Get the first error message
            $errors = $e->validator->errors()->all();
            $firstError = !empty($errors) ? $errors[0] : 'Please fill in all required fields correctly.';

            session()->flash('error', 'Error');
            return false;
        }
    }

    public function getTotalCashCount()
    {
        if (empty($this->cashDenominations)) {
            return '0.00';
        }

        $total = 0;
        foreach ($this->cashDenominations as $denomination) {
            $total += $denomination['denomination'] * $denomination['count'];
        }
        return number_format($total, 2);
    }

    private function prepareTransactionPreview()
    {
        $customer = Customer::find($this->customerId);
        $sourceAccount = Account::find($this->sourceAccountId);
        $destinationAccount = $this->destinationAccountId ? Account::find($this->destinationAccountId) : null;
        $beneficiary = $this->beneficiaryId ? Beneficiary::find($this->beneficiaryId) : null;
        $teller = \App\Models\Eloquent\User::find($this->tellerId);
        $supervisor = $this->supervisorId ? \App\Models\Eloquent\User::find($this->supervisorId) : null;

        // Calculate balance after transaction
        $balanceAfter = $this->accountBalance;
        if (in_array($this->transactionType, ['withdrawal', 'transfer', 'bill_payment', 'loan_payment', 'fee_collection'])) {
            $balanceAfter -= (float) $this->amount;
        } elseif (in_array($this->transactionType, ['deposit', 'cash_deposit', 'cheque_deposit'])) {
            $balanceAfter += (float) $this->amount;
        }

        $this->transactionPreview = [
            'type' => $this->transactionType,
            'type_display' => ucfirst(str_replace('_', ' ', $this->transactionType)),
            'initiator_type' => $this->transactionInitiator,
            'third_party_info' => $this->transactionInitiator === 'third_party' ? [
                'name' => $this->thirdPartyName,
                'id_type' => $this->thirdPartyIdType,
                'id_number' => $this->thirdPartyIdNumber,
                'phone' => $this->thirdPartyPhone,
                'relationship' => $this->thirdPartyRelationship,
                'authorization_document' => $this->authorizationDocument,
            ] : null,
            'customer' => $customer ? [
                'name' => $customer->full_name,
                'number' => $customer->customer_number,
                'id' => $customer->id_number,
            ] : null,
            'source_account' => $sourceAccount ? [
                'number' => $sourceAccount->account_number,
                'name' => $sourceAccount->accountType->name,
                'balance_before' => number_format($this->accountBalance, 2),
                'balance_after' => number_format($balanceAfter, 2),
            ] : null,
            'destination_account' => $destinationAccount ? [
                'number' => $destinationAccount->account_number,
                'name' => $destinationAccount->accountType->name,
                'customer' => $destinationAccount->customer->full_name,
            ] : null,
            'beneficiary' => $beneficiary ? [
                'name' => $beneficiary->full_name,
                'account' => $beneficiary->account_number,
                'bank' => $beneficiary->bank_name,
            ] : null,
            'amount' => number_format((float) $this->amount, 2),
            'currency' => $this->currency,
            'foreign_amount' => $this->foreignAmount ? number_format((float) $this->foreignAmount, 2) : null,
            'description' => $this->description,
            'purpose' => $this->transactionPurpose,
            'teller' => $teller ? $teller->name : null,
            'supervisor' => $supervisor ? $supervisor->name : null,
            'verification' => [
                'method' => $this->customerVerificationMethod,
                'signature' => $this->customerSignature,
                'id_verified' => $this->idVerified,
                'id_type' => $this->idType,
                'id_number' => $this->idNumber,
            ],
            'receipt_options' => [
                'print' => $this->printReceipt,
                'email' => $this->emailReceipt,
                'sms' => $this->smsReceipt,
            ],
            'cash_denominations' => $this->cashDenominations,
            'metadata' => $this->prepareMetadata(),
        ];
    }

    private function prepareMetadata()
    {
        $metadata = [
            'transaction_type' => $this->transactionType,
            'description' => $this->description,
            'purpose' => $this->transactionPurpose,
            'processed_by_teller' => true,
            'teller_id' => $this->tellerId,
            'branch_id' => Auth::user()->branch_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'customer_verified' => true,
            'verification_method' => $this->customerVerificationMethod,
            'initiator_type' => $this->transactionInitiator,
        ];

        // Add third party information if applicable
        if ($this->transactionInitiator === 'third_party') {
            $metadata['third_party'] = [
                'name' => $this->thirdPartyName,
                'id_type' => $this->thirdPartyIdType,
                'id_number' => $this->thirdPartyIdNumber,
                'phone' => $this->thirdPartyPhone,
                'relationship' => $this->thirdPartyRelationship,
                'authorization_document' => $this->authorizationDocument,
                'authorization_verified' => $this->thirdPartyAuthorization,
            ];
        }

        // Add type-specific metadata
        switch ($this->transactionType) {
            case 'withdrawal':
            case 'cash_deposit':
                $metadata['cash_handling_method'] = $this->cashHandlingMethod;
                $metadata['cash_reference'] = $this->cashReferenceNumber;
                $metadata['cash_denominations'] = $this->cashDenominations;
                break;

            case 'cheque_deposit':
                $metadata['cheque_number'] = $this->chequeNumber;
                $metadata['drawer_bank'] = $this->drawerBank;
                break;

            case 'loan_payment':
                $metadata['loan_account'] = $this->loanAccountNumber;
                break;

            case 'fee_collection':
                $metadata['fee_type'] = $this->feeType;
                $metadata['fee_description'] = $this->feeDescription;
                break;

            case 'adjustment':
                $metadata['adjustment_type'] = $this->adjustmentType;
                $metadata['adjustment_reason'] = $this->adjustmentReason;
                break;

            case 'bill_payment':
                $metadata['bill_type'] = $this->billType;
                $metadata['bill_account'] = $this->billAccountNumber;
                break;
        }

        // Add supervisor approval if required
        if ($this->supervisorApproval) {
            $metadata['supervisor_approval'] = true;
            $metadata['supervisor_id'] = $this->supervisorId;
            $metadata['requires_supervisor_approval'] = true;
        }

        // Add receipt options
        $metadata['receipt_options'] = [
            'print' => $this->printReceipt,
            'email' => $this->emailReceipt,
            'sms' => $this->smsReceipt,
        ];

        return $metadata;
    }

    public function confirmTransaction()
    {
        // Verify supervisor password if required
        if ($this->supervisorApproval && $this->supervisorPassword) {
            $supervisor = \App\Models\Eloquent\User::find($this->supervisorId);
            if (!$supervisor || !Hash::check($this->supervisorPassword, $supervisor->password)) {
                $this->addError('supervisorPassword', 'Invalid supervisor password');
                return;
            }
        }

        // Process the transaction
        $this->processTransaction();
    }

    public function processTransaction()
    {
        $this->isProcessing = true;

        try {
            $transactionService = app(TransactionService::class);

            // Get the source account
            $sourceAccount = Account::find($this->sourceAccountId);
            if (!$sourceAccount) {
                throw new \Exception('Source account not found');
            }

            // Prepare common transaction data
            $transactionData = [
                'amount' => (float) $this->amount,
                'currency' => $this->currency,
                'description' => $this->description,
                'metadata' => $this->prepareMetadata(),
                'initiated_by' => Auth::id(),
                'teller_id' => $this->tellerId,
                'customer_id' => $this->customerId,
                'branch_id' => Auth::user()->branch_id,
            ];

            // Process based on transaction type
            $transaction = null;

            switch ($this->transactionType) {
                case 'transfer':
                    if ($this->beneficiaryType === 'internal' && $this->destinationAccountId) {
                        $transactionData['from_account_id'] = $this->sourceAccountId;
                        $transactionData['to_account_id'] = $this->destinationAccountId;
                        $transaction = $transactionService->transfer($transactionData);
                    } elseif ($this->beneficiaryType === 'existing' && $this->beneficiaryId) {
                        $beneficiary = Beneficiary::find($this->beneficiaryId);
                        if ($beneficiary && $beneficiary->internal_account_id) {
                            $transactionData['from_account_id'] = $this->sourceAccountId;
                            $transactionData['to_account_id'] = $beneficiary->internal_account_id;
                            $transaction = $transactionService->transfer($transactionData);
                        } else {
                            throw new \Exception('Beneficiary internal account not found');
                        }
                    } else {
                        throw new \Exception('Please select a destination account or beneficiary');
                    }
                    break;

                case 'withdrawal':
                    $transactionData['account_id'] = $this->sourceAccountId;
                    $transactionData['method'] = $this->cashHandlingMethod;
                    $transactionData['external_reference'] = $this->cashReferenceNumber;
                    $transaction = $transactionService->withdraw($transactionData);
                    break;

                case 'cash_deposit':
                    $transactionData['account_id'] = $this->sourceAccountId;
                    $transactionData['method'] = 'cash';
                    $transactionData['external_reference'] = $this->cashReferenceNumber;
                    $transaction = $transactionService->cashDeposit($transactionData);
                    break;

                case 'cheque_deposit':
                    $transactionData['account_id'] = $this->sourceAccountId;
                    $transactionData['method'] = 'cheque';
                    $transactionData['external_reference'] = $this->chequeNumber;
                    $transactionData['drawer_bank'] = $this->drawerBank;
                    $transaction = $transactionService->deposit($transactionData);
                    break;

                case 'bill_payment':
                    if ($this->beneficiaryType === 'internal' && $this->destinationAccountId) {
                        $transactionData['from_account_id'] = $this->sourceAccountId;
                        $transactionData['to_account_id'] = $this->destinationAccountId;
                        $transactionData['metadata']['bill_type'] = $this->billType;
                        $transactionData['metadata']['bill_account'] = $this->billAccountNumber;
                        $transaction = $transactionService->transfer($transactionData);
                    } else {
                        throw new \Exception('Please select a destination account for bill payment');
                    }
                    break;

                default:
                    throw new \Exception("Transaction type '{$this->transactionType}' is not yet fully implemented");
            }

            if (!$transaction) {
                throw new \Exception('Failed to create transaction');
            }

            // Send receipts if requested
            if ($this->printReceipt) {
                $this->printTransactionReceipt($transaction);
            }

            if ($this->emailReceipt && $this->customerEmail) {
                $this->sendEmailReceipt($transaction, $this->customerEmail);
            }

            if ($this->smsReceipt && $this->customerPhone) {
                $this->sendSmsReceipt($transaction, $this->customerPhone);
            }

            // Reset form
            $this->resetForm();

            session()->flash('success', 'Account created successfully.');
            // Redirect to account details page
            return redirect()->route('transactions.show', $transaction->id);

        } catch (\Exception $e) {
            $this->dispatch(
                'showToast',
                message: 'Transaction failed: ' . $e->getMessage(),
                type: 'error'
            );
            Log::error('Transaction failed: ' . $e->getMessage(), [
                'transaction_type' => $this->transactionType,
                'source_account_id' => $this->sourceAccountId,
                'amount' => $this->amount,
                'user_id' => Auth::id(),
                'error_trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->isProcessing = false;
        }
    }

    private function createNewBeneficiary()
    {
        $customer = Customer::find($this->customerId);

        $beneficiary = Beneficiary::create([
            'tenant_id' => Auth::user()->tenant_id,
            'customer_id' => $this->customerId,
            'created_by' => Auth::id(),
            'beneficiary_type' => 'domestic',
            'entity_type' => 'individual',
            'full_name' => $this->beneficiaryName,
            'account_number' => $this->beneficiaryAccountNumber,
            'account_name' => $this->beneficiaryName,
            'bank_name' => $this->beneficiaryBankName,
            'bank_code' => $this->beneficiaryBankCode,
            'verification_status' => 'pending',
            'is_active' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return $beneficiary;
    }

    private function printTransactionReceipt($transaction)
    {
        // In real app, this would trigger a printer
        // For now, just log it
        Log::info('Printing receipt for transaction: ' . $transaction->transaction_reference);
    }

    private function sendEmailReceipt($transaction, $email)
    {
        // In real app, this would send an email
        // For now, just log it
        Log::info('Emailing receipt to: ' . $email . ' for transaction: ' . $transaction->transaction_reference);
    }

    private function sendSmsReceipt($transaction, $phone)
    {
        // In real app, this would send an SMS
        // For now, just log it
        Log::info('SMS receipt to: ' . $phone . ' for transaction: ' . $transaction->transaction_reference);
    }

    public function cancelTransaction()
    {
        $this->showConfirmation = false;
        $this->transactionPreview = null;
    }

    public function resetForm()
    {
        $this->reset([
            'transactionType',
            'customerId',
            'sourceAccountId',
            'destinationAccountId',
            'amount',
            'description',
            'transactionPurpose',
            'transactionInitiator',
            'thirdPartyName',
            'thirdPartyIdType',
            'thirdPartyIdNumber',
            'thirdPartyPhone',
            'thirdPartyRelationship',
            'thirdPartyAuthorization',
            'authorizationDocument',
            'beneficiaryId',
            'showBeneficiarySection',
            'beneficiaryType',
            'beneficiaryName',
            'beneficiaryAccountNumber',
            'beneficiaryBankName',
            'beneficiaryBankCode',
            'cashHandlingMethod',
            'cashReferenceNumber',
            'chequeNumber',
            'drawerBank',
            'loanAccountNumber',
            'feeType',
            'feeDescription',
            'adjustmentType',
            'adjustmentReason',
            'billType',
            'billAccountNumber',
            'tellerId',
            'supervisorApproval',
            'supervisorId',
            'supervisorPassword',
            'customerVerificationMethod',
            'customerSignature',
            'idVerified',
            'idType',
            'idNumber',
            'currency',
            'exchangeRate',
            'foreignAmount',
            'printReceipt',
            'emailReceipt',
            'smsReceipt',
            'customerEmail',
            'customerPhone',
            'step',
            'showConfirmation',
            'isProcessing',
            'transactionPreview',
            'cashDenominations',
            'customerSearch',
            'selectedCustomer',
        ]);

        // Reload initial data
        $this->loadInitialData();

        // Set default values
        $this->tellerId = Auth::id();
        $this->currency = 'GHS';
        $this->exchangeRate = 1.0;
        $this->foreignAmount = 0;
        $this->transactionInitiator = 'self';
        $this->initializeCashDenominations();
    }

    private function updateAvailableBalance()
    {
        if ($this->sourceAccountId) {
            $account = Account::find($this->sourceAccountId);
            if ($account) {
                $this->availableBalance = $account->available_balance;
                $this->accountBalance = $account->current_balance;
            }
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
        return view('livewire.transactions.transaction-create');
    }
}
