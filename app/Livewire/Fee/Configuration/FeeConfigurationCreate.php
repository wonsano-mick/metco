<?php

namespace App\Livewire\Fee\Configuration;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\FeeConfiguration;
use App\Models\Eloquent\AccountType;
use App\Models\Eloquent\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FeeConfigurationCreate extends Component
{
    public $account_type_id;
    public $apply_to_all_accounts = true;
    public $selectedAccounts = [];
    public $accountSearch = '';
    public $selectAll = false;
    
    public $name;
    public $code; 
    public $frequency = 'monthly';
    public $fee_amount;
    public $currency = 'GHS';
    public $calculation_method = 'fixed';
    public $percentage_rate;
    public $tiers = [];
    public $has_minimum_balance_waiver = false;
    public $minimum_balance_threshold;
    public $charge_day = 'day_of_month';
    public $charge_day_value = 1;
    public $is_active = true;
    public $description;

    protected $rules = [
        'account_type_id' => 'required|exists:account_types,id',
        'apply_to_all_accounts' => 'boolean',
        'selectedAccounts' => 'required_if:apply_to_all_accounts,false|array',
        'selectedAccounts.*' => 'exists:accounts,id',
        'name' => 'required|string|max:100',
        'code' => 'required|string|max:50|unique:fee_configurations,code',
        'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
        'fee_amount' => 'required_if:calculation_method,fixed,tiered|nullable|numeric|min:0',
        'currency' => 'required|string|size:3',
        'calculation_method' => 'required|in:fixed,percentage,tiered',
        'percentage_rate' => 'required_if:calculation_method,percentage|nullable|numeric|min:0|max:100',
        'tiers' => 'nullable|array',
        'has_minimum_balance_waiver' => 'boolean',
        'minimum_balance_threshold' => 'required_if:has_minimum_balance_waiver,true|nullable|numeric|min:0',
        'charge_day' => 'required|in:day_of_month,day_of_week,last_day',
        'charge_day_value' => 'required|integer|min:1|max:31',
        'is_active' => 'boolean',
        'description' => 'nullable|string',
    ];

    protected $messages = [
        'selectedAccounts.required_if' => 'Please select at least one account.',
    ];

    public function mount()
    {
        $this->code = 'FEE-' . Str::upper(Str::random(8));
    }

    public function updatedAccountTypeId()
    {
        $this->selectedAccounts = [];
        $this->accountSearch = '';
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Fix: Get the accounts directly using the same query as availableAccounts property
            $accounts = Account::where('account_type_id', $this->account_type_id)
                ->when($this->accountSearch, function ($query) {
                    $query->where(function ($q) {
                        $q->where('account_number', 'like', '%' . $this->accountSearch . '%')
                          ->orWhereHas('customer', function ($customerQuery) {
                              $customerQuery->where('full_name', 'like', '%' . $this->accountSearch . '%')
                                  ->orWhere('first_name', 'like', '%' . $this->accountSearch . '%')
                                  ->orWhere('last_name', 'like', '%' . $this->accountSearch . '%');
                          });
                    });
                })
                ->active()
                ->limit(50)
                ->pluck('id')
                ->toArray();
            
            $this->selectedAccounts = $accounts;
        } else {
            $this->selectedAccounts = [];
        }
    }

    public function getAvailableAccountsProperty()
    {
        if (!$this->account_type_id) {
            return collect();
        }

        return Account::where('account_type_id', $this->account_type_id)
            ->with('customer')
            ->when($this->accountSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('account_number', 'like', '%' . $this->accountSearch . '%')
                      ->orWhereHas('customer', function ($customerQuery) {
                          $customerQuery->where('full_name', 'like', '%' . $this->accountSearch . '%')
                              ->orWhere('first_name', 'like', '%' . $this->accountSearch . '%')
                              ->orWhere('last_name', 'like', '%' . $this->accountSearch . '%');
                      });
                });
            })
            ->active()
            ->limit(50)
            ->get();
    }

    public function getSelectedAccountTypeNameProperty()
    {
        if (!$this->account_type_id) {
            return '';
        }
        return AccountType::find($this->account_type_id)?->name ?? '';
    }

    public function addTier()
    {
        $this->tiers[] = ['min' => null, 'max' => null, 'fee' => null];
    }

    public function removeTier($index)
    {
        unset($this->tiers[$index]);
        $this->tiers = array_values($this->tiers);
    }

    public function save()
    {
        $this->validate();

        $configuration = FeeConfiguration::create([
            'account_type_id' => $this->account_type_id,
            'name' => $this->name,
            'code' => $this->code,
            'frequency' => $this->frequency,
            'fee_amount' => $this->fee_amount ?? 0,
            'currency' => $this->currency,
            'calculation_method' => $this->calculation_method,
            'percentage_rate' => $this->percentage_rate,
            'tiers' => $this->tiers,
            'has_minimum_balance_waiver' => $this->has_minimum_balance_waiver,
            'minimum_balance_threshold' => $this->minimum_balance_threshold,
            'charge_day' => $this->charge_day,
            'charge_day_value' => $this->charge_day_value,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'created_by' => Auth::id(),
            'metadata' => [
                'apply_to_all_accounts' => $this->apply_to_all_accounts,
                'selected_accounts' => $this->apply_to_all_accounts ? null : $this->selectedAccounts,
            ]
        ]);

        session()->flash('message', 'Fee configuration created successfully.');
        return redirect()->route('fee.configurations');
    }

    #[Layout('layouts.main')]
    public function render()
    {
        $accountTypes = AccountType::all();

        return view('livewire.fee.configuration.create', [
            'accountTypes' => $accountTypes,
        ]);
    }
}