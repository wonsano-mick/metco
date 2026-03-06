<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyAccountProcessing extends Model
{
    protected $table = 'monthly_account_processings';

    protected $fillable = [
        'account_id',
        'processing_month',
        'balance_before',
        'monthly_fee_applied',
        'interest_earned',
        'balance_after',
        'fee_transaction_id',
        'interest_transaction_id',
        'processed_at'
    ];

    protected $casts = [
        'processing_month' => 'date',
        'processed_at' => 'datetime',
        'balance_before' => 'decimal:2',
        'monthly_fee_applied' => 'decimal:2',
        'interest_earned' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function feeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'fee_transaction_id');
    }

    public function interestTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'interest_transaction_id');
    }
}
