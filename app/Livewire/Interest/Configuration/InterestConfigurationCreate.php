<?php

namespace App\Livewire\Interest\Configuration;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\InterestConfiguration;
use App\Models\Eloquent\AccountType;
use App\Models\Eloquent\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InterestConfigurationCreate extends Component
{
    public $account_type_id;
    public $apply_to_all_accounts = true;
    public $selectedAccounts = [];
    public $accountSearch = '';
    public $selectAll = false;
    
    public $name;
    public $code;
    public $frequency = 'monthly';
    public $interest_rate;
    public $calculation_method = 'daily_balance';
    public $posting_method = 'simple';
    public $compound_frequency_days;
    public $tiers = [];
    public $minimum_balance_required;
    public $maximum_balance_limit;
    public $interest_day = 'day_of_month';
    public $interest_day_value = 1;
    public $is_active = true;
    public $description;

    protected $rules = [
        'account_type_id' => 'required|exists:account_types,id',
        'apply_to_all_accounts' => 'boolean',
        'selectedAccounts' => 'required_if:apply_to_all_accounts,false|array',
        'selectedAccounts.*' => 'exists:accounts,id',
        'name' => 'required|string|max:100',
        'code' => 'required|string|max:50|unique:interest_configurations,code',
        'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
        'interest_rate' => 'required|numeric|min:0|max:100',
        'calculation_method' => 'required|in:daily_balance,minimum_balance,average_daily_balance,tiered',
        'posting_method' => 'required|in:simple,compound',
        'compound_frequency_days' => 'required_if:posting_method,compound|nullable|integer|min:1',
        'tiers' => 'nullable|array',
        'minimum_balance_required' => 'nullable|numeric|min:0',
        'maximum_balance_limit' => 'nullable|numeric|min:0',
        'interest_day' => 'required|in:day_of_month,day_of_week,last_day',
        'interest_day_value' => 'required|integer|min:1|max:31',
        'is_active' => 'boolean',
        'description' => 'nullable|string',
    ];

    protected $messages = [
        'selectedAccounts.required_if' => 'Please select at least one account.',
    ];

    public function mount()
    {
        $this->code = 'INT-' . Str::upper(Str::random(8));
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
        $this->tiers[] = ['min' => null, 'max' => null, 'rate' => null];
    }

    public function removeTier($index)
    {
        unset($this->tiers[$index]);
        $this->tiers = array_values($this->tiers);
    }

    public function save()
    {
        $this->validate();

        $configuration = InterestConfiguration::create([
            'account_type_id' => $this->account_type_id,
            'name' => $this->name,
            'code' => $this->code,
            'frequency' => $this->frequency,
            'interest_rate' => $this->interest_rate,
            'calculation_method' => $this->calculation_method,
            'posting_method' => $this->posting_method,
            'compound_frequency_days' => $this->compound_frequency_days,
            'tiers' => $this->tiers,
            'minimum_balance_required' => $this->minimum_balance_required,
            'maximum_balance_limit' => $this->maximum_balance_limit,
            'interest_day' => $this->interest_day,
            'interest_day_value' => $this->interest_day_value,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'created_by' => Auth::id(),
            'metadata' => [
                'apply_to_all_accounts' => $this->apply_to_all_accounts,
                'selected_accounts' => $this->apply_to_all_accounts ? null : $this->selectedAccounts,
            ]
        ]);

        session()->flash('message', 'Interest configuration created successfully.');
        return redirect()->route('interest.configurations');
    }

    #[Layout('layouts.main')]
    public function render()
    {
        $accountTypes = AccountType::all();

        return view('livewire.interest.configuration.create', [
            'accountTypes' => $accountTypes,
        ]);
    }
}