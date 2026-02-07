<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $table = 'loan_repayments';

    protected $fillable = [
        'loan_id',
        'transaction_id',
        'installment_number',
        'due_date',
        'paid_date',
        'principal_amount',
        'interest_amount',
        'penalty_amount',
        'late_fee',
        'total_due',
        'amount_paid',
        'remaining_balance',
        'status',
        'payment_method',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'loan_id' => 'integer',
        'transaction_id' => 'integer',
        'principal_amount' => 'decimal:4',
        'interest_amount' => 'decimal:4',
        'penalty_amount' => 'decimal:4',
        'late_fee' => 'decimal:4',
        'total_due' => 'decimal:4',
        'amount_paid' => 'decimal:4',
        'remaining_balance' => 'decimal:4',
        'due_date' => 'date',
        'paid_date' => 'datetime',
    ];

    // Relationships
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date < now();
    }

    public function getDaysOverdueAttribute(): int
    {
        if ($this->is_overdue) {
            return now()->diffInDays($this->due_date);
        }
        return 0;
    }
}
