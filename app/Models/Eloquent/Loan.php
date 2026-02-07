<?php

namespace App\Models\Eloquent;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'loans';

    protected $fillable = [
        'customer_id',
        'account_id',
        'loan_officer_id',
        'approved_by',
        'loan_number',
        'loan_type',
        'purpose',
        'amount',
        'interest_rate',
        'interest_type',
        'term_months',
        'repayment_frequency',
        'start_date',
        'end_date',
        'disbursement_date',
        'next_payment_date',
        'status',
        'application_status',
        'approval_status',
        'committee_status',
        'total_interest',
        'total_amount',
        'remaining_balance',
        'amount_paid',
        'penalty_rate',
        'late_payment_fee',
        'processing_fee',
        'insurance_fee',
        'collateral_value',
        'collateral_details',
        'guarantors',
        'committee_notes',
        'approval_notes',
        'rejection_reason',
        'disbursement_method',
        'disbursement_account_id',
        'metadata',
        'application_date',
        'approved_at',
        'disbursed_at',
        'completed_at',
        'defaulted_at',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'account_id' => 'integer',
        'loan_officer_id' => 'integer',
        'approved_by' => 'integer',
        'disbursement_account_id' => 'integer',
        'amount' => 'decimal:4',
        'interest_rate' => 'decimal:4',
        'total_interest' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'remaining_balance' => 'decimal:4',
        'amount_paid' => 'decimal:4',
        'penalty_rate' => 'decimal:4',
        'late_payment_fee' => 'decimal:4',
        'processing_fee' => 'decimal:4',
        'insurance_fee' => 'decimal:4',
        'collateral_value' => 'decimal:4',
        'collateral_details' => 'array',
        'guarantors' => 'array',
        'metadata' => 'array',
        'application_date' => 'datetime',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'completed_at' => 'datetime',
        'defaulted_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'disbursement_date' => 'date',
        'next_payment_date' => 'date',
    ];

    protected $appends = [
        'monthly_payment',
        'next_payment_amount',
        'days_overdue',
        'is_overdue',
        'loan_progress',
    ];

    public static function generateLoanNumber(): string
    {
        $prefix = 'LN';
        $year = date('Y');
        $month = date('m');
        $sequence = str_pad(self::count() + 1, 6, '0', STR_PAD_LEFT);
        return $prefix . $year . $month . $sequence;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->loan_number)) {
                $model->loan_number = self::generateLoanNumber();
            }
            if (empty($model->application_date)) {
                $model->application_date = now();
            }
            if (empty($model->status)) {
                $model->status = 'pending';
            }
        });
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function loanOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loan_officer_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function disbursementAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'disbursement_account_id');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'loan_id');
    }

    public function committeeReviews(): HasMany
    {
        return $this->hasMany(LoanCommitteeReview::class);
    }

    // Accessors
    public function getMonthlyPaymentAttribute(): float
    {
        if ($this->amount <= 0 || $this->interest_rate <= 0 || $this->term_months <= 0) {
            return 0;
        }

        $monthlyRate = $this->interest_rate / 12 / 100;
        $numerator = $monthlyRate * pow(1 + $monthlyRate, $this->term_months);
        $denominator = pow(1 + $monthlyRate, $this->term_months) - 1;

        return $this->amount * ($numerator / $denominator);
    }

    public function getNextPaymentAmountAttribute(): float
    {
        $nextRepayment = $this->repayments()
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();

        return $nextRepayment ? (float) $nextRepayment->total_due : 0;
    }

    public function getDaysOverdueAttribute(): int
    {
        if ($this->next_payment_date && $this->status === 'active') {
            return max(0, now()->diffInDays($this->next_payment_date, false));
        }
        return 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->days_overdue > 0;
    }

    public function getLoanProgressAttribute(): float
    {
        if ($this->total_amount <= 0) return 0;
        return min(100, ($this->amount_paid / $this->total_amount) * 100);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByLoanOfficer($query, $officerId)
    {
        return $query->where('loan_officer_id', $officerId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
            ->where('next_payment_date', '<', now());
    }

    // Methods
    public function approve($userId, $notes = null)
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'status' => 'approved',
        ]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($this)
            ->withProperties([
                'loan_number' => $this->loan_number,
                'approved_by' => $userId,
                'notes' => $notes
            ])
            ->log('Loan approved');
    }

    public function reject($reason)
    {
        $this->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $reason,
            'status' => 'rejected',
        ]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($this)
            ->withProperties([
                'loan_number' => $this->loan_number,
                'reason' => $reason
            ])
            ->log('Loan rejected');
    }

    public function disburse($disbursementData)
    {
        $this->update([
            'status' => 'disbursed',
            'disbursement_date' => now(),
            'disbursed_at' => now(),
            'disbursement_method' => $disbursementData['method'] ?? 'bank_transfer',
            'disbursement_account_id' => $disbursementData['account_id'] ?? null,
            'next_payment_date' => $this->calculateNextPaymentDate(),
        ]);

        // Generate repayment schedule
        $this->generateRepaymentSchedule();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($this)
            ->withProperties([
                'loan_number' => $this->loan_number,
                'disbursement_data' => $disbursementData
            ])
            ->log('Loan disbursed');
    }

    private function calculateNextPaymentDate()
    {
        return now()->addMonth();
    }

    public function generateRepaymentSchedule()
    {
        $paymentAmount = $this->monthly_payment;
        $principal = $this->amount;
        $monthlyRate = $this->interest_rate / 12 / 100;
        $currentDate = now();

        for ($i = 1; $i <= $this->term_months; $i++) {
            $interest = $principal * $monthlyRate;
            $principalPayment = $paymentAmount - $interest;
            $principal -= $principalPayment;

            LoanRepayment::create([
                'loan_id' => $this->id,
                'installment_number' => $i,
                'due_date' => $currentDate->copy()->addMonths($i),
                'principal_amount' => $principalPayment,
                'interest_amount' => $interest,
                'total_due' => $paymentAmount,
                'status' => 'pending',
            ]);
        }
    }
}
