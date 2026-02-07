<?php

namespace App\Livewire\Transactions;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use App\Services\AuditLogService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\Eloquent\LedgerEntry;
use App\Models\Eloquent\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransactionReceiptMail;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewTransaction extends Component
{
    public $transaction;
    public $sourceAccount;
    public $destinationAccount;
    public $customer;
    public $beneficiary;
    // public $ledgerEntries;
    public $auditLogs = [];
    public $relatedTransactions = [];

    // UI State
    public $activeTab = 'overview';
    public $showReceiptModal = false;
    public $showReverseModal = false;
    public $reverseReason = '';
    public $isProcessing = false;
    public $loading = true;

    // For reverse confirmation
    public $transactionToReverse = null;

    #[Url]
    public $id = null;
    public $ledgerCache = [];

    // Add receipt properties
    public $receiptHtml = '';
    public $receiptData = [];
    public $selectedReceipientEmail = '';
    public $receiptLoading;
    public $emailReceiptLoading;

    public function mount(Transaction $transaction)
    {
        if ($this->id) {
            $this->loadTransactionData();
        } else {
            $this->loading = false;
        }
    }

    public function updatedId($value)
    {
        if ($value) {
            $this->loadTransactionData();
        }
    }

    public function getLedgerEntriesProperty()
    {
        $id = $this->transaction->id ?? null;

        if (!$id || $this->activeTab !== 'ledger') {
            return collect();
        }

        if (!isset($this->ledgerCache[$id])) {
            $this->ledgerCache[$id] = LedgerEntry::with(['account.customer', 'account.accountType'])
                ->where('transaction_id', $id)
                ->orderByDesc('created_at')
                ->get();
        }

        return $this->ledgerCache[$id];
    }

    private function loadTransactionData()
    {
        if (!$this->id) {
            $this->loading = false;
            return;
        }

        try {
            $this->loading = true;

            // Reset all properties
            $this->resetTransactionData();

            // Load transaction with all relationships in one go
            $this->transaction = Transaction::with([
                'initiator',
                'approver',
                'completer',
                'canceller',
                'sourceAccount.customer',
                'destinationAccount.customer',
                'beneficiary',
                'ledgerEntries' => function ($query) {
                    // Load ledger entries with their account relationships
                    $query->with([
                        'account.customer',
                        'account.accountType'
                    ])->orderBy('created_at', 'desc');
                }
            ])->where('transaction_id', $this->transaction->id);

            if (!$this->transaction) {
                session()->flash('error', 'Transaction not found.');
                $this->loading = false;
                return;
            }
            // Get source account
            $this->sourceAccount = $this->transaction->sourceAccount;
            if ($this->sourceAccount && $this->sourceAccount->customer) {
                $this->customer = $this->sourceAccount->customer;
            }

            // Get destination account
            $this->destinationAccount = $this->transaction->destinationAccount;

            // Get beneficiary
            $this->beneficiary = $this->transaction->beneficiary;

            // Load audit logs
            $this->loadAuditLogs();

            // Load related transactions
            $this->loadRelatedTransactions();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load transaction: ' . $e->getMessage());
            Log::error('Failed to load transaction', [
                'transaction_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->transaction = null;
        } finally {
            $this->loading = false;
        }
    }

    private function resetTransactionData()
    {
        $this->transaction = null;
        $this->sourceAccount = null;
        $this->destinationAccount = null;
        $this->customer = null;
        $this->beneficiary = null;
        $this->auditLogs = [];
        $this->relatedTransactions = [];
    }

    private function loadAuditLogs()
    {
        try {
            if ($this->transaction) {
                $this->auditLogs = AuditLogService::getTransactionLogs($this->transaction->id);
                Log::info('Audit logs loaded', ['count' => count($this->auditLogs)]);
            }
        } catch (\Exception $e) {
            $this->auditLogs = [];
            Log::error('Failed to load audit logs', ['error' => $e->getMessage()]);
        }
    }

    private function loadRelatedTransactions()
    {
        if ($this->customer && $this->transaction) {
            $this->relatedTransactions = Transaction::whereHas('ledgerEntries.account', function ($q) {
                $q->where('customer_id', $this->customer->id);
            })
                ->where('id', '!=', $this->transaction->id)
                ->whereDate('created_at', $this->transaction->created_at)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }
    }

    public function showReverseConfirmation()
    {
        $this->transactionToReverse = $this->transaction;
        $this->showReverseModal = true;
    }

    public function closeReverseModal()
    {
        $this->showReverseModal = false;
        $this->reverseReason = '';
        $this->transactionToReverse = null;
    }

    public function reverseTransaction()
    {
        try {
            $this->isProcessing = true;

            // Add your reversal logic here
            // Example:
            // $this->transaction->reverse($this->reverseReason);

            session()->flash('success', 'Transaction reversed successfully.');
            $this->closeReverseModal();

            // Reload the transaction data
            $this->loadTransactionData();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reverse transaction: ' . $e->getMessage());
            Log::error('Failed to reverse transaction', [
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage()
            ]);
        } finally {
            $this->isProcessing = false;
        }
    }

    public function printReceipt()
    {
        try {
            $this->showReceiptModal = true;

            // Generate receipt data
            $this->generateReceiptData();

            session()->flash('info', 'Receipt preview ready. You can print or download.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate receipt: ' . $e->getMessage());
        }
    }

    public function generateReceiptData()
    {
        if (!$this->transaction) {
            return;
        }

        // Get ledger entries
        $ledgerEntries = $this->transaction->ledgerEntries ?? collect();

        // Determine transaction direction
        $isTransfer = $this->transaction->type === 'transfer';
        $isDeposit = $this->transaction->type === 'deposit';
        $isWithdrawal = $this->transaction->type === 'withdrawal';

        // Get source and destination accounts from ledger entries
        $sourceEntry = $ledgerEntries->where('entry_type', 'debit')->first();
        $destinationEntry = $ledgerEntries->where('entry_type', 'credit')->first();

        $this->receiptData = [
            'transaction' => [
                'id' => $this->transaction->id,
                'reference' => $this->transaction->transaction_reference,
                'type' => $this->transaction->type,
                'type_display' => ucfirst($this->transaction->type),
                'status' => $this->transaction->status,
                'status_display' => ucfirst($this->transaction->status),
                'amount' => number_format($this->transaction->amount, 2),
                'currency' => $this->transaction->currency,
                'description' => $this->transaction->description,
                'date' => $this->transaction->created_at->format('F d, Y'),
                'time' => $this->transaction->created_at->format('h:i A'),
                'datetime' => $this->transaction->created_at->format('Y-m-d H:i:s'),
                'fee' => $this->transaction->fee_amount ? number_format($this->transaction->fee_amount, 2) : '0.00',
                'tax' => $this->transaction->tax_amount ? number_format($this->transaction->tax_amount, 2) : '0.00',
                'net_amount' => number_format($this->transaction->net_amount ?? $this->transaction->amount, 2),
                'initiated_by' => $this->transaction->initiator->name ?? 'System',
                'approved_by' => $this->transaction->approver->name ?? 'N/A',
                'completed_by' => $this->transaction->completer->name ?? 'N/A',
            ],
            'parties' => [
                'source' => $sourceEntry ? [
                    'account_number' => $sourceEntry->account->account_number ?? 'N/A',
                    'account_type' => $sourceEntry->account->accountType->name ?? 'N/A',
                    'customer_name' => $sourceEntry->account->customer->full_name ?? 'N/A',
                    'customer_id' => $sourceEntry->account->customer->customer_number ?? 'N/A',
                    'balance_before' => number_format($sourceEntry->balance_before, 2),
                    'balance_after' => number_format($sourceEntry->balance_after, 2),
                ] : null,
                'destination' => $destinationEntry ? [
                    'account_number' => $destinationEntry->account->account_number ?? 'N/A',
                    'account_type' => $destinationEntry->account->accountType->name ?? 'N/A',
                    'customer_name' => $destinationEntry->account->customer->full_name ?? 'N/A',
                    'customer_id' => $destinationEntry->account->customer->customer_number ?? 'N/A',
                    'balance_before' => number_format($destinationEntry->balance_before, 2),
                    'balance_after' => number_format($destinationEntry->balance_after, 2),
                ] : null,
            ],
            'company' => [
                'name' => config('app.name', 'Banking System'),
                'address' => '123 Financial District, Accra, Ghana',
                'phone' => '+233 30 123 4567',
                'email' => 'info@bankingsystem.com',
                'website' => 'https://metco.com',
                'logo' => asset('images/logo.png'),
            ],
            'ledger_entries' => $ledgerEntries->map(function ($entry) {
                return [
                    'account_number' => $entry->account->account_number ?? 'N/A',
                    'entry_type' => $entry->entry_type,
                    'entry_type_display' => strtoupper($entry->entry_type),
                    'amount' => number_format($entry->amount, 2),
                    'balance_before' => number_format($entry->balance_before, 2),
                    'balance_after' => number_format($entry->balance_after, 2),
                    'description' => $entry->description,
                    'currency' => $entry->currency,
                ];
            })->toArray(),
            'notes' => $this->transaction->notes,
            'metadata' => $this->transaction->metadata ? (is_array($this->transaction->metadata) ? $this->transaction->metadata : json_decode($this->transaction->metadata, true)) : [],
            'qr_code' => $this->generateQrCodeData(),
        ];
    }

    private function generateQrCodeData()
    {
        $data = [
            'transaction_id' => $this->transaction->id,
            'reference' => $this->transaction->transaction_reference,
            'amount' => $this->transaction->amount,
            'currency' => $this->transaction->currency,
            'date' => $this->transaction->created_at->format('Y-m-d H:i:s'),
            'status' => $this->transaction->status,
        ];

        return base64_encode(json_encode($data));
    }

    public function downloadReceipt()
    {
        try {
            $this->receiptLoading = true;

            // Generate receipt data if not already generated
            if (empty($this->receiptData)) {
                $this->generateReceiptData();
            }

            // Generate PDF
            $pdf = Pdf::loadView('receipts.transaction-receipt', $this->receiptData);

            $filename = 'receipt_' . $this->transaction->transaction_reference . '_' . now()->format('Ymd_His') . '.pdf';

            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                $filename,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to download receipt: ' . $e->getMessage());
            Log::error('Receipt download error: ' . $e->getMessage());
        } finally {
            $this->receiptLoading = false;
        }
    }

    public function emailReceipt()
    {
        try {
            $this->emailReceiptLoading = true;

            // Generate receipt data if not already generated
            if (empty($this->receiptData)) {
                $this->generateReceiptData();
            }

            // Get recipient email - default to source account customer
            $recipientEmail = $this->receiptData['parties']['source']['customer_email'] ??
                $this->customer->email ??
                Auth::user()->email;

            // Send email
            Mail::to($recipientEmail)->send(new TransactionReceiptMail($this->receiptData));

            session()->flash('success', 'Receipt has been sent to ' . $recipientEmail);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to email receipt: ' . $e->getMessage());
            Log::error('Receipt email error: ' . $e->getMessage());
        } finally {
            $this->emailReceiptLoading = false;
        }
    }

    public function printReceiptDirect()
    {
        try {
            $this->dispatch('print-receipt');
            session()->flash('success', 'Printing receipt...');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to print: ' . $e->getMessage());
        }
    }

    public function setRecipientEmail($email)
    {
        $this->selectedReceipientEmail = $email;
    }

    public function verifyTransaction()
    {
        try {
            $this->isProcessing = true;

            // Add your verification logic here
            // Example:
            // $this->transaction->verify(Auth::user());

            session()->flash('success', 'Transaction verified successfully.');

            // Reload the transaction data
            $this->loadTransactionData();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to verify transaction: ' . $e->getMessage());
            Log::error('Failed to verify transaction', [
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage()
            ]);
        } finally {
            $this->isProcessing = false;
        }
    }


    public function getTransactionTypes()
    {
        return [
            'transfer' => 'Transfer',
            'withdrawal' => 'Withdrawal',
            'deposit' => 'Deposit',
            'cash_deposit' => 'Cash Deposit',
            'cheque_deposit' => 'Cheque Deposit',
            'bill_payment' => 'Bill Payment',
            'loan_payment' => 'Loan Payment',
            'fee_collection' => 'Fee Collection',
            'adjustment' => 'Adjustment',
            'reversal' => 'Reversal',
        ];
    }

    public function getStatusColors()
    {
        return [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'reversed' => 'bg-purple-100 text-purple-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
        ];
    }

    public function getTransactionTimeline()
    {
        $timeline = [];

        if ($this->transaction) {
            $timeline[] = [
                'status' => 'Created',
                'time' => $this->transaction->created_at,
                'icon' => 'fas fa-plus-circle',
                'color' => 'text-blue-500',
                'description' => 'Transaction initiated',
                'user' => $this->transaction->initiator
            ];

            if ($this->transaction->approved_at) {
                $timeline[] = [
                    'status' => 'Approved',
                    'time' => $this->transaction->approved_at,
                    'icon' => 'fas fa-check',
                    'color' => 'text-green-500',
                    'description' => 'Transaction approved',
                    'user' => $this->transaction->approver
                ];
            }

            if ($this->transaction->completed_at) {
                $timeline[] = [
                    'status' => 'Completed',
                    'time' => $this->transaction->completed_at,
                    'icon' => 'fas fa-check-circle',
                    'color' => 'text-emerald-500',
                    'description' => 'Transaction completed',
                    'user' => $this->transaction->completer
                ];
            }

            if ($this->transaction->cancelled_at) {
                $timeline[] = [
                    'status' => 'Cancelled',
                    'time' => $this->transaction->cancelled_at,
                    'icon' => 'fas fa-times-circle',
                    'color' => 'text-red-500',
                    'description' => 'Transaction cancelled',
                    'user' => $this->transaction->canceller
                ];
            }
        }

        return $timeline;
    }

    
    #[Layout('layouts.main')]
    public function render()
    {
        $statusConfig = [
            'pending' => [
                'color' => 'bg-yellow-100 text-yellow-800',
                'icon' => 'fas fa-clock',
                'badge' => 'Pending'
            ],
            'completed' => [
                'color' => 'bg-green-100 text-green-800',
                'icon' => 'fas fa-check-circle',
                'badge' => 'Completed'
            ],
            'failed' => [
                'color' => 'bg-red-100 text-red-800',
                'icon' => 'fas fa-times-circle',
                'badge' => 'Failed'
            ],
            'reversed' => [
                'color' => 'bg-purple-100 text-purple-800',
                'icon' => 'fas fa-undo',
                'badge' => 'Reversed'
            ],
            'cancelled' => [
                'color' => 'bg-gray-100 text-gray-800',
                'icon' => 'fas fa-ban',
                'badge' => 'Cancelled'
            ],
        ];

        return view('livewire.transactions.view-transaction', [
            'transactionTypes' => $this->getTransactionTypes(),
            'statusConfig' => $statusConfig,
        ]);
    }
}