<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountInterest extends Model
{
    use SoftDeletes;

    protected $table = 'account_interests';

    protected $fillable = [
        'account_id',
        'interest_configuration_id',
        'transaction_id',
        'interest_reference',
        'amount',
        'interest_rate',
        'currency',
        'status',
        'calculation_method',
        'period_start',
        'period_end',
        'posting_date',
        'processed_at',
        'average_balance',
        'minimum_balance',
        'balance_before',
        'balance_after',
        'failure_reason',
        'calculation_details',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'interest_rate' => 'decimal:4',
        'average_balance' => 'decimal:4',
        'minimum_balance' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'period_start' => 'date',
        'period_end' => 'date',
        'posting_date' => 'date',
        'processed_at' => 'date',
        'calculation_details' => 'json',
        'metadata' => 'json',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function interestConfiguration(): BelongsTo
    {
        return $this->belongsTo(InterestConfiguration::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessed(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
