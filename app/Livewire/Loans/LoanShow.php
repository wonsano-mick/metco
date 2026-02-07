<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use App\Models\Eloquent\Loan;
use App\Models\Eloquent\User;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\Customer;
use App\Models\Eloquent\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class LoanShow extends Component
{
    public $loan;
    public $loanId;
    public $activeTab = 'details';
    public $showApproveModal = false;
    public $showRejectModal = false;
    public $showDisburseModal = false;
    public $approvalNotes = '';
    public $rejectionReason = '';
    public $disbursementData = [
        'method' => 'bank_transfer',
        'account_id' => null,
        'cheque_number' => '',
        'mobile_money_number' => '',
        'mobile_money_provider' => '',
        'notes' => '',
    ];

    public $customer = null;
    public $account = null;
    public $loanOfficer = null;
    public $approver = null;
    public $disbursementAccount = null;
    /** @var Collection */
    public $repayments;
    /** @var Collection */
    public $committeeReviews;
    /** @var Collection */
    public $transactions;




    public function mount(Loan $loan)
    {
        $this->loanId = $loan;
        $this->loadLoan();
    }

    private function loadLoan()
    {
        // Load loan without relationships first
        $this->loan = Loan::findOrFail($this->loanId->id);

        // Load relationships separately
        $this->loadRelationships();

        // Initialize disbursement data
        if ($this->loan->account_id) {
            $this->disbursementData['account_id'] = $this->loan->account_id;
        }
    } 

    private function loadRelationships()
    {
        // Load customer
        if ($this->loan->customer_id) {
            $this->customer = Customer::select([
                'id',
                'customer_number',
                'first_name',
                'last_name',
                'email',
                'phone',
                'profile_photo_path',
                'monthly_income',
                'kyc_status'
            ])->find($this->loan->customer_id)?->toArray();
        }

        // Load account
        if ($this->loan->account_id) {
            $this->account = Account::select(['id', 'account_number', 'current_balance'])
                ->find($this->loan->account_id);
        }

        // Load loan officer
        if ($this->loan->loan_officer_id) {
            $this->loanOfficer = User::select(['id', 'first_name', 'last_name'])
                ->find($this->loan->loan_officer_id);
        }

        // Load approver
        if ($this->loan->approved_by) {
            $this->approver = User::select(['id', 'first_name', 'last_name'])
                ->find($this->loan->approved_by);
        }

        // Load disbursement account
        if ($this->loan->disbursement_account_id) {
            $this->disbursementAccount = Account::select(['id', 'account_number'])
                ->find($this->loan->disbursement_account_id);
        }

        // Load repayments
        $this->repayments = $this->loan->repayments()
            ->with(['transaction:id,transaction_reference'])
            ->orderBy('installment_number')
            ->get();

        // Load committee reviews
        $this->committeeReviews = $this->loan->committeeReviews()
            ->with(['reviewer:id,first_name,last_name'])
            ->get();

        // Load transactions
        // $this->transactions = $this->loan->transactions()
        //     ->select(['id', 'transaction_reference', 'amount', 'type', 'status', 'description', 'initiated_at'])
        //     ->orderBy('initiated_at', 'desc')
        //     ->get();
    }

    // Helper methods for display
    public function getCustomerFullName()
    {
        if (!$this->customer) {
            return 'N/A';
        }

        return trim(($this->customer->first_name ?? '') . ' ' . ($this->customer->last_name ?? ''));
    }

    public function getLoanOfficerFullName()
    {
        if (!$this->loanOfficer) {
            return 'N/A';
        }

        return trim(($this->loanOfficer->first_name ?? '') . ' ' . ($this->loanOfficer->last_name ?? ''));
    }

    public function getApproverFullName()
    {
        if (!$this->approver) {
            return 'N/A';
        }

        return trim(($this->approver->first_name ?? '') . ' ' . ($this->approver->last_name ?? ''));
    }

    public function getCustomerProfilePhoto()
    {
        if (!$this->customer) {
            return null;
        }

        return $this->customer['profile_photo_path'] ?? null;
    }
    

    public function getCustomerFullNameProperty()
    {
        if (!$this->loan || !$this->loan->customer) {
            return 'N/A';
        }

        return $this->loan->customer->full_name ??
            trim($this->loan->customer->first_name . ' ' . $this->loan->customer->last_name);
    }

    public function getLoanOfficerFullNameProperty()
    {
        if (!$this->loan || !$this->loan->loanOfficer) {
            return 'N/A';
        }

        return $this->loan->loanOfficer->full_name ??
            trim($this->loan->loanOfficer->first_name . ' ' . $this->loan->loanOfficer->last_name);
    }

    public function getApproverFullNameProperty()
    {
        if (!$this->loan || !$this->loan->approver) {
            return 'N/A';
        }

        return $this->loan->approver->full_name ??
            trim($this->loan->approver->first_name . ' ' . $this->loan->approver->last_name);
    }

    public function getStatsProperty()
    {
        if (!$this->loan) {
            return [
                'total_paid' => 0,
                'remaining_balance' => 0,
                'next_payment' => null,
                'days_overdue' => 0,
                'installments_paid' => 0,
                'total_installments' => 0,
            ];
        }

        $paidInstallments = $this->repayments->where('status', 'paid')->count();

        return [
            'total_paid' => $this->loan->amount_paid ?? 0,
            'remaining_balance' => $this->loan->remaining_balance ?? 0,
            'next_payment' => $this->loan->next_payment_date ?? null,
            'days_overdue' => $this->loan->days_overdue ?? 0,
            'installments_paid' => $paidInstallments,
            'total_installments' => $this->loan->term_months ?? 0,
        ];
    }


    public function openApproveModal()
    {
        $this->showApproveModal = true;
    }

    public function openRejectModal()
    {
        $this->showRejectModal = true;
    }

    public function openDisburseModal()
    {
        $this->showDisburseModal = true;
    }

    public function closeModal($modal)
    {
        $this->{$modal} = false;
        if ($modal === 'showApproveModal') {
            $this->approvalNotes = '';
        } elseif ($modal === 'showRejectModal') {
            $this->rejectionReason = '';
        } elseif ($modal === 'showDisburseModal') {
            $this->disbursementData = [
                'method' => $this->loan->disbursement_method ?? 'bank_transfer',
                'account_id' => $this->loan->account_id,
                'cheque_number' => '',
                'mobile_money_number' => '',
                'mobile_money_provider' => '',
                'notes' => '',
            ];
        }
    }

    public function approveLoan()
    {
        $this->validate([
            'approvalNotes' => 'nullable|string|max:500',
        ]);

        try {
            
            if ($this->loan->committee_status !== 'reviewed') {
                throw new \Exception('Loan cannot be approved until committee recommends it.');
            }

            $this->loan->approve(Auth::id(), $this->approvalNotes);

            $this->dispatch(
                'showToast',
                message: 'Loan approved successfully!',
                type: 'success'
            );

            $this->closeModal('showApproveModal');
            $this->loadLoan();
        } catch (\Exception $e) {
            $this->dispatch(
                'showToast',
                message: 'Error approving loan: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }

    public function rejectLoan()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:10|max:500',
        ]);

        try {
            $this->loan->reject($this->rejectionReason);

            $this->dispatch(
                'showToast',
                message: 'Loan rejected successfully!',
                type: 'success'
            );

            $this->closeModal('showRejectModal');
            $this->loadLoan();
        } catch (\Exception $e) {
            $this->dispatch(
                'showToast',
                message: 'Error rejecting loan: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }

    public function disburseLoan()
    {
        dd($this->loan);
        $this->validate([
            'disbursementData.method' => 'required|in:bank_transfer,cash,cheque,mobile_money',
            'disbursementData.account_id' => 'required_if:disbursementData.method,bank_transfer|exists:accounts,id',
            'disbursementData.cheque_number' => 'required_if:disbursementData.method,cheque|string',
            'disbursementData.mobile_money_number' => 'required_if:disbursementData.method,mobile_money|string',
            'disbursementData.mobile_money_provider' => 'required_if:disbursementData.method,mobile_money|string',
            'disbursementData.notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->loan->disburse($this->disbursementData);

            // Create disbursement transaction
            $transaction = Transaction::create([
                'transaction_reference' => 'DISB' . time() . mt_rand(1000, 9999),
                'type' => 'loan_disbursement',
                'status' => 'completed',
                'amount' => $this->loan->amount,
                'currency' => 'GHS',
                'description' => 'Loan disbursement for ' . $this->loan->loan_number,
                'metadata' => [
                    'loan_id' => $this->loan->id,
                    'disbursement_method' => $this->disbursementData['method'],
                    'approved_by' => Auth::id(),
                ],
                'initiated_by' => Auth::id(),
                'initiated_at' => now(),
                'completed_at' => now(),
            ]);

            // Link transaction to loan
            $this->loan->transactions()->attach($transaction->id);

            //Create ledger entry

            $this->dispatch(
                'showToast',
                message: 'Loan disbursed successfully!',
                type: 'success'
            );

            $this->closeModal('showDisburseModal');
            $this->loadLoan();
        } catch (\Exception $e) {
            $this->dispatch(
                'showToast',
                message: 'Error disbursing loan: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }
    public function markAsPaid($repaymentId)
    {
        try {
            $repayment = $this->repayments->firstWhere('id', $repaymentId);

            if (!$repayment) {
                throw new \Exception('Repayment not found');
            }

            // Create payment transaction
            $transaction = Transaction::create([
                'transaction_reference' => 'LNPMT' . time() . mt_rand(1000, 9999),
                'type' => 'loan_payment',
                'status' => 'completed',
                'amount' => $repayment->total_due,
                'currency' => 'GHS',
                'description' => 'Loan payment for installment #' . $repayment->installment_number,
                'metadata' => [
                    'loan_id' => $this->loan->id,
                    'installment_number' => $repayment->installment_number,
                ],
                'initiated_by' => Auth::id(),
                'initiated_at' => now(),
                'completed_at' => now(),
            ]);

            // Update repayment
            $repayment->update([
                'status' => 'paid',
                'paid_date' => now(),
                'amount_paid' => $repayment->total_due,
                'remaining_balance' => 0,
                'transaction_id' => $transaction->id,
            ]);

            // Update loan totals
            $this->loan->update([
                'amount_paid' => $this->loan->amount_paid + $repayment->total_due,
                'remaining_balance' => $this->loan->remaining_balance - $repayment->total_due,
            ]);

            // Reload data
            $this->loadLoan();

            $this->dispatch(
                'showToast',
                message: 'Payment recorded successfully!',
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch(
                'showToast',
                message: 'Error recording payment: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }

    public function getCanApproveProperty()
    {
        return Gate::allows('approve loans') &&
            $this->loan->status === 'pending' &&
            $this->loan->approval_status === 'pending' &&
            $this->loan->committee_status === 'reviewed';
    }

    public function getCanDisburseProperty()
    {
        return Gate::allows('disburse loans') &&
            $this->loan->status === 'approved' &&
            !$this->loan->disbursed_at;
    }

    public function getCanRejectProperty()
    {
        if (!$this->loan) return false;

        return Gate::allows('reject loans') &&
            $this->loan->status === 'pending' &&
            $this->loan->approval_status === 'pending' &&
            $this->loan->committee_status === 'reviewed';
    }

    public function getCanProcessPaymentProperty()
    {
        return Gate::allows('process loan payments') &&
            $this->loan->status === 'active' &&
            $this->loan->remaining_balance > 0;
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.loans.loan-show', [
            'stats' => $this->stats,
            'customerFullName' => $this->getCustomerFullName(),
            'loanOfficerFullName' => $this->getLoanOfficerFullName(),
            'approverFullName' => $this->getApproverFullName(),
            'customerProfilePhoto' => $this->getCustomerProfilePhoto(),
            'canApprove' => $this->getCanApproveProperty(),
            'canDisburse' => $this->getCanDisburseProperty(),
            'canReject' => $this->getCanRejectProperty(),
            'canProcessPayment' => $this->getCanProcessPaymentProperty(),
        ]);
    }
}