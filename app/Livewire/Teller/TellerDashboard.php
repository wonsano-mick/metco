<?php

namespace App\Livewire\Teller;

use App\Models\Eloquent\SystemAccount;
use App\Models\Eloquent\SystemLedgerEntry;
use App\Services\Transaction\EnhancedTransactionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TellerDashboard extends Component
{
    public $tellerAccount;
    public $todayTransactions = [];
    public $cashBalance;
    public $topUpAmount;
    public $topUpReference;
    public $withdrawAmount;
    public $withdrawReference;
    public $showTopUpModal = false;
    public $showWithdrawModal = false;

    public function mount()
    {
        $user = Auth::user();

        // Check if user has teller role or is manager/supervisor
        if (!in_array($user->role, ['teller', 'manager', 'supervisor', 'admin'])) {
            abort(403, 'Unauthorized access to teller dashboard.');
        }
        
        $this->loadTellerData();
    }

    public function loadTellerData()
    {
        $this->tellerAccount = SystemAccount::where('type', SystemAccount::TYPE_TELLER)
            ->where('code', 'TELLER-CASH-001')
            ->first();

        if ($this->tellerAccount) {
            $this->cashBalance = $this->tellerAccount->balance;

            $this->todayTransactions = SystemLedgerEntry::with(['transaction'])
                ->where('system_account_id', $this->tellerAccount->id)
                ->whereDate('created_at', today())
                ->orderBy('created_at', 'desc')
                ->get();
        }
    }

    public function openTopUpModal()
    {
        $this->reset(['topUpAmount', 'topUpReference']);
        $this->showTopUpModal = true;
    }

    public function openWithdrawModal()
    {
        $this->reset(['withdrawAmount', 'withdrawReference']);
        $this->showWithdrawModal = true;
    }

    public function topUpTeller(EnhancedTransactionService $transactionService)
    {
        $this->validate([
            'topUpAmount' => 'required|numeric|min:1',
            'topUpReference' => 'required|string|max:50',
        ]);

        try {
            $transactionService->topUpTeller($this->topUpAmount, $this->topUpReference);

            $this->loadTellerData();
            $this->showTopUpModal = false;

            $this->dispatch('showToast', [
                'message' => 'Teller cash topped up successfully',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'message' => 'Error: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function withdrawTellerCash(EnhancedTransactionService $transactionService)
    {
        $this->validate([
            'withdrawAmount' => 'required|numeric|min:1',
            'withdrawReference' => 'required|string|max:50',
        ]);

        try {
            $transactionService->withdrawTellerCash($this->withdrawAmount, $this->withdrawReference);

            $this->loadTellerData();
            $this->showWithdrawModal = false;

            $this->dispatch('showToast', [
                'message' => 'Cash withdrawn from teller drawer',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'message' => 'Error: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.teller.teller-dashboard');
    }
}
