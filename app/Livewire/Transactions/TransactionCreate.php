<?php

namespace App\Livewire\Transactions;

use App\Models\Eloquent\Account;
use App\Models\Eloquent\Beneficiary;
use App\Models\Eloquent\Customer;
use App\Models\Eloquent\Transaction;
use App\Models\Eloquent\TransactionLimit;
use App\Models\Eloquent\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

class TransactionCreate extends Component
{
    #[Validate('required|in:transfer,withdrawal,deposit,cash_deposit,cheque_deposit,bill_payment,loan_payment,fee_collection,adjustment,initial_deposit')]
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

    #[Validate('required_if:transactionType,transfer,deposit,cash_deposit,cheque_deposit,loan_payment,fee_collection,adjustment|string')]
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
    public $supervisors;

    // Customer search
    public $accountSearch = '';
    public $searchResults = [];
    public $showSearchResults = false;
    public $isSearching = false;
    public $selectedCustomer = null;
    public $selectedAccount = null;

    /**
     * Teller limit configuration
     */
    public $tellerLimit = 0;
    public $exceedsTellerLimit = false;
    public $requiresSupervisorApproval = false;
    public $supervisorApprovalReason = null;
    public $tellerTransactionLimits = [];
    public $supervisorOverride = false;

    /**
     * Transaction limit check results
     */
    public $limitCheckResults = [];
    public $limitViolations = [];

    /**
     * Supervisor approval tracking
     */
    public $supervisorApprovalRequired = false;
    public $supervisorApprovalStatus = 'pending'; // pending, approved, rejected
    public $supervisorApprovalNotes = '';
    public $supervisorApprovedAt = null;
    public $supervisorApprovedBy = null;

    // Flag to check if initial deposit is done
    public $hasInitialDeposit = false;

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

    // Transaction purposes - NOW ONLY FOR NON-WITHDRAWAL TRANSACTIONS
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
        $this->loadTellerLimits();

        // Set default teller as current user
        $this->tellerId = Auth::id();

        // Initialize cash denominations
        $this->initializeCashDenominations();

        // Ensure total steps is 4
        $this->totalSteps = 4;
        $this->step = 1;
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
                ->where(function ($q) {
                    $q->where('role', 'teller')
                        ->orWhere('role', 'manager');
                })
                ->where('status', 'active')
                ->get();

            $this->supervisors = \App\Models\Eloquent\User::where('branch_id', $user->branch_id)
                ->where('role', 'supervisor')
                ->where('status', 'active')
                ->get();
        }
    }

    // Get available transaction types based on initial deposit status
    public function getAvailableTransactionTypes()
    {
        $types = [
            'transfer' => 'Transfer',
            'withdrawal' => 'Withdrawal',
            'cash_deposit' => 'Cash Deposit',
            'cheque_deposit' => 'Cheque Deposit',
            'bill_payment' => 'Bill Payment',
            'loan_payment' => 'Loan Payment',
            'fee_collection' => 'Fee Collection',
            'adjustment' => 'Adjustment',
        ];

        // If no initial deposit has been made, only show initial deposit option
        if (!$this->hasInitialDeposit && $this->sourceAccountId) {
            return ['initial_deposit' => 'Initial Deposit'];
        }

        return $types;
    }

    // Get search results property - NOW SEARCHES BY ACCOUNT NUMBER ONLY
    public function getSearchResultsProperty()
    {
        if (!$this->accountSearch || strlen($this->accountSearch) < 3) {
            return [];
        }

        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return [];
        }

        // Search for accounts by account number only
        $query = Account::query()
            ->where('account_number', 'like', '%' . $this->accountSearch . '%')
            ->where('status', 'active')
            ->with(['customer', 'accountType']);

        // Filter by branch if user doesn't have all-branch access
        if (!$user->can('view all customers') && $user->branch_id) {
            $query->whereHas('customer', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        try {
            return $query->limit(10)->get();
        } catch (\Exception $e) {
            Log::error('Account search failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Load teller limits based on user role and account type
     */
    private function loadTellerLimits()
    {
        $user = Auth::user();

        // Get teller's default limit from user settings or configuration
        // This can be stored in user profile, role-based settings, or configuration
        $this->tellerLimit = $this->getTellerLimitForUser($user);

        // Load transaction limits from database for all account types
        $this->tellerTransactionLimits = TransactionLimit::where('is_active', true)
            ->with('accountType')
            ->get()
            ->groupBy('account_type_id')
            ->toArray();
    }

    /**
     * Get teller limit for user (can be customized based on role, experience, etc.)
     */
    private function getTellerLimitForUser($user)
    {
        // Default teller limit
        $defaultLimit = 10000;

        if (!$user) {
            return $defaultLimit;
        }

        // Check if user has a custom limit in their profile
        if (isset($user->teller_limit) && $user->teller_limit > 0) {
            return $user->teller_limit;
        }

        // Set limits based on role
        switch ($user->role) {
            case 'supervisor':
            case 'manager':
                return 50000; // Higher limit for supervisors
            case 'senior_teller':
                return 25000; // Higher limit for senior tellers
            case 'teller':
                return $defaultLimit;
            default:
                return $defaultLimit;
        }
    }

    /**
     * Check if transaction exceeds teller limit
     */
    private function checkTellerLimit()
    {
        if (!$this->amount || !is_numeric($this->amount)) {
            $this->exceedsTellerLimit = false;
            $this->requiresSupervisorApproval = false;
            return;
        }

        $amount = (float) $this->amount;

        // Reset violations array
        $this->limitViolations = [];
        $this->requiresSupervisorApproval = false;

        // Check if amount exceeds teller's personal limit
        if ($amount >= $this->tellerLimit) {
            $this->exceedsTellerLimit = true;
            $this->requiresSupervisorApproval = true;
            $this->supervisorApprovalReason = "Amount (" . number_format($amount, 2) . ") exceeds teller limit of " . number_format($this->tellerLimit, 2);

            $this->limitViolations[] = [
                'type' => 'teller_limit',
                'reason' => $this->supervisorApprovalReason,
                'limit' => $this->tellerLimit,
                'amount' => $amount
            ];

            // Don't return here - check other limits too
        }

        // Check transaction type specific limits from database
        if ($this->sourceAccountId) {
            $account = Account::with('accountType')->find($this->sourceAccountId);
            if ($account && $account->accountType) {
                $limitCheck = $this->checkTransactionTypeLimit($account->accountType->id, $amount);

                if ($limitCheck['exceeds_limit']) {
                    $this->requiresSupervisorApproval = true;
                    $this->exceedsTellerLimit = true;
                    $this->supervisorApprovalReason = $limitCheck['reason'];
                    $this->limitViolations[] = $limitCheck;
                }
            }
        }

        // Check daily aggregate limits
        if ($this->sourceAccountId) {
            $dailyLimitCheck = $this->checkDailyAggregateLimit();
            if ($dailyLimitCheck['exceeds_limit']) {
                $this->requiresSupervisorApproval = true;
                $this->exceedsTellerLimit = true;
                $this->supervisorApprovalReason = $dailyLimitCheck['reason'];
                $this->limitViolations[] = $dailyLimitCheck;
            }
        }
    }

    /**
     * Check transaction type specific limits
     */
    private function checkTransactionTypeLimit($accountTypeId, $amount)
    {
        $result = [
            'exceeds_limit' => false,
            'reason' => null,
            'limit_type' => null,
            'max_allowed' => null,
        ];

        // Find limits for this account type and transaction type
        $limits = TransactionLimit::where('account_type_id', $accountTypeId)
            ->where('transaction_type', $this->transactionType)
            ->where('period', 'per_transaction')
            ->where('is_active', true)
            ->first();

        if ($limits && $limits->max_amount && $amount > $limits->max_amount) {
            $result['exceeds_limit'] = true;
            $result['reason'] = "Amount ({$amount}) exceeds per-transaction limit of {$limits->max_amount} for {$this->transactionType}";
            $result['limit_type'] = 'per_transaction';
            $result['max_allowed'] = $limits->max_amount;
            $result['limit_id'] = $limits->id;
        }

        return $result;
    }

    /**
     * Check daily aggregate limits for the teller
     */
    private function checkDailyAggregateLimit()
    {
        $result = [
            'exceeds_limit' => false,
            'reason' => null,
            'limit_type' => 'daily',
            'current_daily_total' => 0,
            'daily_limit' => 0,
        ];

        $user = Auth::user();

        // Calculate today's total transaction amount by this teller
        $todayTotal = Transaction::where('initiated_by', $user->id)
            ->whereDate('created_at', today())
            ->whereIn('status', ['completed', 'pending'])
            ->sum('amount');

        // Add current transaction amount
        $newTotal = $todayTotal + (float) $this->amount;

        // Get daily limit from transaction limits or user profile
        $dailyLimit = $this->getDailyLimitForTeller($user);

        if ($newTotal > $dailyLimit) {
            $result['exceeds_limit'] = true;
            $result['reason'] = "Daily transaction total ({$newTotal}) exceeds daily limit of {$dailyLimit}";
            $result['current_daily_total'] = $todayTotal;
            $result['daily_limit'] = $dailyLimit;
        }

        return $result;
    }

    /**
     * Get daily limit for teller
     */
    private function getDailyLimitForTeller($user)
    {
        // Default daily limit
        $defaultDailyLimit = 50000;

        // Check if user has custom daily limit
        if (isset($user->daily_teller_limit) && $user->daily_teller_limit > 0) {
            return (float) $user->daily_teller_limit;
        }

        // Set based on role
        switch ($user->role) {
            case 'supervisor':
            case 'manager':
                return 200000.00;
            case 'senior_teller':
                return 100000.00;
            case 'teller':
                return $defaultDailyLimit;
            default:
                return $defaultDailyLimit;
        }
    }
    /**
     * Get available supervisors for approval
     */
    public function getAvailableSupervisorsProperty()
    {
        $user = Auth::user();

        if (!$user || !$user->branch_id) {
            return collect();
        }

        return User::where('branch_id', $user->branch_id)
            ->whereIn('role', ['supervisor', 'manager'])
            ->where('status', 'active')
            ->where('id', '!=', $user->id) // Exclude current user
            ->get();
    }

    /**
     * Auto-assign supervisor when limit is exceeded
     */
    private function autoAssignSupervisor()
    {
        if (!$this->requiresSupervisorApproval) {
            return;
        }

        // Get available supervisors
        $supervisors = $this->getAvailableSupervisorsProperty();

        if ($supervisors->isNotEmpty()) {
            // Auto-select first available supervisor
            $this->supervisorId = $supervisors->first()->id;

            // Also set supervisorApproval to true for UI
            $this->supervisorApproval = true;

            // Log auto-assignment with correct amount
            Log::info('Supervisor auto-assigned for transaction exceeding limit', [
                'teller_id' => Auth::id(),
                'amount' => $this->amount,
                'supervisor_id' => $this->supervisorId,
                'reason' => $this->supervisorApprovalReason
            ]);
        }
    }
    /**
     * Validate supervisor approval
     */
    private function validateSupervisorApproval()
    {
        if (!$this->requiresSupervisorApproval) {
            return true;
        }

        // Check if supervisor is selected
        if (!$this->supervisorId) {
            $this->addError('supervisorId', 'Supervisor approval is required for this transaction. Please select a supervisor.');
            return false;
        }

        // Verify supervisor exists and is active
        $supervisor = User::find($this->supervisorId);
        if (!$supervisor || $supervisor->status !== 'active') {
            $this->addError('supervisorId', 'Selected supervisor is not available.');
            return false;
        }

        // Verify supervisor is from same branch
        if ($supervisor->branch_id !== Auth::user()->branch_id) {
            $this->addError('supervisorId', 'Supervisor must be from the same branch.');
            return false;
        }

        return true;
    }

    /**
     * Verify supervisor password
     */
    public function verifySupervisorPassword()
    {
        if (!$this->requiresSupervisorApproval || !$this->supervisorId) {
            return true;
        }

        if (empty($this->supervisorPassword)) {
            $this->addError('supervisorPassword', 'Supervisor password is required.');
            return false;
        }

        $supervisor = User::find($this->supervisorId);

        if (!$supervisor || !\Illuminate\Support\Facades\Hash::check($this->supervisorPassword, $supervisor->password)) {
            $this->addError('supervisorPassword', 'Invalid supervisor password.');
            return false;
        }

        // Record approval
        $this->supervisorApprovalStatus = 'approved';
        $this->supervisorApprovedAt = now();
        $this->supervisorApprovedBy = $supervisor->id;

        return true;
    }

    /**
     * Updated amount handler with limit checking
     */
    public function updatedAmount($value)
    {
        try {
            // Ensure value is numeric before processing
            $value = (float) $value;

            $this->amount = $value;
            $this->validateAmount();

            // Check teller limits
            $this->checkTellerLimit();

            // Auto-assign supervisor if needed
            if ($this->requiresSupervisorApproval) {
                $this->autoAssignSupervisor();
                session()->flash('info', 'This transaction requires supervisor approval as it exceeds teller limits.');
            }

            // Auto-calculate cash denominations for cash transactions
            if (
                in_array($this->transactionType, ['withdrawal', 'cash_deposit']) &&
                $this->cashHandlingMethod === 'cash' &&
                is_numeric($value) && $value > 0
            ) {
                $this->calculateCashDenominations($value);
            }
        } catch (\Exception $e) {
            Log::error('Error in updatedAmount', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'amount' => $value,
                'transaction_type' => $this->transactionType
            ]);

            // Re-throw to see the error in Laravel log
            throw $e;
        }
    }

    /**
     * Updated source account handler with limit loading
     */
    public function updatedSourceAccountId($value)
    {
        if ($value) {
            $account = Account::with(['accountType'])->find($value);
            if ($account) {
                $this->accountBalance = $account->current_balance;
                $this->availableBalance = $account->available_balance;
                $this->currency = $account->currency;

                // Check if initial deposit has been done
                $this->hasInitialDeposit = $this->checkInitialDeposit($value);

                // Load transaction limits from database
                $this->loadTransactionLimitsFromDb($account);

                // Re-check teller limits with new account info
                if ($this->amount) {
                    $this->checkTellerLimit();
                }

                // Dispatch event to focus on amount field
                $this->dispatch('account-selected');
            }
        }
    }

    /**
     * Load transaction limits from database
     */
    private function loadTransactionLimitsFromDb($account)
    {
        if (!$account || !$account->accountType) {
            $this->limits = [];
            return;
        }

        $this->limits = TransactionLimit::where('account_type_id', $account->account_type_id)
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(function ($limit) {
                return [
                    $limit->period => [
                        'max_amount' => $limit->max_amount,
                        'max_count' => $limit->max_count,
                        'id' => $limit->id,
                        'transaction_type' => $limit->transaction_type,
                    ]
                ];
            })
            ->toArray();

        // Also load for specific transaction type
        $typeLimits = TransactionLimit::where('account_type_id', $account->account_type_id)
            ->where('transaction_type', $this->transactionType)
            ->where('period', 'per_transaction')
            ->where('is_active', true)
            ->first();

        if ($typeLimits) {
            $this->limits['per_transaction_type'] = [
                'max_amount' => $typeLimits->max_amount,
                'max_count' => $typeLimits->max_count,
                'id' => $typeLimits->id,
            ];
        }
    }

    /**
     * Updated transaction type handler
     */
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

        // Reset supervisor approval flags when changing transaction type
        $this->requiresSupervisorApproval = false;
        $this->exceedsTellerLimit = false;
        $this->supervisorApprovalReason = null;
        $this->limitViolations = [];

        // Show beneficiary section for transfers and bill payments
        if (in_array($value, ['transfer', 'bill_payment'])) {
            $this->showBeneficiarySection = true;
        } else {
            $this->showBeneficiarySection = false;
        }

        // Reset transaction purpose for withdrawals
        if ($value === 'withdrawal') {
            $this->transactionPurpose = '';
        }

        // Set default description based on type
        $this->updateDescription();

        // Clear validation errors
        $this->resetErrorBag();

        // Re-check limits if amount is set
        if ($this->amount && $this->sourceAccountId) {
            $this->checkTellerLimit();
        }
    }


    // Updated account search method
    public function updatedAccountSearch($value)
    {
        if (empty($value) || strlen($value) < 3) {
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

    // Select account method - AUTO SELECTS THE ACCOUNT IF EXACT MATCH IS FOUND, OTHERWISE SHOWS SEARCH RESULTS
    public function selectAccount($accountId)
    {
        $account = Account::with(['customer', 'accountType'])->find($accountId);

        if ($account && $account->customer) {
            $customer = $account->customer;

            // Set customer ID
            $this->customerId = $customer->id;

            // Set source account ID
            $this->sourceAccountId = $account->id;
            $this->selectedAccount = $account;

            // Update account search display
            $this->accountSearch = $account->account_number . ' - ' . $customer->full_name;

            // Store selected customer data WITH ALL FIELDS
            $this->selectedCustomer = [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'customer_number' => $customer->customer_number,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'id_number' => $customer->id_number,
                'profile_photo_url' => $customer->profile_photo_url,
                'signature_url' => $customer->signature_image_url,
                'signature_path' => $customer->signature_image_path,
                'kyc_status' => $customer->kyc_status,
                'accounts' => $customer->accounts->map(function ($acc) {
                    return [
                        'id' => $acc->id,
                        'account_number' => $acc->account_number,
                        'current_balance' => $acc->current_balance,
                        'available_balance' => $acc->available_balance,
                        'currency' => $acc->currency,
                        'status' => $acc->status,
                        'account_type' => $acc->accountType ? [
                            'name' => $acc->accountType->name,
                        ] : null,
                    ];
                })->toArray(),
            ];

            // Check if initial deposit has been done for this account
            $this->hasInitialDeposit = $this->checkInitialDeposit($account->id);

            // Load customer accounts for selection
            $this->customerAccounts = $customer->accounts()
                ->where('status', 'active')
                ->with(['accountType'])
                ->get();

            // Load customer beneficiaries
            $this->beneficiaries = $customer->beneficiaries()
                ->where('is_active', true)
                ->get();

            // Set customer contact info for receipts
            $this->customerEmail = $customer->email;
            $this->customerPhone = $customer->phone;

            // Reset other fields
            $this->reset(['destinationAccountId', 'amount', 'transactionInitiator']);
            $this->updateAvailableBalance();

            // Close search results
            $this->closeSearchResults();

            // Dispatch event to focus on amount field
            $this->dispatch('account-selected');
        }
    }

    /**
     * Check if signature file exists
     */
    public function checkSignatureFile($customerId)
    {
        if (!$customerId) {
            return null;
        }

        $customer = Customer::find($customerId);
        if (!$customer || !$customer->signature_image_path) {
            return [
                'exists' => false,
                'message' => 'No signature path in database'
            ];
        }

        $path = $customer->signature_image_path;
        $publicPath = public_path('storage/' . $path);
        $storagePath = storage_path('app/public/' . $path);

        return [
            'path' => $path,
            'url' => $customer->signature_image_url,
            'public_path' => $publicPath,
            'public_exists' => file_exists($publicPath),
            'storage_path' => $storagePath,
            'storage_exists' => file_exists($storagePath),
        ];
    }

    // Check if initial deposit has been done for an account
    private function checkInitialDeposit($accountId)
    {
        return Transaction::where('destination_account_id', $accountId)
            ->where('type', 'initial_deposit')
            ->where('status', 'completed')
            ->exists();
    }

    public function closeSearchResults()
    {
        $this->showSearchResults = false;
        $this->searchResults = [];
    }

    public function clearAccountSelection()
    {
        $this->reset([
            'customerId',
            'accountSearch',
            'sourceAccountId',
            'customerAccounts',
            'destinationAccountId',
            'amount',
            'beneficiaries',
            'customerEmail',
            'customerPhone',
            'selectedCustomer',
            'selectedAccount',
            'transactionInitiator',
            'thirdPartyName',
            'thirdPartyIdType',
            'thirdPartyIdNumber',
            'thirdPartyPhone',
            'thirdPartyRelationship',
            'thirdPartyAuthorization',
            'authorizationDocument',
            'hasInitialDeposit',
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

    private function initializeCashDenominations()
    {
        $this->cashDenominations = [
            ['denomination' => 200, 'count' => 0],
            ['denomination' => 100, 'count' => 0],
            ['denomination' => 50, 'count' => 0],
            ['denomination' => 20, 'count' => 0],
            ['denomination' => 10, 'count' => 0],
            ['denomination' => 5, 'count' => 0],
            ['denomination' => 2, 'count' => 0],
            ['denomination' => 1, 'count' => 0],
            ['denomination' => 0.50, 'count' => 0],
            ['denomination' => 0.20, 'count' => 0],
            ['denomination' => 0.10, 'count' => 0],
            ['denomination' => 0.05, 'count' => 0],
            ['denomination' => 0.01, 'count' => 0],
        ];
    }

    // MANUAL DENOMINATION UPDATE - ALLOW TELLER TO ADJUST COUNTS
    public function updateDenomination($index, $count)
    {
        if (isset($this->cashDenominations[$index])) {
            $this->cashDenominations[$index]['count'] = max(0, (int) $count);

            // Validate total matches amount
            $total = $this->getTotalCashCount();
            if (abs($total - (float) $this->amount) > 0.01) {
                $this->addError('cashDenominations', 'Total cash amount must equal transaction amount');
            } else {
                $this->clearValidation('cashDenominations');
            }
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
        $denominations = [200, 100, 50, 20, 10, 5, 2, 1, 0.50, 0.20, 0.10, 0.05, 0.01];

        foreach ($this->cashDenominations as $key => $denomination) {
            $denomValue = $denomination['denomination'];
            if ($remaining >= $denomValue - 0.001) { // Small epsilon for floating point
                $count = floor($remaining / $denomValue);
                $this->cashDenominations[$key]['count'] = $count;
                $remaining = round($remaining - ($count * $denomValue), 2);
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
            'initial_deposit' => 'Initial Deposit',
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

    /**
     * Override the nextStep method to include supervisor validation
     */
    public function nextStep()
    {
        // Validate current step before moving forward
        if (!$this->validateCurrentStep()) {
            session()->flash('error', 'Please fill in all required fields correctly.');
            return;
        }

        // Special validation for step 1 to check supervisor approval requirement
        if ($this->step === 1) {
            // Re-check teller limits to ensure they're up to date
            $this->checkTellerLimit();

            // If transaction requires supervisor approval, ensure supervisor is selected
            if ($this->requiresSupervisorApproval) {
                if (!$this->validateSupervisorApproval()) {
                    return;
                }
            }
        }

        if ($this->step < $this->totalSteps) {
            $this->step++;

            // If moving to step 3 (verification), show supervisor section if required
            if ($this->step === 3 && $this->requiresSupervisorApproval) {
                $this->supervisorApproval = true;
            }

            // If moving to step 4 (final review)
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


    // AUTO SUPERVISOR APPROVAL WHEN AMOUNT HITS TRANSACTION LIMIT
    private function checkAutoSupervisorApproval()
    {
        if (!$this->amount || !$this->sourceAccountId) {
            return;
        }

        $amount = (float) $this->amount;

        // Check if amount exceeds teller limit
        $tellerLimit = 10000; // This should come from configuration or user settings

        if ($amount >= $tellerLimit) {
            $this->supervisorApproval = true;

            // Auto-select a supervisor (e.g., first available supervisor from same branch)
            if (!empty($this->supervisors)) {
                if (is_array($this->supervisors) && isset($this->supervisors[0])) {
                    // Handle as array
                    $this->supervisorId = is_array($this->supervisors[0])
                        ? ($this->supervisors[0]['id'] ?? null)
                        : ($this->supervisors[0]->id ?? null);
                } elseif (method_exists($this->supervisors, 'first') && $this->supervisors->first()) {
                    // Handle as collection
                    $this->supervisorId = $this->supervisors->first()->id;
                }
            }

            session()->flash('info', 'Supervisor approval required for amounts above ' . number_format($tellerLimit, 2));
            return redirect()->route('transactions.index');
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;

            // If going back from step 4, hide confirmation
            if ($this->step === 3) {
                $this->showConfirmation = false;
            }

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
                    $rules = [
                        'customerId' => 'required|exists:customers,id',
                        'transactionType' => 'required|in:transfer,withdrawal,deposit,cash_deposit,cheque_deposit,bill_payment,loan_payment,fee_collection,adjustment,initial_deposit',
                        'sourceAccountId' => 'required|exists:accounts,id',
                        'amount' => 'required|numeric|min:0.01',
                        'description' => 'required|string|max:255',
                    ];

                    // Only validate purpose if NOT withdrawal or initial deposit
                    if (!in_array($this->transactionType, ['withdrawal', 'initial_deposit'])) {
                        $rules['transactionPurpose'] = 'required|string';
                    }

                    $this->validate($rules);

                    // For debit transactions, check sufficient funds
                    if (in_array($this->transactionType, ['withdrawal', 'transfer', 'bill_payment', 'loan_payment', 'fee_collection'])) {
                        $account = Account::find($this->sourceAccountId);
                        if ($account && (float)$this->amount > $account->available_balance + $account->overdraft_limit) {
                            $this->addError('amount', 'Insufficient funds. Available balance: ' . number_format($account->available_balance, 2));
                            return false;
                        }
                    }

                    // Check if initial deposit is required for non-initial-deposit transactions
                    if ($this->transactionType !== 'initial_deposit' && !$this->hasInitialDeposit) {
                        $this->addError('transactionType', 'Initial deposit must be made before other transactions.');
                        return false;
                    }

                    // For initial deposit, check that it hasn't been done already
                    if ($this->transactionType === 'initial_deposit' && $this->hasInitialDeposit) {
                        $this->addError('transactionType', 'Initial deposit has already been made for this account.');
                        return false;
                    }

                    // Additional validation for specific transaction types
                    if (in_array($this->transactionType, ['withdrawal', 'cash_deposit'])) {
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

                case 3: // Verification and Receipt Options
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

                    // Validate supervisor ID if supervisor approval is checked
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

    /**
     * Prepare transaction preview with limit information
     */
    private function prepareTransactionPreview()
    {
        $customer = Customer::find($this->customerId);
        $sourceAccount = Account::find($this->sourceAccountId);
        $destinationAccount = $this->destinationAccountId ? Account::find($this->destinationAccountId) : null;
        $beneficiary = $this->beneficiaryId ? Beneficiary::find($this->beneficiaryId) : null;
        $teller = User::find($this->tellerId);
        $supervisor = $this->supervisorId ? User::find($this->supervisorId) : null;

        // Get profile photo and signature for customer
        $customerModel = Customer::find($this->customerId);
        $customerName = $customerModel->full_name ?? 'Customer';
        $profilePhoto = $customerModel->profile_photo_url ?? $this->getDefaultProfilePhoto($customerName);
        $signature = $customerModel->signature_image_url ?? null;

        // Calculate balance after transaction
        $balanceAfter = $this->accountBalance;
        if (in_array($this->transactionType, ['withdrawal', 'transfer', 'bill_payment', 'loan_payment', 'fee_collection'])) {
            $balanceAfter -= (float) $this->amount;
        } elseif (in_array($this->transactionType, ['deposit', 'cash_deposit', 'cheque_deposit', 'initial_deposit'])) {
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
                'profile_photo' => $profilePhoto,
                'signature' => $signature,
            ] : null,
            'source_account' => $sourceAccount ? [
                'number' => $sourceAccount->account_number,
                'name' => $sourceAccount->accountType->name ?? 'N/A',
                'balance_before' => number_format($this->accountBalance, 2),
                'balance_after' => number_format($balanceAfter, 2),
            ] : null,
            'destination_account' => $destinationAccount ? [
                'number' => $destinationAccount->account_number,
                'name' => $destinationAccount->accountType->name ?? 'N/A',
                'customer' => $destinationAccount->customer->full_name ?? 'N/A',
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
            'teller' => $teller ? $teller->name : (Auth::user()->name ?? 'Teller'),
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
            'requires_supervisor' => $this->requiresSupervisorApproval || $this->supervisorApproval,
            'supervisor_auto_assigned' => $this->requiresSupervisorApproval,
            'supervisor_approval_reason' => $this->supervisorApprovalReason,
            'teller_limit' => $this->tellerLimit,
            'limit_violations' => $this->limitViolations,
        ];
    }

    /* Get customer signature URL
 */
    public function getCustomerSignatureUrl($customerId)
    {
        $customer = Customer::find($customerId);
        return $customer ? $customer->signature_image_url : null;
    }

    /**
     * Prepare metadata with supervisor approval information
     */
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
            'has_initial_deposit' => $this->hasInitialDeposit,

            // Supervisor approval metadata
            'requires_supervisor_approval' => $this->requiresSupervisorApproval || $this->supervisorApproval,
            'supervisor_approval_status' => $this->supervisorApprovalStatus,
            'supervisor_approval_reason' => $this->supervisorApprovalReason,
            'supervisor_approved_at' => $this->supervisorApprovedAt,
            'supervisor_approved_by' => $this->supervisorApprovedBy,
            'teller_limit_at_time' => $this->tellerLimit,
            'limit_violations' => $this->limitViolations,
        ];

        // Add supervisor ID if set
        if ($this->supervisorId) {
            $metadata['supervisor_id'] = $this->supervisorId;
        }

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

        // Add receipt options
        $metadata['receipt_options'] = [
            'print' => $this->printReceipt,
            'email' => $this->emailReceipt,
            'sms' => $this->smsReceipt,
        ];

        return $metadata;
    }

    /**
     * Override confirmTransaction to include supervisor password verification
     */
    public function confirmTransaction()
    {
        // Verify supervisor password if required
        if ($this->requiresSupervisorApproval) {
            if (!$this->verifySupervisorPassword()) {
                return;
            }
        } elseif ($this->supervisorApproval && $this->supervisorPassword) {
            // Manual supervisor approval check
            $supervisor = User::find($this->supervisorId);
            if (!$supervisor || !\Illuminate\Support\Facades\Hash::check($this->supervisorPassword, $supervisor->password)) {
                $this->addError('supervisorPassword', 'Invalid supervisor password');
                return;
            }

            $this->supervisorApprovalStatus = 'approved';
            $this->supervisorApprovedAt = now();
            $this->supervisorApprovedBy = $supervisor->id;
        }

        // Process the transaction
        $this->processTransaction();
    }


    /**
     * Get summary of limits for display
     */
    public function getLimitSummaryProperty()
    {
        $summary = [
            'teller_limit' => number_format($this->tellerLimit, 2),
            'daily_limit' => number_format($this->getDailyLimitForTeller(Auth::user()), 2),
            'transaction_limits' => [],
        ];

        if ($this->sourceAccountId && $this->selectedAccount) {
            $account = $this->selectedAccount;
            if ($account->accountType) {
                $limits = TransactionLimit::where('account_type_id', $account->account_type_id)
                    ->where('is_active', true)
                    ->get();

                foreach ($limits as $limit) {
                    $summary['transaction_limits'][] = [
                        'type' => $limit->transaction_type,
                        'period' => $limit->period,
                        'max_amount' => $limit->max_amount ? number_format($limit->max_amount, 2) : 'Unlimited',
                        'max_count' => $limit->max_count ?? 'Unlimited',
                    ];
                }
            }
        }

        return $summary;
    }


    /**
     * Process the transaction with proper service integration
     */
    public function processTransaction()
    {
        $this->isProcessing = true;

        try {
            // Use the new TellerTransactionService
            $transactionService = app(\App\Services\Transaction\TellerTransactionService::class);

            $teller = Auth::user();

            // Prepare supervisor data if approval is required
            $supervisorData = null;
            if ($this->requiresSupervisorApproval || $this->supervisorApproval) {
                $supervisor = User::find($this->supervisorId);
                if ($supervisor) {
                    $supervisorData = [
                        'supervisor_id' => $supervisor->id,
                        'reason' => $this->supervisorApprovalReason ?? 'Supervisor approval required',
                        'metadata' => [
                            'teller_limit' => $this->tellerLimit,
                            'amount' => $this->amount,
                            'limit_violations' => $this->limitViolations,
                            'supervisor_notes' => $this->supervisorApprovalNotes,
                        ]
                    ];
                }
            }

            // Prepare base transaction data
            $baseData = [
                'amount' => (float) $this->amount,
                'description' => $this->description,
                'metadata' => $this->prepareMetadata(),
            ];

            $transaction = null;

            // Process based on transaction type
            switch ($this->transactionType) {
                case 'withdrawal':
                    $transactionData = array_merge($baseData, [
                        'account_id' => $this->sourceAccountId,
                    ]);
                    $transaction = $transactionService->processWithdrawal(
                        $transactionData,
                        $teller,
                        $supervisorData
                    );
                    break;

                case 'cash_deposit':
                    $transactionData = array_merge($baseData, [
                        'account_id' => $this->sourceAccountId,
                        'transaction_type' => 'cash_deposit',
                    ]);
                    $transaction = $transactionService->processCashDeposit(
                        $transactionData,
                        $teller,
                        $supervisorData
                    );
                    break;

                case 'cheque_deposit':
                    $transactionData = array_merge($baseData, [
                        'account_id' => $this->sourceAccountId,
                        'transaction_type' => 'cheque_deposit',
                        'cheque_number' => $this->chequeNumber,
                        'drawer_bank' => $this->drawerBank,
                    ]);
                    $transaction = $transactionService->processChequeDeposit(
                        $transactionData,
                        $teller,
                        $supervisorData
                    );
                    break;

                case 'deposit':
                    // Generic deposit - treat as cash deposit
                    $transactionData = array_merge($baseData, [
                        'account_id' => $this->sourceAccountId,
                        'transaction_type' => 'cash_deposit',
                    ]);
                    $transaction = $transactionService->processCashDeposit(
                        $transactionData,
                        $teller,
                        $supervisorData
                    );
                    break;

                case 'transfer':
                    if (!$this->destinationAccountId) {
                        throw new \Exception('Destination account is required for transfer');
                    }
                    $transactionData = array_merge($baseData, [
                        'from_account_id' => $this->sourceAccountId,
                        'to_account_id' => $this->destinationAccountId,
                    ]);
                    $transaction = $transactionService->processTransfer(
                        $transactionData,
                        $teller,
                        $supervisorData
                    );
                    break;

                case 'initial_deposit':
                    $transactionData = array_merge($baseData, [
                        'account_id' => $this->sourceAccountId,
                    ]);
                    $transaction = $transactionService->processInitialDeposit(
                        $transactionData,
                        $teller,
                        $supervisorData
                    );
                    break;

                default:
                    throw new \Exception("Transaction type '{$this->transactionType}' is not yet implemented");
            }

            if (!$transaction) {
                throw new \Exception('Failed to create transaction');
            }

            // Handle receipts
            $this->handleReceipts($transaction);

            session()->flash('success', 'Transaction completed successfully.');
            return redirect()->route('transactions.show', $transaction->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Transaction failed: ' . $e->getMessage());
            Log::error('Transaction failed: ' . $e->getMessage(), [
                'transaction_type' => $this->transactionType,
                'source_account_id' => $this->sourceAccountId,
                'destination_account_id' => $this->destinationAccountId,
                'amount' => $this->amount,
                'user_id' => Auth::id(),
                'requires_supervisor' => $this->requiresSupervisorApproval,
                'supervisor_id' => $this->supervisorId,
                'error_trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('transactions.index');
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Handle receipt generation and sending
     */
    private function handleReceipts($transaction)
    {
        if ($this->printReceipt) {
            $this->printTransactionReceipt($transaction);
        }

        if ($this->emailReceipt && $this->customerEmail) {
            $this->sendEmailReceipt($transaction, $this->customerEmail);
        }

        if ($this->smsReceipt && $this->customerPhone) {
            $this->sendSmsReceipt($transaction, $this->customerPhone);
        }
    }

    private function printTransactionReceipt($transaction)
    {
        Log::info('Printing receipt for transaction: ' . $transaction->transaction_reference);
    }

    private function sendEmailReceipt($transaction, $email)
    {
        Log::info('Emailing receipt to: ' . $email . ' for transaction: ' . $transaction->transaction_reference);
    }

    private function sendSmsReceipt($transaction, $phone)
    {
        Log::info('SMS receipt to: ' . $phone . ' for transaction: ' . $transaction->transaction_reference);
    }

    public function cancelTransaction()
    {
        $this->showConfirmation = false;
        $this->transactionPreview = null;
        $this->step = 3;
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
            'accountSearch',
            'selectedCustomer',
            'selectedAccount',
            'hasInitialDeposit',
        ]);

        // Reload initial data
        $this->loadInitialData();

        // Set default values
        $this->tellerId = Auth::id();
        $this->currency = 'GHS';
        $this->exchangeRate = 1.0;
        $this->foreignAmount = 0;
        $this->transactionInitiator = 'self';
        $this->step = 1;
        $this->totalSteps = 4;
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

    public function getDefaultProfilePhoto(?string $name): string
    {
        if (empty($name)) {
            $name = 'User';
        }

        $initials = collect(explode(' ', $name))
            ->map(fn($word) => mb_substr($word, 0, 1))
            ->filter()
            ->join('');

        // If initials are empty, use a default
        if (empty($initials)) {
            $initials = 'U';
        }

        return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=7F9CF5&color=FFFFFF&size=256";
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.transactions.transaction-create');
    }
}
