<?php

namespace App\Livewire\Interest\Configuration;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Eloquent\InterestConfiguration;
use App\Models\Eloquent\AccountType;

class InterestConfigurationIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $accountTypeFilter = '';
    public $statusFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'accountTypeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

     #[Layout('layouts.main')]
    public function render()
    {
        $configurations = InterestConfiguration::query()
            ->with(['accountType', 'createdBy'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->accountTypeFilter, function ($query) {
                $query->where('account_type_id', $this->accountTypeFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $accountTypes = AccountType::all();

        return view('livewire.interest.configuration.index', [
            'configurations' => $configurations,
            'accountTypes' => $accountTypes,
        ]);
    }
}
