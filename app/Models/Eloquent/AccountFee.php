<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountFee extends Model
{
    use SoftDeletes;

    protected $table = 'account_fees';

    protected $fillable = [
        'account_id',
        'fee_configuration_id',
        'transaction_id',
        'fee_reference',
        'amount',
        'currency',
        'status',
        'period_type',
        'period_start',
        'period_end',
        'charge_date',
        'processed_at',
        'balance_before',
        'balance_after',
        'waived',
        'waiver_reason',
        'failure_reason',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'waived' => 'boolean',
        'period_start' => 'date',
        'period_end' => 'date',
        'charge_date' => 'date',
        'processed_at' => 'date',
        'metadata' => 'json',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';
    const STATUS_WAIVED = 'waived';

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function feeConfiguration(): BelongsTo
    {
        return $this->belongsTo(FeeConfiguration::class);
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

    public function isWaived(): bool
    {
        return $this->waived || $this->status === self::STATUS_WAIVED;
    }
}
