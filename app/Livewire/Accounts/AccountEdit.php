<?php

namespace App\Livewire\Accounts;

use Livewire\Component;
use App\Enums\AccountStatus;
use App\Models\Eloquent\Branch;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\Customer;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Eloquent\AccountType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AccountEdit extends Component
{
    public Account $account;

    // Step 1: Customer Selection (readonly)
    public $customer_id = '';

    #[Validate('required|exists:account_types,id')]
    public $account_type_id = '';

    #[Validate('required|in:GHS, USD, GBP, EUR')]
    public $currency = 'GHS';

    #[Validate('required|numeric|min:0')]
    public $current_balance = 0;

    #[Validate('required|numeric|min:0')]
    public $available_balance = 0;

    #[Validate('required|numeric|min:0')]
    public $ledger_balance = 0;

    #[Validate('required|numeric|min:0')]
    public $overdraft_limit = 0;

    #[Validate('required|numeric|min:0')]
    public $minimum_balance = 0;

    #[Validate('required|in:active,pending,dormant,frozen,closed,suspended')]
    public $status = 'active';

    #[Validate('nullable|string|max:500')]
    public $notes = '';

    // For display only
    public $selectedCustomer = null;
    public $selectedAccountType = null;
    public $branch_id = '';

    // Data for dropdowns
    public $accountTypes = [];
    public $branches = [];
    public $currencies = ['GHS', 'USD', 'GBP', 'EUR'];

    // Status options
    public $statusOptions = [];

    public function mount(Account $account)
    {
        // Authorization check
        if (!Gate::allows('update accounts')) {
            abort(403, 'Unauthorized access.');
        }

        $this->account = $account;
        $this->loadAccountData();
        $this->loadDropdownData();
    }

    protected function loadAccountData()
    {
        // Load account data into component properties
        $this->customer_id = $this->account->customer_id;
        $this->account_type_id = $this->account->account_type_id;
        $this->currency = $this->account->currency;
        $this->current_balance = (float) $this->account->current_balance;
        $this->available_balance = (float) $this->account->available_balance;
        $this->ledger_balance = (float) $this->account->ledger_balance;
        $this->overdraft_limit = (float) $this->account->overdraft_limit;
        $this->minimum_balance = (float) $this->account->minimum_balance;
        $this->status = $this->account->status;

        // Load metadata
        $metadata = $this->account->metadata ?? [];
        $this->notes = $metadata['notes'] ?? '';
        $this->branch_id = $metadata['branch_id'] ?? (Auth::user()->branch_id ?? '');

        // Load customer details
        $this->loadCustomerDetails();

        // Load account type details
        $this->loadAccountTypeDetails();
    }

    // Show toast notification
    private function showToast($message, $type = 'success')
    {
        $this->dispatch('showToast', message: $message, type: $type);
    }

    // Set session flash for cross-page toast
    private function setSessionToast($message, $type = 'success')
    {
        session()->flash('toast', [
            'message' => $message,
            'type' => $type
        ]);
    }

    protected function loadDropdownData()
    {
        $this->loadAccountTypes();
        $this->loadBranches();
        $this->loadStatusOptions();
    }

    protected function loadAccountTypes()
    {
        $this->accountTypes = AccountType::query()
            ->when(
                method_exists(AccountType::class, 'tableHasColumn') &&
                    AccountType::tableHasColumn('account_types', 'is_active'),
                fn($query) => $query->where('is_active', true),
                fn($query) => $query
            )
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'code' => $type->code,
                    'min_balance' => (float) $type->min_balance,
                    'max_balance' => $type->max_balance ? (float) $type->max_balance : null,
                    'interest_rate' => (float) $type->interest_rate,
                    'description' => $type->description,
                ];
            })
            ->toArray();
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

    protected function loadStatusOptions()
    {
        $this->statusOptions = collect(AccountStatus::cases())
            ->map(fn($status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->toArray();
    }

    public function loadCustomerDetails()
    {
        if ($this->customer_id) {
            try {
                $customer = Customer::with(['branch', 'accounts' => function ($query) {
                    $query->with('accountType')->latest()->take(3);
                }])->find($this->customer_id);

                if ($customer) {
                    $this->selectedCustomer = [
                        'id' => $customer->id,
                        'customer_number' => $customer->customer_number,
                        'full_name' => $customer->full_name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'date_of_birth' => $customer->date_of_birth?->format('Y-m-d'),
                        'age' =>  $customer->date_of_birth ? $customer->date_of_birth->age : null,
                        'address' => ($customer->address_line_1 ?? '') .
                            ($customer->address_line_2 ? ', ' . $customer->address_line_2 : ''),
                        'city_state' => ($customer->city ?? '') .
                            ($customer->state ? ', ' . $customer->state : ''),
                        'branch_name' => $customer->branch->name ?? 'N/A',
                        'profile_photo_url' => $customer->profile_photo_url,
                        'kyc_status' => $customer->kyc_status,
                        'customer_tier' => $customer->customer_tier,
                        'existing_accounts' => $customer->accounts->count(),
                        'total_balance' => $customer->accounts->sum('current_balance'),
                        'accounts' => $customer->accounts->map(function ($account) {
                            return [
                                'account_number' => $account->account_number,
                                'type' => $account->accountType->name ?? 'N/A',
                                'balance' => $account->current_balance,
                                'status' => $account->status,
                            ];
                        })->toArray(),
                    ];
                }
            } catch (\Exception $e) {
                // Silently fail for customer details loading in edit mode
            }
        }
    }

    public function loadAccountTypeDetails()
    {
        if ($this->account_type_id) {
            $type = AccountType::find($this->account_type_id);
            $this->selectedAccountType = $type ? [
                'name' => $type->name,
                'code' => $type->code,
                'min_balance' => (float) $type->min_balance,
                'max_balance' => $type->max_balance ? (float) $type->max_balance : null,
                'interest_rate' => (float) $type->interest_rate,
                'description' => $type->description,
                'features' => $type->features ?? [],
            ] : null;
        }
    }

    public function updatedAccountTypeId()
    {
        $this->loadAccountTypeDetails();
    }

    public function validateMinimumBalance()
    {
        if ($this->selectedAccountType && $this->current_balance < $this->selectedAccountType['min_balance']) {
            $this->addError(
                'current_balance',
                "Minimum balance for {$this->selectedAccountType['name']} is " .
                    number_format($this->selectedAccountType['min_balance'], 2)
            );
            return false;
        }
        return true;
    }

    public function validateBalanceConsistency()
    {
        // Ensure available balance is not greater than current balance
        if ($this->available_balance > $this->current_balance) {
            $this->addError(
                'available_balance',
                "Available balance cannot be greater than current balance."
            );
            return false;
        }

        // Ensure ledger balance matches current balance (or adjust as needed)
        if ($this->ledger_balance != $this->current_balance) {
            // You might want to allow this for certain scenarios, or add a note
            // For now, we'll just warn but allow it
            // $this->addError('ledger_balance', 'Ledger balance should match current balance for normal accounts.');
        }

        return true;
    }

    public function save()
    {
        $this->validate();

        // Additional validation
        if (!$this->validateMinimumBalance()) {
            return;
        }

        if (!$this->validateBalanceConsistency()) {
            return;
        }

        try {
            DB::transaction(function () {
                // Prepare metadata
                $metadata = $this->account->metadata ?? [];
                $metadata['notes'] = $this->notes;
                $metadata['branch_id'] = $this->branch_id;
                $metadata['last_updated_by'] = Auth::user()->id;
                $metadata['updated_at'] = now()->toISOString();

                // Update the account
                $this->account->update([
                    'account_type_id' => $this->account_type_id,
                    'currency' => $this->currency,
                    'current_balance' => $this->current_balance,
                    'available_balance' => $this->available_balance,
                    'ledger_balance' => $this->ledger_balance,
                    'overdraft_limit' => $this->overdraft_limit,
                    'minimum_balance' => $this->minimum_balance,
                    'status' => $this->status,
                    'metadata' => $metadata,
                    'last_activity_at' => now(),
                ]);

                // Log activity
                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($this->account)
                    ->withProperties([
                        'customer_id' => $this->account->customer_id,
                        'customer_name' => $this->selectedCustomer['full_name'] ?? 'Unknown',
                        'account_number' => $this->account->account_number,
                        'account_type' => $this->selectedAccountType['name'] ?? 'Unknown',
                        'changes' => $this->account->getChanges(),
                    ])
                    ->log('Account updated');
            });

            // Show success message
            session()->flash('toast', [
                'type' => 'success',
                'message' => "Account {$this->account->account_number} updated successfully.",
            ]);

            // Redirect to account show page
            return redirect()->route('accounts.show', $this->account->id);
            
        } catch (\Exception $e) {
            Log::error('Account update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'account_id' => $this->account->id,
                'user_id' => Auth::id(),
            ]);
            $this->addError('general', 'Failed to update account: ' . $e->getMessage());
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
        return view('livewire.accounts.account-edit');
    }
}
