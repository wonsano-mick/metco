<?php

namespace App\Livewire\Accounts;

use App\Models\Eloquent\AccountType;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AccountTypeManager extends Component
{
    use WithPagination;

    public $showForm = false;
    public $editingId = null;
    public $is_for_organizations = false;
    public $code;
    public $name;
    public $description;
    public $min_balance = 0;
    public $max_balance;
    public $interest_rate = 0;
    public $monthly_fee = 0;
    public $is_active = true;
    public $features = [];

    protected $rules = [
        'code' => 'required|string|max:20|unique:account_types,code',
        'name' => 'required|string|max:100',
        'description' => 'nullable|string',
        'min_balance' => 'required|numeric|min:0',
        'max_balance' => 'nullable|numeric|gt:min_balance',
        'interest_rate' => 'required|numeric|min:0|max:100',
        'monthly_fee' => 'required|numeric|min:0',
        'is_active' => 'boolean',
        'is_for_organizations' => 'boolean'
    ];

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingId = null;
    }

    public function edit($id) 
    {
        if (!Gate::allows('manage account types')) {
            abort(403, 'Unauthorized access.');
        }
        
        $type = AccountType::findOrFail($id);
        $this->editingId = $id;
        $this->is_for_organizations = $type->is_for_organizations;
        $this->code = $type->code;
        $this->name = $type->name;
        $this->description = $type->description;
        $this->min_balance = $type->min_balance;
        $this->max_balance = $type->max_balance;
        $this->interest_rate = $type->interest_rate;
        $this->monthly_fee = $type->monthly_fee;
        $this->is_active = $type->is_active;
        $this->features = $type->features ?? [];
        $this->showForm = true;
    }

    public function save()
    {
        if (!Gate::allows('manage account types')) {
            abort(403, 'Unauthorized access.');
        }

        if ($this->editingId) {
            $this->rules['code'] = 'required|string|max:20|unique:account_types,code,' . $this->editingId;
        }

        $this->validate();

        $data = [
            'is_for_organizations' => $this->is_for_organizations,
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'description' => $this->description,
            'min_balance' => $this->min_balance,
            'max_balance' => $this->max_balance,
            'interest_rate' => $this->interest_rate,
            'monthly_fee' => $this->monthly_fee,
            'is_active' => $this->is_active,
            'features' => $this->features
        ];

        if ($this->editingId) {
            $type = AccountType::find($this->editingId);
            $type->update($data);
            session()->flash('success', 'Account type updated successfully.');
            return redirect()->route('accounts.account-types');
        } else {
            AccountType::create($data);
            session()->flash('success', 'Account type created successfully.');
            return redirect()->route('accounts.account-types');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'is_for_organizations',
            'code',
            'name',
            'description',
            'min_balance',
            'max_balance',
            'interest_rate',
            'monthly_fee',
            'is_active',
            'features'
        ]);
        $this->resetValidation();
    }

    public function toggleActive($id)
    {
        if (!Gate::allows('manage account types')) {
            abort(403, 'Unauthorized access.');
        }
        
        $type = AccountType::find($id);
        $type->update(['is_active' => !$type->is_active]);
        session()->flash('success', 'Account type updated successfully.');
        return redirect()->route('accounts.account-types');
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.accounts.account-type-manager', [
            'accountTypes' => AccountType::withCount('accounts')
                ->orderBy('name')
                ->paginate(10)
        ]);
    }
}
