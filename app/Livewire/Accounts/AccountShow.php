<?php

namespace App\Livewire\Accounts;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use Illuminate\Support\Facades\Gate;

class AccountShow extends Component
{
    public Account $account;

    public function mount(Account $account)
    {
        // Authorization check
        if (!Gate::allows('view accounts')) {
            abort(403, 'Unauthorized access.');
        }
        $this->account = $account;
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.accounts.account-show');
    }
}
