<?php

namespace App\Livewire\Teller;

use App\Models\Eloquent\SystemAccount;
use App\Models\Eloquent\SystemLedgerEntry;
use App\Models\Eloquent\Transaction;
use App\Services\Transaction\EnhancedTransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TellerDashboard extends Component
{
    public $tellerAccount;
    public $todayTransactions = [];
    public $cashBalance = 0;
    public $topUpAmount;
    public $topUpReference;
    public $withdrawAmount;
    public $withdrawReference;
    public $showTopUpModal = false;
    public $showWithdrawModal = false;

    // Add property for opening balance
    public $openingBalance = 0;

    public function mount()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                abort(403, 'No authenticated user');
            }

            // Check if user has teller role
            if ($user->role !== 'teller') {
                abort(403, 'Unauthorized access. Only tellers can access this dashboard.');
            }

            // Initialize as empty array
            $this->todayTransactions = [];

            $this->loadTellerData();
        } catch (\Exception $e) {
            Log::error('Error in TellerDashboard mount: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Set default values
            $this->cashBalance = 0;
            $this->openingBalance = 0;
            $this->todayTransactions = [];
        }
    }

    public function loadTellerData()
    {
        try {
            $user = Auth::user();
            // Reset today's transactions
            $this->todayTransactions = [];

            // Find the teller's specific cash account
            $this->findTellerAccount();

            if ($this->tellerAccount) {
                // Set current cash balance from account
                $this->cashBalance = (float) ($this->tellerAccount->balance ?? 0);

                // Calculate opening balance (balance before today's first transaction)
                $this->calculateOpeningBalance();

                // Get today's ledger entries for this specific teller account
                $systemEntries = SystemLedgerEntry::with(['transaction'])
                    ->where('system_account_id', $this->tellerAccount->id)
                    ->whereDate('created_at', today())
                    ->orderBy('created_at', 'asc')
                    ->get();
 
                // Convert to array format
                foreach ($systemEntries as $entry) {
                    $this->todayTransactions[] = [
                        'id' => 'sys_' . $entry->id,
                        'created_at' => $entry->created_at->format('Y-m-d H:i:s'),
                        'entry_type' => $entry->entry_type,
                        'description' => $entry->description,
                        'amount' => (float) $entry->amount,
                        'transaction_reference' => $entry->transaction->transaction_reference ?? null,
                        'source' => 'system',
                        'type' => $entry->transaction->type ?? 'unknown'
                    ];
                }
            } else {
                // Try to create a teller account for this user
                $this->createTellerAccount();

                if ($this->tellerAccount) {
                    $this->cashBalance = (float) ($this->tellerAccount->balance ?? 0);
                    $this->openingBalance = $this->cashBalance;
                } else {
                    $this->cashBalance = 0;
                    $this->openingBalance = 0;
                }
            }

            // Get regular transactions where this teller was involved
            $this->loadTellerTransactions();

            // Sort by created_at ascending for proper display
            usort($this->todayTransactions, function ($a, $b) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });

        } catch (\Exception $e) {
            Log::error('Error in loadTellerData: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Set default values to prevent further errors
            $this->cashBalance = 0;
            $this->openingBalance = 0;
            $this->todayTransactions = [];
        }
    }

    /**
     * Calculate opening balance (balance before today's first transaction)
     */
    private function calculateOpeningBalance()
    {
        if (!$this->tellerAccount) {
            $this->openingBalance = 0;
            return;
        }

        // Get the last transaction before today
        $lastTransaction = SystemLedgerEntry::where('system_account_id', $this->tellerAccount->id)
            ->whereDate('created_at', '<', today())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastTransaction) {
            // Opening balance is the balance after the last transaction before today
            $this->openingBalance = (float) ($lastTransaction->balance_after ?? 0);
        } else {
            // If no previous transactions, opening balance is the current balance
            // But we need to subtract today's net change
            $todayNetChange = $this->calculateTodayNetChange();
            $this->openingBalance = $this->cashBalance - $todayNetChange;
        }
    }

    /**
     * Calculate net change from today's transactions
     */
    private function calculateTodayNetChange(): float
    {
        if (!$this->tellerAccount) {
            return 0;
        }

        $todayEntries = SystemLedgerEntry::where('system_account_id', $this->tellerAccount->id)
            ->whereDate('created_at', today())
            ->get();

        $netChange = 0;
        foreach ($todayEntries as $entry) {
            if ($entry->entry_type === 'debit') {
                $netChange += (float) $entry->amount;
            } else {
                $netChange -= (float) $entry->amount;
            }
        }

        return $netChange;
    }

    /**
     * Find the teller's cash account - strictly for this teller only
     */
    private function findTellerAccount()
    {
        try {
            $user = Auth::user();

            // Strategy 1: Look for account with teller code containing user ID (TELLER-00003 format)
            $this->tellerAccount = SystemAccount::where('type', 'teller')
                ->where('code', 'TELLER-' . str_pad($user->id, 5, '0', STR_PAD_LEFT))
                ->first();

            if ($this->tellerAccount) {
                return;
            }

            // Strategy 2: Look for teller account with metadata containing user ID
            $this->tellerAccount = SystemAccount::where('type', 'teller')
                ->where('metadata->user_id', $user->id)
                ->first();

            if ($this->tellerAccount) {
                return;
            }

            // Strategy 3: Look for any teller account with name containing user's name
            $this->tellerAccount = SystemAccount::where('type', 'teller')
                ->where('name', 'LIKE', '%' . $user->first_name . '%' . $user->last_name . '%')
                ->first();

            if ($this->tellerAccount) {
                return;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in findTellerAccount: ' . $e->getMessage());
            $this->tellerAccount = null;
        }
    }

    /**
     * Create teller account for current user if it doesn't exist
     */
    private function createTellerAccount()
    {
        try {
            $user = Auth::user();

            // Check if account already exists (double-check)
            $existingAccount = SystemAccount::where('type', 'teller')
                ->where(function ($query) use ($user) {
                    $query->where('code', 'TELLER-' . str_pad($user->id, 5, '0', STR_PAD_LEFT))
                        ->orWhere('metadata->user_id', $user->id);
                })
                ->first();

            if ($existingAccount) {
                $this->tellerAccount = $existingAccount;
                return;
            }

            // Generate a unique code
            $code = 'TELLER-' . str_pad($user->id, 5, '0', STR_PAD_LEFT);

            // Create new teller account for this specific teller
            $this->tellerAccount = SystemAccount::create([
                'type' => 'teller',
                'code' => $code,
                'name' => 'Teller Cash Account - ' . $user->full_name . ' (ID: ' . $user->id . ')',
                'balance' => 0,
                'currency' => 'GHS',
                'is_active' => 1,
                'metadata' => json_encode([
                    'user_id' => $user->id,
                    'branch_id' => $user->branch_id,
                    'created_at' => now()->toIso8601String(),
                    'created_by' => $user->id
                ]),
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating teller account: ' . $e->getMessage());
            $this->tellerAccount = null;
        }
    }

    /**
     * Load transactions where this specific teller was involved
     */
    private function loadTellerTransactions()
    {
        try {
            $user = Auth::user();

            // Get today's transactions where this specific teller was the initiator or completer
            $tellerTransactions = Transaction::where(function ($query) use ($user) {
                $query->where('initiated_by', $user->id)
                    ->orWhere('completed_by', $user->id);
            })
                ->whereDate('initiated_at', today())
                ->orderBy('initiated_at', 'asc')
                ->get();

            if ($tellerTransactions->isNotEmpty()) {
                // Create a map of existing transaction references to avoid duplicates
                $existingReferences = [];
                foreach ($this->todayTransactions as $entry) {
                    if (isset($entry['transaction_reference'])) {
                        $existingReferences[$entry['transaction_reference']] = true;
                    }
                }

                foreach ($tellerTransactions as $transaction) {
                    // Skip if this transaction is already in the list
                    if (isset($existingReferences[$transaction->transaction_reference])) {
                        Log::info('Skipping duplicate transaction: ' . $transaction->id);
                        continue;
                    }

                    // Determine entry type based on transaction type
                    $entryType = $this->determineEntryType($transaction);

                    // Add as array
                    $this->todayTransactions[] = [
                        'id' => 'trans_' . $transaction->id,
                        'created_at' => $transaction->initiated_at->format('Y-m-d H:i:s'),
                        'entry_type' => $entryType,
                        'description' => $transaction->description ?: ucfirst(str_replace('_', ' ', $transaction->type)),
                        'amount' => (float) $transaction->amount,
                        'transaction_reference' => $transaction->transaction_reference,
                        'source' => 'transaction',
                        'type' => $transaction->type
                    ];

                }
            }
        } catch (\Exception $e) {
            Log::error('Error in loadTellerTransactions: ' . $e->getMessage());
        }
    }

    /**
     * Determine entry type based on transaction type
     */
    private function determineEntryType($transaction): string
    {
        // Transaction types that increase teller cash (money coming in)
        $debitTypes = ['teller_topup', 'initial_deposit', 'deposit', 'cash_deposit'];

        // Transaction types that decrease teller cash (money going out)
        $creditTypes = ['withdrawal', 'teller_withdrawal'];

        if (in_array($transaction->type, $debitTypes)) {
            return 'debit';
        } elseif (in_array($transaction->type, $creditTypes)) {
            return 'credit';
        }

        // Default based on description or metadata
        if ($transaction->description && str_contains(strtolower($transaction->description), 'withdrawal')) {
            return 'credit';
        }

        return 'debit'; // Default to debit for unknown types
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.teller.teller-dashboard');
    }
}
