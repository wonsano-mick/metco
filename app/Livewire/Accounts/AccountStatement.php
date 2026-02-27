<?php

namespace App\Livewire\Accounts;

use App\Models\Eloquent\Account;
use App\Models\Eloquent\Customer;
use App\Models\Eloquent\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AccountStatement extends Component
{
    use WithPagination;

    public Account $account;
    public Customer $customer;

    // Filter properties
    #[Url(history: true)]
    public $dateRange = 'this_month';

    #[Url(history: true)]
    public $startDate;

    #[Url(history: true)]
    public $endDate;

    #[Url(history: true)]
    public $transactionType = '';

    #[Url(history: true)]
    public $minAmount = '';

    #[Url(history: true)]
    public $maxAmount = '';

    #[Url(history: true)]
    public $search = '';

    // Summary properties
    public $openingBalance = 0;
    public $closingBalance = 0;
    public $totalCredits = 0;
    public $totalDebits = 0;
    public $transactionCount = 0;
    public $runningBalances = [];

    // Export options
    public $exportFormat = 'pdf';
    public $showExportModal = false;

    protected $queryString = [
        'dateRange' => ['except' => 'this_month'],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'transactionType' => ['except' => ''],
        'minAmount' => ['except' => ''],
        'maxAmount' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    protected function rules()
    {
        return [
            'startDate' => 'nullable|date|before_or_equal:endDate',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'minAmount' => 'nullable|numeric|min:0',
            'maxAmount' => 'nullable|numeric|min:0|gte:minAmount',
            'transactionType' => 'nullable|string|in:deposit,withdrawal,transfer,fee,interest,all',
        ];
    }

    public function mount($accountId)
    {
        try {
            // Load the account with relationships
            $this->account = Account::with(['customer', 'accountType'])
                ->findOrFail($accountId);

            // Load customer
            $this->customer = $this->account->customer;

            // Check authorization - only account owner, tellers, and managers can view
            $user = Auth::user();
            $isAccountOwner = $this->customer->user_id === $user->id;
            $isTellerOrManager = in_array($user->role, ['teller', 'branch-manager', 'admin', 'super-admin', 'manager', 'auditor', 'loan-officer', 'supervisor']);

            if (!$isAccountOwner && !$isTellerOrManager) {
                abort(403, 'You are not authorized to view this account statement.');
            }

            // Set default date range (this month)
            $this->setDateRange('this_month');

            // Load initial statement data
            $this->loadStatementData();
        } catch (\Exception $e) {
            Log::error('Error loading account statement: ' . $e->getMessage(), [
                'account_id' => $accountId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Unable to load account statement. Please try again.');
        }
    }

    public function updatedDateRange($value)
    {
        $this->setDateRange($value);
        $this->resetPage();
        $this->loadStatementData();
    }

    public function updatedTransactionType()
    {
        $this->resetPage();
        $this->loadStatementData();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadStatementData();
    }

    public function updatedMinAmount()
    {
        $this->resetPage();
        $this->loadStatementData();
    }

    public function updatedMaxAmount()
    {
        $this->resetPage();
        $this->loadStatementData();
    }

    public function applyFilters()
    {
        $this->validate();
        $this->resetPage();
        $this->loadStatementData();
    }

    public function resetFilters()
    {
        $this->reset(['dateRange', 'startDate', 'endDate', 'transactionType', 'minAmount', 'maxAmount', 'search']);
        $this->setDateRange('this_month');
        $this->loadStatementData();
    }

    private function setDateRange($range)
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $this->startDate = $now->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;

            case 'this_week':
                $this->startDate = $now->startOfWeek()->format('Y-m-d');
                $this->endDate = $now->endOfWeek()->format('Y-m-d');
                break;

            case 'this_month':
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;

            case 'last_month':
                $lastMonth = $now->subMonth();
                $this->startDate = $lastMonth->startOfMonth()->format('Y-m-d');
                $this->endDate = $lastMonth->endOfMonth()->format('Y-m-d');
                break;

            case 'this_quarter':
                $this->startDate = $now->startOfQuarter()->format('Y-m-d');
                $this->endDate = $now->endOfQuarter()->format('Y-m-d');
                break;

            case 'this_year':
                $this->startDate = $now->startOfYear()->format('Y-m-d');
                $this->endDate = $now->endOfYear()->format('Y-m-d');
                break;

            case 'custom':
                // Keep existing custom dates or set defaults
                if (!$this->startDate) {
                    $this->startDate = $now->startOfMonth()->format('Y-m-d');
                }
                if (!$this->endDate) {
                    $this->endDate = $now->format('Y-m-d');
                }
                break;

            default:
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    private function loadStatementData()
    {
        try {
            // Get transactions query
            $query = $this->getTransactionsQuery();

            // Calculate totals
            $this->calculateTotals(clone $query);

            // Get paginated transactions
            $transactions = $query->paginate(20);

            // Calculate running balance
            $this->calculateRunningBalance($transactions);

            return $transactions;
        } catch (\Exception $e) {
            Log::error('Error loading statement data: ' . $e->getMessage());
            session()->flash('error', 'Error loading transaction data.');
            return collect();
        }
    }

    private function getTransactionsQuery()
    {
        $query = Transaction::where(function ($q) {
            // Transactions where this account is either source or destination
            $q->where('source_account_id', $this->account->id)
                ->orWhere('destination_account_id', $this->account->id);
        })
            ->whereIn('status', ['completed', 'posted'])
            ->orderBy('initiated_at', 'desc');

        // Apply date range filter
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('initiated_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        // Apply transaction type filter
        if ($this->transactionType && $this->transactionType !== 'all') {
            switch ($this->transactionType) {
                case 'deposit':
                    $query->whereIn('type', ['deposit', 'cash_deposit', 'initial_deposit']);
                    break;
                case 'withdrawal':
                    $query->whereIn('type', ['withdrawal', 'cash_withdrawal']);
                    break;
                case 'transfer':
                    $query->whereIn('type', ['transfer', 'internal_transfer', 'external_transfer']);
                    break;
                case 'fee':
                    $query->where('type', 'like', '%fee%');
                    break;
                case 'interest':
                    $query->where('type', 'interest');
                    break;
            }
        }

        // Apply amount filters
        if ($this->minAmount) {
            $query->where('amount', '>=', $this->minAmount);
        }

        if ($this->maxAmount) {
            $query->where('amount', '<=', $this->maxAmount);
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('transaction_reference', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    private function calculateTotals($query)
    {
        // Clone query for different calculations
        $allTransactions = clone $query;

        // Calculate opening balance (balance before the statement period)
        $this->openingBalance = $this->calculateOpeningBalance();

        // Calculate credits (money coming into the account)
        $this->totalCredits = (clone $allTransactions)
            ->where('destination_account_id', $this->account->id)
            ->sum('amount');

        // Calculate debits (money leaving the account)
        $this->totalDebits = (clone $allTransactions)
            ->where('source_account_id', $this->account->id)
            ->sum('amount');

        // Calculate closing balance
        $this->closingBalance = $this->openingBalance + $this->totalCredits - $this->totalDebits;

        // Get transaction count
        $this->transactionCount = $allTransactions->count();
    }

    private function calculateOpeningBalance()
    {
        // Get the balance just before the statement period starts
        $lastTransactionBeforePeriod = Transaction::where(function ($q) {
            $q->where('source_account_id', $this->account->id)
                ->orWhere('destination_account_id', $this->account->id);
        })
            ->whereIn('status', ['completed', 'posted'])
            ->where('initiated_at', '<', Carbon::parse($this->startDate)->startOfDay())
            ->orderBy('initiated_at', 'desc')
            ->first();

        if ($lastTransactionBeforePeriod) {
            // You might need to calculate the actual balance from ledger entries
            // For now, we'll use the account's current balance and subtract period transactions
            $periodCredits = Transaction::where('destination_account_id', $this->account->id)
                ->whereIn('status', ['completed', 'posted'])
                ->whereBetween('initiated_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ])
                ->sum('amount');

            $periodDebits = Transaction::where('source_account_id', $this->account->id)
                ->whereIn('status', ['completed', 'posted'])
                ->whereBetween('initiated_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ])
                ->sum('amount');

            return $this->account->current_balance - $periodCredits + $periodDebits;
        }

        // If no transactions before period, opening balance is 0 or initial deposit
        return 0;
    }

    private function calculateRunningBalance($transactions)
    {
        $balance = $this->closingBalance;
        $runningBalances = [];

        // Calculate running balance for each transaction (from oldest to newest)
        foreach ($transactions->reverse() as $transaction) {
            if ($transaction->destination_account_id == $this->account->id) {
                // Credit transaction - subtract from balance going backwards
                $balance -= $transaction->amount;
            } elseif ($transaction->source_account_id == $this->account->id) {
                // Debit transaction - add to balance going backwards
                $balance += $transaction->amount;
            }
            $runningBalances[$transaction->id] = $balance;
        }

        // Store in a property that can be accessed in the view
        $this->runningBalances = $runningBalances;
    }

    public function getTransactionsProperty()
    {
        return $this->loadStatementData();
    }

    public function exportStatement()
    {
        if (!Gate::allows('export account statements')) {
            abort(403, 'Unauthorized access to export account statements.');
        }

        try {
            
            $transactions = $this->getTransactionsQuery()->get();

            if ($transactions->isEmpty()) {
                session()->flash('warning', 'No transactions to export.');
                return;
            }

            // Recalculate running balance for export
            $this->calculateTotals(clone $this->getTransactionsQuery());

            $data = [
                'account' => $this->account,
                'customer' => $this->customer,
                'transactions' => $transactions,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'openingBalance' => $this->openingBalance,
                'closingBalance' => $this->closingBalance,
                'totalCredits' => $this->totalCredits,
                'totalDebits' => $this->totalDebits,
                'generatedAt' => now(),
                'generatedBy' => Auth::user(),
            ];

            if ($this->exportFormat === 'pdf') {
                return $this->exportAsPdf($data);
            } elseif ($this->exportFormat === 'csv') {
                return $this->exportAsCsv($data);
            } elseif ($this->exportFormat === 'excel') {
                return $this->exportAsExcel($data);
            }
        } catch (\Exception $e) {
            Log::error('Error exporting statement: ' . $e->getMessage());
            session()->flash('error', 'Failed to export statement. Please try again.');
        }
    }

    private function exportAsPdf($data)
    {
        $pdf = Pdf::loadView('exports.account-statement-pdf', $data);

        $filename = 'statement_' . $this->account->account_number .
            '_' . now()->format('Y-m-d_His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    private function exportAsCsv($data)
    {
        $filename = 'statement_' . $this->account->account_number .
            '_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, [
                'Date',
                'Reference',
                'Description',
                'Type',
                'Debit',
                'Credit',
                'Balance'
            ]);

            $balance = $data['openingBalance'];

            // Add transactions in chronological order
            foreach ($data['transactions']->sortBy('initiated_at') as $transaction) {
                $isCredit = $transaction->destination_account_id == $data['account']->id;
                $isDebit = $transaction->source_account_id == $data['account']->id;

                $debit = $isDebit ? $transaction->amount : 0;
                $credit = $isCredit ? $transaction->amount : 0;

                if ($isCredit) {
                    $balance += $transaction->amount;
                } elseif ($isDebit) {
                    $balance -= $transaction->amount;
                }

                fputcsv($file, [
                    $transaction->initiated_at->format('Y-m-d H:i:s'),
                    $transaction->transaction_reference,
                    $transaction->description,
                    ucfirst(str_replace('_', ' ', $transaction->type)),
                    $debit ? number_format($debit, 2) : '',
                    $credit ? number_format($credit, 2) : '',
                    number_format($balance, 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportAsExcel($data)
    {
        // For Excel, you might want to use a package like Laravel Excel
        // For now, we'll use CSV as fallback
        return $this->exportAsCsv($data);
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.accounts.account-statement', [
            'transactions' => $this->getTransactionsProperty()
        ]);
    }
}
