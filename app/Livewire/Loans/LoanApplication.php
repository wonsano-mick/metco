<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Customer;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\Loan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Collection;

class LoanApplication extends Component
{
    use WithFileUploads;

    public $step = 1;
    public $totalSteps = 4;
    public $isProcessing = false;

    // Step 1: Customer & Loan Details
    public $customerId = '';
    public $customerSearch = '';
    public $selectedCustomer = null;
    public $searchResults = [];
    public $isSearching = false;
    public $showSearchResults = false;

    public $loanType = 'personal';
    public $purpose = '';
    public $amount = '';
    public $termMonths = 12;
    public $interestRate = '';
    public $interestType = 'flat';
    public $repaymentFrequency = 'monthly';

    public $account_id = '';
    public $disbursementMethod = 'bank_transfer';
    public $disbursementAccountNumber = '';
    public $disbursementBankName = '';

    // Step 2: Collateral & Guarantors
    public $collateralValue = '';
    public $collateralDescription = '';
    public $collateralDetails = [];
    public $selectedCollateralTypes = [];
    public $guarantors = [];
    public $newGuarantor = ['name' => '', 'relationship' => '', 'phone' => ''];
    public $processingFee = 0;
    public $insuranceFee = 0;

    // Step 3: Documentation & Review
    public $applicationDate;
    public $startDate;
    public $attachments = [];
    public $additionalAttachments = [];
    public $additionalNotes = '';

    // Step 4: Confirmation
    public $showConfirmation = false;
    public $loanPreview = null;

    // Calculated values
    public $monthlyPayment = 0;
    public $totalInterest = 0;
    public $totalAmount = 0;

    /** @var Collection|null */
    public $customerAccounts = null;

    // protected $listeners = ['customerSelected'];

    public function mount()
    {
        if (!Gate::allows('create loans')) {
            abort(403, 'You are not authorized to create loans.');
        }

        $this->applicationDate = now()->format('Y-m-d');
        $this->startDate = now()->addDays(7)->format('Y-m-d');
        $this->loadInterestRates();
        $this->customerAccounts = collect();
    }

    private function loadInterestRates()
    {
        $rates = [
            'personal' => 12.0,
            'mortgage' => 8.0,
            'funeral' => 6.0,
            'business' => 10.0,
            'auto' => 9.0,
            'education' => 5.0,
            'agriculture' => 7.0,
            'emergency' => 15.0,
        ];

        $this->interestRate = $rates[$this->loanType] ?? 10.0;
    }

    public function updatedLoanType()
    {
        $this->loadInterestRates();
        $this->calculatePayments();
    }

    public function updatedAmount()
    {
        $this->calculatePayments();
    }

    public function updatedTermMonths($value)
    {
        $this->termMonths = (int) $value;
        $this->calculatePayments();
    }

    public function updatedInterestRate()
    {
        $this->calculatePayments();
    }

    public function calculatePayments()
    {
        if (!$this->amount || !$this->termMonths || !$this->interestRate) {
            return;
        }

        $principal = (float) $this->amount;
        $monthlyRate = (float) $this->interestRate / 12 / 100;
        $months = (int) $this->termMonths;

        if ($this->interestType === 'reducing') {
            $numerator = $monthlyRate * pow(1 + $monthlyRate, $months);
            $denominator = pow(1 + $monthlyRate, $months) - 1;
            $this->monthlyPayment = $principal * ($numerator / $denominator);
            $this->totalAmount = $this->monthlyPayment * $months;
        } else {
            // Flat interest calculation
            $totalInterest = $principal * ($this->interestRate / 100) * ($months / 12);
            $this->totalAmount = $principal + $totalInterest;
            $this->monthlyPayment = $this->totalAmount / $months;
        }

        $this->totalInterest = $this->totalAmount - $principal;
    }

    public function addGuarantor()
    {
        $this->validate([
            'newGuarantor.name' => 'required|string|min:3',
            'newGuarantor.relationship' => 'required|string',
            'newGuarantor.phone' => 'required|string',
        ]);

        $this->guarantors[] = $this->newGuarantor;
        $this->newGuarantor = ['name' => '', 'relationship' => '', 'phone' => ''];
    }

    public function removeGuarantor($index)
    {
        unset($this->guarantors[$index]);
        $this->guarantors = array_values($this->guarantors);
    }

    public function searchCustomer()
    {
        if (strlen($this->customerSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->isSearching = true;
        $this->searchResults = Customer::where(function ($query) {
            $query->where('first_name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('last_name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('customer_number', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('email', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->customerSearch . '%');
        })
            ->active()
            ->verified()
            ->limit(10)
            ->get();

        $this->isSearching = false;
    }

    public function updatedCustomerSearch()
    {
        if (strlen($this->customerSearch) < 2) {
            $this->searchResults = [];
            $this->showSearchResults = false;
            return;
        }

        $this->isSearching = true;
        $this->showSearchResults = true;

        try {
            $this->searchResults = Customer::with(['accounts.accountType'])
                ->where(function ($query) {
                    $query->where('first_name', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('last_name', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('customer_number', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('email', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('id_number', 'like', '%' . $this->customerSearch . '%');
                })
                ->active()
                ->verified()
                ->limit(10)
                ->get()
                ->toArray(); // Convert to array for consistency

        } catch (\Exception $e) {
            $this->searchResults = [];
            $this->dispatch(
                'showToast',
                message: 'Error searching customers: ' . $e->getMessage(),
                type: 'error'
            );
        }

        $this->isSearching = false;
    }

    public function closeSearchResults()
    {
        $this->showSearchResults = false;
        $this->searchResults = [];
    }

    public function selectCustomer($customerId)
    {
        try {
            $customer = Customer::with(['accounts.accountType'])->find($customerId);

            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            $this->customerId = $customer->id;
            $this->selectedCustomer = [
                'id' => $customer->id,
                'customer_number' => $customer->customer_number,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'profile_photo_url' => $customer->profile_photo_url,
                'monthly_income' => $customer->monthly_income,
                'age' => $customer->age ?? null, // Add null check for age
                'has_accounts' => $customer->accounts->isNotEmpty(),
                'accounts' => $customer->accounts->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'account_number' => $account->account_number,
                        'account_type' => $account->accountType->name ?? 'N/A',
                        'current_balance' => $account->current_balance,
                        'currency' => $account->currency,
                    ];
                })->toArray(),
            ];

            // Store accounts as Collection
            $this->customerAccounts = $customer->accounts;

            // Only auto-select account if customer has accounts
            if ($this->customerAccounts->isNotEmpty()) {
                $firstAccount = $this->customerAccounts->first();
                $this->account_id = $firstAccount->id;
                $this->disbursementMethod = 'bank_transfer';
            } else {
                $this->account_id = '';
                $this->disbursementMethod = 'cash'; // Default to cash if no account
            }

            $this->customerSearch = $customer->full_name;
            $this->searchResults = [];
            $this->showSearchResults = false;
        } catch (\Exception $e) {
            $this->dispatch(
                'showToast',
                message: 'Error selecting customer: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }

    public function updatedDisbursementMethod($value)
    {
        if ($value !== 'bank_transfer') {
            $this->account_id = ''; // Clear account selection if not bank transfer
        } elseif ($this->customerAccounts && $this->customerAccounts->isNotEmpty()) {
            // Auto-select first account if switching back to bank transfer
            $this->account_id = $this->customerAccounts->first()->id;
        }
    }

    public function getHasAccountsProperty()
    {
        return $this->customerAccounts && $this->customerAccounts->isNotEmpty();
    }

    public function clearSearch()
    {
        $this->customerSearch = '';
        $this->searchResults = [];
        $this->showSearchResults = false;
    }

    public function nextStep()
    {
        if ($this->step < $this->totalSteps) {
            if ($this->validateStep()) {
                $this->step++;
                $this->dispatch('step-changed');
            }
        } else {
            $this->showConfirmation = true;
            $this->prepareLoanPreview();
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
            $this->dispatch('step-changed');
        }
    }

    private function validateStep()
    {
        switch ($this->step) {
            case 1:
                $this->validate([
                    'customerId' => 'required|exists:customers,id',
                    'loanType' => 'required|in:personal,mortgage,funeral,business,auto,education,agriculture,emergency',
                    'purpose' => 'required|string|min:10|max:500',
                    'amount' => 'required|numeric|min:100|max:1000000',
                    'termMonths' => 'required|integer|min:1|max:360',
                    'interestRate' => 'required|numeric|min:1|max:50',
                ]);
                break;
            case 2:
                $this->validate([
                    'collateralValue' => 'nullable|numeric|min:0',
                    'processingFee' => 'nullable|numeric|min:0',
                    'insuranceFee' => 'nullable|numeric|min:0',
                ]);
                break;
        }
        return true;
    }

    private function prepareLoanPreview()
    {
        $this->loanPreview = [
            'customer' => $this->selectedCustomer,
            'loan_details' => [
                'type' => $this->loanType,
                'purpose' => $this->purpose,
                'amount' => number_format($this->amount, 2),
                'term' => $this->termMonths . ' months',
                'interest_rate' => $this->interestRate . '%',
                'interest_type' => ucfirst($this->interestType),
                'repayment_frequency' => ucfirst($this->repaymentFrequency),
            ],
            'financials' => [
                'monthly_payment' => number_format($this->monthlyPayment, 2),
                'total_interest' => number_format($this->totalInterest, 2),
                'total_amount' => number_format($this->totalAmount, 2),
                'processing_fee' => number_format($this->processingFee, 2),
                'insurance_fee' => number_format($this->insuranceFee, 2),
            ],
            'collateral' => [
                'value' => $this->collateralValue ? number_format($this->collateralValue, 2) : 'None',
                'details' => $this->collateralDetails,
            ],
            'guarantors' => $this->guarantors,
            'dates' => [
                'application_date' => $this->applicationDate,
                'start_date' => $this->startDate,
            ],
        ];
    }

    public function submitApplication()
    {
        // Make account_id conditional based on disbursement method
        $validationRules = [
            'customerId' => 'required|exists:customers,id',
            'loanType' => 'required',
            'purpose' => 'required|string|min:10|max:500',
            'amount' => 'required|numeric|min:100',
            'termMonths' => 'required|integer|min:1|max:360',
            'interestRate' => 'required|numeric|min:1|max:50',
            'interestType' => 'required|in:fixed,reducing,flat',
            'repaymentFrequency' => 'required|in:monthly,biweekly,weekly,quarterly',
            'applicationDate' => 'required|date',
            'startDate' => 'required|date|after_or_equal:applicationDate',
            'collateralValue' => 'nullable|numeric|min:0',
            'processingFee' => 'nullable|numeric|min:0',
            'insuranceFee' => 'nullable|numeric|min:0',
            'disbursementMethod' => 'required|in:bank_transfer,cash,cheque,mobile_money',
        ];

        // Only require account_id if disbursement method is bank_transfer AND customer has accounts
        if ($this->disbursementMethod === 'bank_transfer' && $this->hasAccounts) {
            $validationRules['account_id'] = 'required|exists:accounts,id';
        }

        $this->validate($validationRules);

        try {
            $this->isProcessing = true;

            $startDate = \Carbon\Carbon::parse($this->startDate);
            $endDate = $startDate->copy()->addMonths((int)$this->termMonths);

            // Prepare loan data
            $loanData = [
                'customer_id' => $this->customerId,
                'loan_officer_id' => Auth::id(),
                'loan_type' => $this->loanType,
                'purpose' => $this->purpose,
                'amount' => (float) $this->amount,
                'interest_rate' => (float) $this->interestRate,
                'interest_type' => $this->interestType,
                'term_months' => (int) $this->termMonths,
                'repayment_frequency' => $this->repaymentFrequency,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'application_date' => now(),
                'status' => 'pending',
                'application_status' => 'new',
                'approval_status' => 'pending',
                'collateral_value' => $this->collateralValue ? (float) $this->collateralValue : null,
                'collateral_details' => !empty($this->collateralDetails) ? $this->collateralDetails : null,
                'guarantors' => !empty($this->guarantors) ? $this->guarantors : null,
                'processing_fee' => (float) $this->processingFee,
                'insurance_fee' => (float) $this->insuranceFee,
                'total_interest' => (float) $this->totalInterest,
                'total_amount' => (float) $this->totalAmount,
                'remaining_balance' => (float) $this->totalAmount,
                'disbursement_method' => $this->disbursementMethod,
            ];

            // Only include account_id if disbursement is bank transfer and account is selected
            if ($this->disbursementMethod === 'bank_transfer' && $this->account_id) {
                $loanData['account_id'] = $this->account_id;
                $loanData['disbursement_account_id'] = $this->account_id;
            }

            $loan = Loan::create($loanData);

            $this->isProcessing = false;

            $this->dispatch(
                'showToast',
                message: 'Loan application submitted successfully!',
                type: 'success'
            );

            return redirect()->route('loans.show', $loan->id);
        } catch (\Exception $e) {
            $this->isProcessing = false;

            $this->dispatch(
                'showToast',
                message: 'Error submitting application: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }

    public function clearCustomerSelection()
    {
        $this->customerId = '';
        $this->selectedCustomer = null;
        $this->customerAccounts = collect(); // Reset to empty collection
        $this->account_id = '';
        $this->customerSearch = '';
        $this->searchResults = [];
        $this->showSearchResults = false;
    }

    public function updatedAttachments($value, $key)
    {
        // Validate file uploads
        $this->validate([
            'attachments.' . $key => 'nullable|file|max:10240', // 10MB max
        ]);
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.loans.loan-application',[
            'hasAccounts' => $this->hasAccounts,
        ]);
    }
}
