<?php

namespace App\Models\Eloquent;

use Ramsey\Uuid\Uuid;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    // protected $primaryKey = 'id';
    // public $incrementing = false;
    // protected $keyType = 'string';

    protected $fillable = [
        // 'id',
        // 'tenant_id',
        'transaction_reference',
        'type',
        'status',
        'category',
        // Financial Details
        'amount',
        'fee_amount',
        'tax_amount',
        'net_amount',
        'currency',

        // Description and Metadata
        'description',
        'notes',
        'metadata',
        'failure_reason',

        // Parties Involved
        'initiated_by',
        'approved_by',
        'completed_by',
        'cancelled_by',

        // Account Relationships
        'source_account_id',
        'destination_account_id',
        'beneficiary_id',

        // Timestamps
        'initiated_at',
        'approved_at',
        'processed_at',
        'completed_at',
        'cancelled_at',
        'reversed_at',
        'scheduled_for',
        'expires_at',

        // Audit Trail
        'ip_address',
        'user_agent',
        'device_id',
        'location',
    ];

    protected $casts = [
        'metadata' => 'json',
        'amount' => 'decimal:4',
        'initiated_at' => 'datetime',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (empty($model->id)) {
    //             $model->id = Uuid::uuid4()->toString();
    //         }
    //         if (empty($model->transaction_reference)) {
    //             $model->transaction_reference = 'TXN' . time() . mt_rand(1000, 9999);
    //         }
    //         if (empty($model->initiated_at)) {
    //             $model->initiated_at = now();
    //         }
    //     });
    // }

    // public function tenant(): BelongsTo
    // {
    //     return $this->belongsTo(Tenant::class);
    // }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'transaction_id');
    }

    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'source_account_id');
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }

    public function complete(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    public function fail(string $reason = null): void
    {
        $this->status = 'failed';
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['failure_reason'] = $reason;
            $this->metadata = $metadata;
        }
        $this->save();
    }

    public function reverse(): void
    {
        $this->status = 'reversed';
        $this->reversed_at = now();
        $this->save();
    }

    public function getAmount(): float
    {
        return (float) $this->amount;
    }

    /**
     * Boot the model.
     */

    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            AuditLogService::logTransactionCreated($transaction, [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'branch_id' => $transaction->metadata['branch_id'] ?? null,
                'teller_id' => $transaction->metadata['teller_id'] ?? null,
            ]);
        });

        static::updated(function ($transaction) {
            // Check if status changed
            if ($transaction->isDirty('status')) {
                AuditLogService::logTransactionStatusChange(
                    $transaction,
                    $transaction->getOriginal('status'),
                    $transaction->status,
                    [
                        'changed_by' => Auth::user()->id,
                        'ip_address' => request()->ip(),
                    ]
                );
            }

            // Log other changes
            $changes = [];
            foreach ($transaction->getDirty() as $field => $newValue) {
                if ($field !== 'status' && $field !== 'updated_at') {
                    $changes[$field] = [
                        'old' => $transaction->getOriginal($field),
                        'new' => $newValue,
                    ];
                }
            }

            if (!empty($changes)) {
                AuditLogService::logTransactionUpdated($transaction, $changes, [
                    'changed_by' => Auth::user()->id,
                ]);
            }
        });

        static::deleting(function ($transaction) {
            AuditLogService::log('transaction_deleted', $transaction, [
                'transaction_data' => $transaction->toArray(),
            ], null, [
                'deleted_by' => Auth::user()->id,
                'deleted_at' => now(),
            ]);
        });
    }

    /**
     * Mark transaction as completed and log it.
     */
    public function markAsCompleted($userId = null)
    {
        $oldStatus = $this->status;
        $this->status = 'completed';
        $this->completed_at = now();
        $this->completer_id = $userId ?? Auth::user()->id;
        $this->save();

        // Log the completion
        AuditLogService::logTransactionStatusChange($this, $oldStatus, 'completed', [
            'completed_by' => $this->completer_id,
            'completed_at' => $this->completed_at,
        ]);

        return $this;
    }

    /**
     * Mark transaction as verified and log it.
     */
    public function markAsVerified($userId = null)
    {
        $this->verified_at = now();
        $this->verified_by = $userId ?? Auth::user()->id;
        $this->save();

        AuditLogService::logTransactionVerified($this, [
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at,
        ]);

        return $this;
    }
}
